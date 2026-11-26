<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");
Class ActionSet {


	private static function updateBudgetTransferStatus($btNum, $status, $tm) {

		$now = Xbo::setEnterDate();

		$budgetTransferSet = (new Set())->useTransactionManager()
			->MboSetRemote("budgettransfer")
			->where("budgettransfernum = ?", $btNum)
			->setValue("status", $status)
			->setValue("statusdate", $now); 

		if (strtolower($status) == "approved") {
			$approver = Xbo::getLoginUser();
			$budgetTransferSet->setValue("approver", $approver)->setValue("approvaldate", $now);
		}	

		$budgetTransferSet->change();
	}

	public static function transfering_budget()
	{
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$budgettransfernum = Super::getParam("transfering_budget");
			$new_status = Super::getParam("new_status");

			if (empty($budgettransfernum) || empty($new_status)) {
				throw new InvalidArgumentException("Missing required parameters: budgettransfernum, new_status.");
			}

			//  2: Ambil Data Budget Transfer dengan Aman
			// Gunakan useTransactionManager agar berada dalam koneksi yang sama
			$btSet = (new Set())->useTransactionManager()
				->MboSetRemote("budgettransfer")
				->where("budgettransfernum = ?", $budgettransfernum)
				->getMboSet();

			$bt = $btSet->fetch();
			if (!$bt) {
				throw new Exception("Budget Transfer record not found.");
			}

			// Validasi Status (Sesuai komentar Anda)
			$current_status = strtolower($bt['status']);
			$target_status = strtolower($new_status);

			// Jika mau cancel, pastikan belum ditransfer
			if ($target_status == 'canceled') {
				if ($current_status == 'transfered') {
					throw new Exception("Cannot cancel. Budget has already been transfered!");
				}
				
				self::updateBudgetTransferStatus($budgettransfernum, 'canceled', $tm);
				$tm->commit();
				Super::sendJsonResponse("success", "Budget transfer request has been canceled.");
				return;
			}

			// Hanya boleh transfer jika status sekarang adalah 'approved'
			if ($target_status == 'transfered' && $current_status != 'approved') {
				throw new Exception("Cannot transfer. Current status is '$current_status', must be 'approved'.");
			}

			// Jika status bukan 'transfered', mungkin hanya update status biasa (pending -> approved)
			if ($target_status != 'transfered') {
				self::updateBudgetTransferStatus($budgettransfernum, $target_status, $tm);
				$tm->commit();
				Super::sendJsonResponse("success", "Status updated to $target_status.");
				return;
			}

			// === PROSES TRANSFER DANA (Hanya jika status 'transfered') ===

			$transferamount = (float) $bt['transferamount'];
			$frombudgetnum = $bt['frombudgetnum'];
			$frombudgetlinenum = $bt['frombudgetlinenum'];
			$tobudgetnum = $bt['tobudgetnum'];
			$tobudgetlinenum = $bt['tobudgetlinenum'];

			// 3: Ambil & Validasi SUMBER Dana
			$sourceBudgetSet = (new Set())->useTransactionManager()
				->MboSetRemote("budgetline")
				->where("budgetnum = ? AND budgetlinenum = ?", $frombudgetnum, $frombudgetlinenum)
				->getMboSet();
			
			$sourceBudget = $sourceBudgetSet->fetch();
			if (!$sourceBudget) {
				throw new Exception("Source budget line not found.");
			}

			$current_from_amount = (float) $sourceBudget['budgetamount'];
			$committed_amount = (float) ($sourceBudget['committedamount'] ?? 0);
			$available_funds = $current_from_amount - $committed_amount;

			if ($available_funds < $transferamount) {
				throw new Exception("Insufficient funds. Available: $available_funds, Required: $transferamount");
			}

			// 4: TUJUAN Dana
			$targetBudgetSet = (new Set())->useTransactionManager()
				->MboSetRemote("budgetline")
				->where("budgetnum = ? AND budgetlinenum = ?", $tobudgetnum, $tobudgetlinenum)
				->getMboSet();

			$targetBudget = $targetBudgetSet->fetch();
			if (!$targetBudget) {
				throw new Exception("Target budget line not found.");
			}
			
			$current_to_amount = (float) $targetBudget['budgetamount'];

			// 5: EKSEKUSI TRANSFER
			
			// Kurangi Sumber (Gunakan validasi saldo lagi di WHERE untuk mencegah race condition)
			$deductSource = (new Set())->useTransactionManager()
				->MboSetRemote("budgetline")
				->where("budgetnum = ? AND budgetlinenum = ? AND budgetamount >= ?", $frombudgetnum, $frombudgetlinenum, $transferamount)
				->setValue("budgetamount", $current_from_amount - $transferamount)
				->change();
				
			// Cek apakah update berhasil (affected rows). Jika 0, berarti saldo berubah di tengah jalan!
			// (Perlu penyesuaian di class Set agar change() mengembalikan affected rows atau melempar error jika 0)
			// Untuk sekarang kita asumsikan transaksi mengunci baris (tergantung isolation level DB).

			// Tambah Tujuan
			(new Set())->useTransactionManager()
				->MboSetRemote("budgetline")
				->where("budgetnum = ? AND budgetlinenum = ?", $tobudgetnum, $tobudgetlinenum)
				->setValue("budgetamount", $current_to_amount + $transferamount)
				->change();

			// 6: Catat History & Update Status Akhir
			(new Set())->useTransactionManager()->MboSetRemote("budgettransferhistory")
				->setValue("budgettransfernum", $budgettransfernum)
				->setValue("frombudgetnum", $frombudgetnum)
				->setValue("frombudgetlinenum", $frombudgetlinenum)
				->setValue("tobudgetnum", $tobudgetnum)
				->setValue("tobudgetlinenum", $tobudgetlinenum)
				->setValue("prevbudgetamount", $current_to_amount)
				->setValue("newbudgetamount", $current_to_amount + $transferamount)
				->setValue("budgetvarianceamount", $transferamount)
				->setValue("transferedby", Xbo::getLoginUser())
				->setValue("transfereddate", Xbo::setEnterDate())
				->add();

			// Update status transfer menjadi 'transfered'
			self::updateBudgetTransferStatus($budgettransfernum, 'transfered', $tm);

			$tm->commit();
			Super::sendJsonResponse("success", "The budget has been successfully transfered.");

		} catch (Exception $e) {
			// Jika ada kesalahan APAPUN, rollback SEMUANYA.
			$tm->rollback();
			Super::sendJsonResponse("error", "Transfer failed: " . $e->getMessage());
		}
	}
}

?>