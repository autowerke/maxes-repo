<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function releaseReservation() {
		// 1. Dapatkan dan Validasi Input
		$payload = Super::getParam("releaseReservation");
		$invreservenum = $payload['invreservenum'] ?? null;
		$release_reason = $payload['reason'] ?? 'Released by user.'; 
		
		$loggedInUser = Xbo::getLoginUser();

		if (empty($invreservenum)) {
			self::sendJsonResponse("error", "Invalid payload. Reservation number is missing.");
			return;
		}

		// =================================================================================
		// 2. VALIDASI 
		// =================================================================================
		
		// Validasi A: Dapatkan reservasi dan cek apakah ada
		$mainSet = new Set();
		$invreserveMbo = $mainSet->MboSetRemote("invreserve")->setWhere("invreservenum='$invreservenum'")->getMboSet()->getMbo();

		if (!$invreserveMbo) {
			self::sendJsonResponse('error', "Reservation {$invreservenum} not found.");
			return;
		}
		$status = $invreserveMbo->getString('status');
		$itemnum_to_revalidate = $invreserveMbo->getString('itemnum');
		// Validasi B: Cek kepemilikan. Hanya pemilik yang boleh melepaskan.
		$ownerPersonId = $invreserveMbo->getString('reservedfor');
		$mainSet = new Set();
		$userMbo = $mainSet->MboSetRemote("mxsusers")->setWhere("personid='{$ownerPersonId}'")->getMboSet()->getMbo();
		
		if (!$userMbo || $userMbo->getString('username') !== $loggedInUser) {
			self::sendJsonResponse('error', 'You are not authorized to release this reservation.');
			return;
		}
		
		// Validasi C: Cek status. Hanya reservasi 'RESERVED' yang bisa dilepaskan.
		if ($status !== 'RESERVED') {
			self::sendJsonResponse('error', "Only reservations with 'RESERVED' status can be released. Current status is: " . $invreserveMbo->getString('status'), 409);
			return;
		}

		

		// =================================================================================
		// 3. EKSEKUSI 
		// =================================================================================
		try {
			// --- AKSI 1: Batalkan Reservasi ---
			// Ubah status menjadi status yang deskriptif
			$mainSet = new Set();
			$mainSet->MboSetRemote("invreserve")->setWhere("invreservenum='$invreservenum'")
				->setValue('status', 'CANCELLED_BY_USER')
				->setValue('changeby', $loggedInUser)
				->setValue('changedate', Xbo::setEnterDate())
				->change();

			// --- AKSI 2 (Opsional tapi bagus): Buat Jejak Audit ---
			// Kita bisa gunakan 'reservationswap' lagi atau tabel log khusus
			$setAudit = new Set();
			$setAudit->MboSetRemote("reservationswap")
				->setValue("target_invreservenum", $invreservenum) // Reservasi yang dilepaskan
				->setValue("requestermsg", "Reservation released by owner. Reason: " . $release_reason)
				->setValue("invreservenum", $invreservenum)
				->setValue("status", "RELEASED")
				->setValue("enterby", $loggedInUser)
				->setValue("enterdate", Xbo::setEnterDate())
				->add();

		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred while releasing the reservation: ' . $e->getMessage());
			return;
		}

		// =================================================================================
		// 4. PICU ULANG ALLOCATION ENGINE (Menutup Siklus)
		// =================================================================================
		if (!empty($itemnum_to_revalidate)) {
			// Panggil mesin alokasi untuk item spesifik ini.
			// Ini akan secara otomatis mencari tahu siapa di waiting list yang berhak
			// mendapatkan stok yang baru saja "bebas" ini.
			self::revalidateall($itemnum_to_revalidate);
		}
		
		// 5. Kirim Response Sukses
		self::sendJsonResponse('success', "Reservation {$invreservenum} has been successfully released. The stock is now available for others.");
	}
	
	public static function getLastInsertInvreserveID($whereclause){
	
		$set = new Set();
		$invreserveSet = $set->MboSetRemote("invreserve")->setWhere($whereclause)->MboRemote("max(invreserveid) as invreserveid")->getMboSet();
		$invs = $invreserveSet->getMbo();
		$last_invs_id = $invs->getString("invreserveid");
		return $last_invs_id;
	}
	public static function getLastInsertId($whereclause){
		$set = new Set();
		$reservationswapSet = $set->MboSetRemote("reservationswap")->setWhere($whereclause)->MboRemote("max(reservationswapid) as reservationswapid")->getMboSet();
		$reservationswap = $reservationswapSet->getMbo();
		$last_swap_id = $reservationswap->getString("reservationswapid");
		return $last_swap_id;
	}
	public static function sendJsonResponse($status, $message, $followup = null) {		
			
		echo json_encode(['status' => $status, 'message' => $message, "followup"=>$followup]);
		exit();
	}
	
	public static function forceDescalation() {
		// 1. Dapatkan dan Validasi Payload Utama
		$payload = Super::getParam("forceDescalation");
		if (empty($payload) || !is_array($payload)) {
			self::sendJsonResponse("error", "Invalid or empty payload.");
			return;
		}

		$loggedInUser = Xbo::getLoginUser(); 
		$wodescalation_array = $payload['wodescalation'] ?? [];
		//$message_template = $payload['message_template'] ?? 'Your reservation was cancelled by management to support a higher priority task.';
		$message_template = 'Aplogize, your reservation was cancelled by management to support a higher priority task.';
		if (empty($wodescalation_array)) {
			self::sendJsonResponse('error', 'No Work Order reservations were selected for de-escalation.');
			return;
		}

		$processedCount = 0;
		$errorMessages = [];
		$items_to_revalidate = []; 

		foreach ($wodescalation_array as $itemToDeescalate) {
			try {
				$wonum = $itemToDeescalate['wonum'];
				$itemnum = $itemToDeescalate['itemnum'];
				$reservedfor_user = $itemToDeescalate['reservedfor']; 

				$set = new Set();
				$reservationsToCancelSet = $set->MboSetRemote("invreserve")
					->setWhere("wonum='{$wonum}' AND itemnum='{$itemnum}' AND status='RESERVED'")
					->getMboSet();

				if ($reservationsToCancelSet->isEmpty()) {
					$errorMessages[] = "No active RESERVED reservation found for WO {$wonum} and item {$itemnum}. It might have been changed by another process.";
					continue;
				}

				// --- Langkah B: Eksekusi Pembatalan untuk setiap reservasi yang ditemukan ---
				for ($i = 0; $i < $reservationsToCancelSet->counts(); $i++) {
					$invreserveMbo = $reservationsToCancelSet->getMbo($i);
					$invreserveid = $invreserveMbo->getString('invreserveid');
					$invreservenum = $invreserveMbo->getString('invreservenum');

					// --- AKSI 1: Batalkan Reservasi ---
					$setUpdate = new Set();
					$setUpdate->MboSetRemote("invreserve")->setWhere("invreserveid='{$invreserveid}'")
						->setValue('status', 'CANCELLED_BY_MGR')
						->setValue('changeby', $loggedInUser)
						->setValue('changedate', Xbo::setEnterDate())
						->change();

					// --- AKSI 2: Buat Jejak Audit ---
					// Kita gunakan reservationswap sebagai log. Ini konsisten.
					$setAudit = new Set();
					$setAudit->MboSetRemote("reservationswap")
						->setValue("invreservenum", $invreservenum) 
						->setValue("target_invreservenum", $invreservenum) // Reservasi yang dibatalkan
						->setValue("requestermsg", "Force de-escalation by {$loggedInUser}. Reason: " . $message_template)
						->setValue("status", "DE-ESCALATED")
						->setValue("enterby", $loggedInUser)
						->setValue("enterdate", Xbo::setEnterDate())
						->add();
					$last_swap_id = self::getLastInsertId("target_invreservenum='$invreservenum'");

					// --- AKSI 3: Kirim Notifikasi "Kabar Buruk" ---
					Super::sendNotification([
						'username'    => $reservedfor_user,
						'message'     => "URGENT: Your reservation for {$itemnum} (WO: {$wonum}) was cancelled by management.",
						'objectname'  => 'invreserve',
						'objectid'    => $invreserveid,
						'objectnum'   => $invreservenum,
						'processname' => 'RESERVATION_DEESCALATED'
					]);

					// Tandai item ini untuk divalidasi ulang nanti
					$items_to_revalidate[] = $itemnum;
					$processedCount++;
				}

			} catch (Exception $e) {
				$errorMessages[] = "A critical error occurred for WO {$wonum}: " . $e->getMessage();
			}
		} // End of foreach loop

		// =================================================================================
		// 3. LANGKAH FINAL: PICU ULANG ALLOCATION ENGINE
		//    Ini adalah langkah paling penting yang menutup siklus.
		// =================================================================================
		if (!empty($items_to_revalidate)) {
			// Ambil item unik untuk mencegah revalidasi ganda yang tidak perlu
			$unique_items = array_unique($items_to_revalidate);
			foreach ($unique_items as $item_to_process) {
				self::revalidateall($item_to_process);
			}
		}

		// 4. Kirim Response Akhir
		if (empty($errorMessages)) {
			self::sendJsonResponse("success", "Successfully de-escalated {$processedCount} reservation(s). Stock has been re-allocated.");
		} else {
			$finalMessage = "Process completed with errors. Success: {$processedCount}. Failures: " . count($errorMessages) . ". Details: " . implode(' | ', $errorMessages);
			self::sendJsonResponse("error", $finalMessage);
		}
	}
	
	public static function sendInquiryDescalation() {
		// 1. Dapatkan dan Validasi Payload Utama
		$payload = Super::getParam("sendInquiryDescalation");
		if (empty($payload) || !is_array($payload)) {
			self::sendJsonResponse("error", "Invalid or empty payload.");
			return;
		}

		$loggedInUser = Xbo::getLoginUser(); // Manajer yang mengirim
		$wodescalation_array = $payload['wodescalation'] ?? [];
		$message_template = $payload['message_template'] ?? 'An inquiry has been sent regarding your reservation.';

		if (empty($wodescalation_array)) {
			self::sendJsonResponse('error', 'No Work Order reservations were selected for this inquiry.');
			return;
		}

		$processedCount = 0;
		$errorMessages = [];
		

		// 2. Loop untuk setiap reservasi yang akan dikirimi inquiry
		foreach ($wodescalation_array as $itemToInquire) {
			try {
				$wonum = $itemToInquire['wonum'];
				$itemnum = $itemToInquire['itemnum'];
				$reservedfor_user = $itemToInquire['reservedfor'];

				$mainSet = new Set();
				$reservationsToInquireSet = $mainSet->MboSetRemote("invreserve")
					->setWhere("wonum='{$wonum}' AND itemnum='{$itemnum}' AND status='RESERVED'")
					->getMboSet();

				if ($reservationsToInquireSet->isEmpty()) {
					$errorMessages[] = "No active RESERVED reservation found for WO {$wonum} and item {$itemnum} to send an inquiry to.";
					continue;
				}
				for ($i = 0; $i < $reservationsToInquireSet->counts(); $i++) {
					$invreserveMbo = $reservationsToInquireSet->getMbo($i);
					$invreserveid = $invreserveMbo->getString('invreserveid');
					$invreservenum = $invreserveMbo->getString('invreservenum');

					// --- AKSI 1: Buat Catatan Inquiry di 'reservationswap' ---
					// Ini memulai thread percakapan baru antara manajer dan teknisi
					$setInquiry = new Set();
					$setInquiry->MboSetRemote("reservationswap")
						->setValue("invreservenum", $invreservenum) 
						->setValue("target_invreservenum", $invreservenum) // Target inquiry
						->setValue("requester_invreservenum", 'MANAGER_INQUIRY')
						->setValue("swaptype", 'DEESCALATION_INQUIRY')
						->setValue("requestermsg", $message_template)
						->setValue("status", "MGR_INQUIRY_SENT") // Status baru yang sangat spesifik
						->setValue("enterby", $loggedInUser) // Manajer yang bertanya
						->setValue("enterdate", Xbo::setEnterDate())
						->add();
					$last_swap_id = self::getLastInsertId("target_invreservenum='$invreservenum' and requester_invreservenum='MANAGER'");

					// --- AKSI 2: Kirim Notifikasi ke Teknisi ---
					Super::sendNotification([
						'username'    => $reservedfor_user,
						'message'     => "{$loggedInUser} has sent an inquiry regarding your reservation for {$itemnum} (WO: {$wonum}).",
						'objectname'  => 'invreserve',
						'objectid'    => $invreserveid,
						'objectnum'   => $invreservenum,
						'processname' => 'MANAGER_INQUIRY_SENT'
					]);

					// --- AKSI 3 (Opsional tapi bagus): Update 'negotiationstatus' di invreserve target ---
					$setUpdateStatus = new Set();
					$setUpdateStatus->MboSetRemote("invreserve")->setWhere("invreserveid='{$invreserveid}'")
						->setValue('negotiationstatus', 'AWAITING_RESPONSE_FROM_YOU')
						->setValue('negotiationrefid', $last_swap_id)
						->change();

					$processedCount++;
				}

			} catch (Exception $e) {
				$errorMessages[] = "A critical error occurred for WO {$wonum}: " . $e->getMessage();
			}
		} // End of foreach loop

		// 3. Kirim Response Akhir
		if (empty($errorMessages)) {
			self::sendJsonResponse("success", "Successfully sent inquiries for {$processedCount} reservation(s).");
		} else {
			$finalMessage = "Process completed with errors. Success: {$processedCount}. Failures: " . count($errorMessages) . ". Details: " . implode(' | ', $errorMessages);
			self::sendJsonResponse("error", $finalMessage, 409);
		}
	}
	public static function findBadActors() {
		
		$payload = Super::getParam("findBadActors");
		$itemnum = $payload['itemnum'] ?? null;
		$startdate = $payload['startdate'] ?? null;
		$enddate = $payload['enddate'] ?? null;

		if (empty($itemnum)) {
			self::sendJsonResponse("error", "Invalid payload. Missing itemnum.");
			return;
		}
		if (empty($startdate) || empty($enddate)) {
			self::sendJsonResponse("error", "Start Date and End Date cannot be empty. Please define a valid analysis period.");
			return;
		}

		// Data untuk respon akhir
		$all_usage_data = [];

		$set = new Set();
		$safe_itemnum = addslashes($itemnum); // Escaping
		$whereClause = "mt.itemnum='$safe_itemnum' AND mt.actualdate >= '$startdate' AND mt.actualdate <= '$enddate' AND mt.transtype='ISSUE'";
		
		$matuseSet = $set->MboSetRemote("matusetrans mt")->setWhere($whereClause)
			->setRelationship("assets", "mt.assetnum = assets.assetnum")
			->MboRemote("mt.assetnum")
			->MboRemote("assets.description")
			->MboRemote("ABS(SUM(mt.quantity)) as total_usage")
			->group("mt.assetnum")
			->order("total_usage DESC")
			->getMboSet();

		//Transform ke array untuk response (lebih ringkas)
		if (!$matuseSet->isEmpty()) {
			for ($i = 0; $i < $matuseSet->counts(); $i++) {
				$matuse = $matuseSet->getMbo($i);
				$assetnum = $matuse->getString("assetnum");
				$assetdesc =  $matuse->getString("description");
				$total_usage = floatval($matuse->getString("total_usage"));
				$all_usage_data[] = ["assetnum" => $assetnum, "asset_description" => $assetdesc, "total_usage" => $total_usage];
			}
		}

		// 5. Persiapkan dan Kirim Respon JSON
		$response = [
			"status" => "success",
			"itemnum" => $itemnum,
			"startdate" => $startdate,
			"enddate" => $enddate,
			"total_usage_from_transactions" => $all_usage_data,
		];

		header('Content-Type: application/json');
		echo json_encode($response);
	}
		
	public static function applyReorderSafety() {
	
		$payload = Super::getParam("applyReorderSafety");

		$itemnum = $payload['itemnum'] ?? null;
		$warehouse = $payload['warehouse'] ?? null; 
		$recom_rop = $payload['recom_rop'] ?? null;
		$recom_safety = $payload['recom_safety'] ?? null;

		if (empty($itemnum) || is_null($recom_rop) || is_null($recom_safety)) {
			self::sendJsonResponse("error", "Invalid payload. Missing itemnum or recommendation values.");
			return;
		}
		
		$set = new Set();
		$updateResult = $set->MboSetRemote("inventory")
			->setWhere("itemnum='$itemnum'")
			->setValue("reorder", $recom_rop) 
			->setValue("safetystock", $recom_safety)
			->change();
		
		// 3. Kirim Response Sukses yang Informatif
		$successMessage = "Parameters for item {$itemnum} have been updated successfully.";
		self::sendJsonResponse("success", $successMessage);
	}
	public static function analyzeItem() {
		// 1. Dapatkan dan Validasi Input
		$payload = Super::getParam(); // Ambil seluruh payload
		$itemnum = $payload['analyzeItem'] ?? null;
		$startdate = $payload['startdate'] ?? null;
		$enddate = $payload['enddate'] ?? null;

		if (empty($itemnum) || !is_string($itemnum)) {
			self::sendJsonResponse("error", "Invalid or empty itemnum provided.");
			return;
		}
		if (empty($startdate) || empty($enddate)) {
			self::sendJsonResponse("error", "Start Date or End Date cannot be empty.");
			return;
		}
		// --- Parameter Bisnis ---
		define('SERVICE_LEVEL_TARGET', 95); // dalam persen
		define('SERVICE_LEVEL_Z_SCORE', 1.65); // Z-Score untuk service level 95%
		
		// Hitung jumlah bulan dalam rentang waktu
		$date1 = new DateTime($startdate);
		$date2 = new DateTime($enddate);
		$interval = $date1->diff($date2);
		$months_in_range = $interval->y * 12 + $interval->m + ($interval->d / 30.44);
		if ($months_in_range < 1) $months_in_range = 1; // Hindari pembagian dengan nol
		
		
		// =================================================================================
		// 2. ANALISIS PERMINTAAN (DEMAND ANALYSIS)
		// =================================================================================
	
		$setDemand = new Set();
		$demandMbo = $setDemand->MboSetRemote("matusetrans")
			->setWhere("itemnum='$itemnum' AND actualdate BETWEEN '$startdate' AND '$enddate' AND transtype='ISSUE'")
			->MboRemote("ABS(SUM(quantity)) as totalIssuedQty, DATE_FORMAT(actualdate, '%Y-%m') as issue_month")
			->group("issue_month")
			->getMboSet();
		
		$totalIssuedQty = 0;
		$monthly_consumption = [];
		if (!$demandMbo->isEmpty()) {
			for ($i=0; $i<$demandMbo->counts(); $i++) {
				$mbo = $demandMbo->getMbo($i);
				$qty = floatval($mbo->getString('totalIssuedQty'));
				$totalIssuedQty += $qty;
				$monthly_consumption[] = $qty;
			}
		}
		
		$avg_consumption_monthly = $totalIssuedQty / $months_in_range;
		
		$max_consumption_monthly = !empty($monthly_consumption) ? max($monthly_consumption) : $avg_consumption_monthly;

		// =================================================================================
		// 3. ANALISIS PASOKAN (SUPPLY ANALYSIS - ACTUAL LEAD TIME)
		// =================================================================================
		$setLeadtime = new Set();
		// DATEDIFF menghitung selisih hari antara tanggal terima dan tanggal order
		$leadtimeMbo = $setLeadtime->MboSetRemote("matrectrans mt")
			->setRelationship("po", "mt.ponum=po.ponum")
			->setRelationship("poline pl", "mt.ponum=pl.ponum AND mt.polinenum=pl.polinenum AND po.ponum = pl.ponum" )
			->setWhere("mt.itemnum = '$itemnum' AND mt.enterdate BETWEEN '$startdate' AND '$enddate'")
			->MboRemote("AVG(DATEDIFF(mt.actualdate, po.orderdate)) as actual_avg_leadtime, MAX(DATEDIFF(mt.actualdate, po.orderdate)) as actual_max_leadtime")
			->getMboSet()->getMbo();
		
		$actual_avg_leadtime_days = $leadtimeMbo ? floatval($leadtimeMbo->getString('actual_avg_leadtime')) : 30;
		$actual_max_leadtime_days = $leadtimeMbo ? floatval($leadtimeMbo->getString('actual_max_leadtime')) : $actual_avg_leadtime_days * 1.5; // Estimasi jika tidak ada data

		// =================================================================================
		// 4. DAPATKAN PARAMETER SAAT INI & HITUNG REKOMENDASI
		// =================================================================================
		$setInv = new Set();
		$invMbo = $setInv->MboSetRemote("inventory")->setWhere("itemnum='$itemnum'")->getMboSet()->getMbo();
		$current_rop = $invMbo ? floatval($invMbo->getString('reorder')) : 0;
		$current_ss = $invMbo ? floatval($invMbo->getString('safetystock')) : 0;
		
		  // Rumus Safety Stock yang lebih canggih: (Max Usage * Max Lead Time) - (Avg Usage * Avg Lead Time)
		$safety_stock_units = ($max_consumption_monthly / 30.44 * $actual_max_leadtime_days) - ($avg_consumption_monthly / 30.44 * $actual_avg_leadtime_days);
		$recommended_ss = ceil(max(0, $safety_stock_units)); // Pastikan tidak negatif

		// Rumus Reorder Point
		$demand_during_leadtime = ($avg_consumption_monthly / 30.44) * $actual_avg_leadtime_days;
		$recommended_rop = ceil($demand_during_leadtime + $recommended_ss);
			
		

		// Aturan 2: Terapkan nilai minimum. Tidak masuk akal punya ROP 0 atau 1.
		// Jika ada konsumsi sama sekali, paling tidak ROP-nya harus beberapa kali lipat dari itu.
		if ($totalIssuedQty > 0) {
			// Jika safety stock hasil perhitungan kurang dari 1, setidaknya jadikan 1.
			if ($recommended_ss < 1) {
				$recommended_ss = 1;
			}
			
			// ROP harus selalu lebih tinggi dari Safety Stock.
			// Aturan sederhana: ROP = SS + (konsumsi sebulan)
			$recalculated_rop = $recommended_ss + ceil($avg_consumption_monthly);
			if ($recommended_rop < $recalculated_rop) {
				$recommended_rop = $recalculated_rop;
			}
		}
		
		// Pastikan ROP tidak pernah lebih rendah dari SS
		if ($recommended_rop <= $recommended_ss) {
			$recommended_rop = $recommended_ss + 1;
		}

		// =================================================================================
		// 5. BANGUN DAN KIRIM RESPON JSON
		// =================================================================================
		 $response = [
			"status" => "success",
			"itemnum" => $itemnum,
			"current_params" => [
				"rop" => round($current_rop),
				"safetystock" => round($current_ss)
			],
			"recommended_params" => [
				"rop" => $recommended_rop,
				"safetystock" => $recommended_ss
			],
			"justification_data" => [
				"avg_consumption_monthly" => round($avg_consumption_monthly, 2),
				"actual_avg_leadtime_days" => round($actual_avg_leadtime_days),
				"service_level" => SERVICE_LEVEL_TARGET // Kirim service level ke frontend
			],
			// Teks justifikasi sekarang bisa lebih kaya
			"justification_text" => sprintf(
				"Analysis & Recommendation: Based on historical data showing an average monthly usage of %.2f units and current supplier lead time of %.0f days, the recommended parameters have been calculated to maintain a %d%% service level 
				while minimizing excess inventory costs.",
				$avg_consumption_monthly,
				$actual_avg_leadtime_days,
				SERVICE_LEVEL_TARGET
			)
		];

		header('Content-Type: application/json');
		echo json_encode($response);
		return;
	
	}
	public static function initiateSwapRequest() {
		// 1. Dapatkan dan Validasi Payload Utama
		$payload = Super::getParam("initiateSwapRequest");
		if (empty($payload) || !is_array($payload)) {
			self::sendJsonResponse("error", "Invalid or empty payload.");
			return;
		}

		$loggedInUser = Xbo::getLoginUser(); // Manajer yang memulai
		$itemnum = $payload['itemnum'] ?? null;
		$requesters_context = $payload['requesters'] ?? [];
		$targets_context = $payload['targets'] ?? [];
		$message_template = $payload['msg_template'] ?? 'A swap has been initiated on your behalf.';

		if (empty($itemnum) || empty($requesters_context) || empty($targets_context)) {
			self::sendJsonResponse('error', 'Payload is missing itemnum, requesters, or targets.');
			return;
		}

		// =================================================================================
		// 2. Translate konteks bisnis menjadi data presisi
		// =================================================================================
		
		// --- Kumpulkan semua kebutuhan Requesters (NEEDS POOL) ---
		$needsPool = [];
		foreach ($requesters_context as $req) {
			$setNeeds = new Set();
			$needsSet = $setNeeds->MboSetRemote("invreserve")
				->setWhere("wonum='{$req['wonum']}' AND itemnum='$itemnum' AND status='WAITING_LIST'")
				->getMboSet();
			if (!$needsSet->isEmpty()) {
				for ($i = 0; $i < $needsSet->counts(); $i++) {
					$mbo = $needsSet->getMbo($i);
					$needsPool[] = [
						'invreservenum' => $mbo->getString('invreservenum'),
						'quantity' => floatval($mbo->getString('quantity')),
						'owner' => $req['reservedfor']
					];
				}
			}
		}

		// --- Kumpulkan semua sumber daya Targets (RESOURCES POOL) ---
		$resourcesPool = [];
		foreach ($targets_context as $tar) {
			$setResources = new Set();
			$resourcesSet = $setResources->MboSetRemote("invreserve")
				->setWhere("wonum='{$tar['wonum']}' AND itemnum='$itemnum' AND status='RESERVED'")
				->getMboSet();
			if (!$resourcesSet->isEmpty()) {
				for ($i = 0; $i < $resourcesSet->counts(); $i++) {
					$mbo = $resourcesSet->getMbo($i);
					$resourcesPool[] = [
						'invreservenum' => $mbo->getString('invreservenum'),
						'quantity' => floatval($mbo->getString('quantity')),
						'owner' => $tar['reservedfor']
					];
				}
			}
		}

		if (empty($needsPool) || empty($resourcesPool)) {
			self::sendJsonResponse('error', 'Could not find specific reservation records for the selected Work Orders.');
			return;
		}

		// =================================================================================
		// 3. EKSEKUSI PENJODOHAN & PEMBUATAN NEGOSIASI
		// =================================================================================
		try {
			$negotiations_created = 0;
			// Loop melalui setiap KEBUTUHAN
			foreach ($needsPool as $need) {
				$remaining_to_fulfill = $need['quantity'];
				
				// Loop melalui setiap SUMBER DAYA untuk memenuhi kebutuhan ini
				foreach ($resourcesPool as &$resource) { // Gunakan '&' untuk pass by reference
					if ($remaining_to_fulfill <= 0) break; // Kebutuhan ini sudah terpenuhi
					if ($resource['quantity'] <= 0) continue; // Sumber daya ini sudah habis

					$swap_qty = min($remaining_to_fulfill, $resource['quantity']);

					// --- BUAT PERMINTAAN NEGOSIASI SPESIFIK ---
					$setNego = new Set();
					$setNego->MboSetRemote("reservationswap")
						->setValue("requester_invreservenum", $need['invreservenum'])
						->setValue("target_invreservenum", $resource['invreservenum'])
						->setValue("invreservenum", $resource['invreservenum'])
						->setValue("swaptype", 'AVAILABILITY_SWAP')
						->setValue("requestermsg", $message_template)
						->setValue("swapqty", $swap_qty)
						->setValue("status", "PENDING")
						->setValue("enterby", $loggedInUser) // Diinisiasi oleh manajer
						->setValue("enterdate", Xbo::setEnterDate())
						->add();
					
					$last_swap_id = self::getLastInsertId("requester_invreservenum='{$need['invreservenum']}' and target_invreservenum='{$resource['invreservenum']}'");

					// --- KIRIM NOTIFIKASI ---
					// Notifikasi ke Target
					Super::sendNotification([
						'username' => $resource['owner'],
						'message' => "A swap request for {$itemnum} was initiated by {$loggedInUser} on behalf of {$need['owner']}.",
						'objectname' => 'invreserve',
						'objectnum' => $resource['invreservenum'],
						'objectid' => $last_swap_id, // Bisa merujuk ke swapnya
						'processname' => 'MANAGER_INITIATED_SWAP'
					]);
					// Notifikasi ke Requester
					Super::sendNotification([
						'username' => $need['owner'],
						'message' => "{$loggedInUser} has initiated a swap request for {$itemnum} on your behalf.",
						'objectname' => 'invreserve',
						'objectnum' => $need['invreservenum'],
						'objectid' => $last_swap_id,
						'processname' => 'MANAGER_INITIATED_SWAP'
					]);

					// Update state untuk loop berikutnya
					$remaining_to_fulfill -= $swap_qty;
					$resource['quantity'] -= $swap_qty; // Kurangi sumber daya yang tersedia
					$negotiations_created++;
				}
			}
		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred during swap initiation: ' . $e->getMessage());
			return;
		}

		// 4. Kirim Response Sukses
		self::sendJsonResponse('success', "{$negotiations_created} negotiation request(s) have been initiated successfully.");
	}
	public static function getSentBy(){
		$loggedInUser = Super::getParam("getSentBy");
		$fullname = (new Set())->MboSetRemote("employees")->setWhere("personid='".(new Set())->MboSetRemote("mxsusers")->setWhere("mxsusers.username='$loggedInUser'")->MboRemote("personid")->getMboSet()->getMbo()->getString("personid")."'")
		->MboRemote("fullname")->getMboSet()->getMbo()->getString("fullname");
		self::sendJsonResponse("success", $fullname);
	}
	public static function acceptSubstitution() {
    // 1. Dapatkan dan Validasi Payload Utama
		$payload = Super::getParam("acceptSubstitution");
		if (empty($payload) || !is_array($payload)) {
			self::sendJsonResponse("error", "Invalid or empty payload.");
			return;
		}

		$loggedInUser = Xbo::getLoginUser();
		$original_invnum = $payload['invreservenum'] ?? null;
		$replacements_array = $payload['replaceWithAlternates'] ?? [];
		
		if (empty($original_invnum) || empty($replacements_array)) {
			self::sendJsonResponse('error', 'Payload is missing original invreservenum or replacement items.');
			return;
		}
		$mainSet = new Set();
		$invreserveSet = $mainSet->MboSetRemote("invreserve")->setWhere("invreservenum='$original_invnum'")->getMboSet();		
			
		if ($invreserveSet->isEmpty()) {
			self::sendJsonResponse('error', "Original reservation {$original_invnum} not found.");
			return;
		}
		$invreserve = $invreserveSet->getMbo();
		$reservedfor = $invreserve->getString("reservedfor");
		$wonum = $invreserve->getString('wonum');
		$status = $invreserve->getString('status');
		$original_needed_qty = floatval($invreserve->getString('quantity'));
		$original_invreserveid = $invreserve->getString('invreserveid');
		
		$set = new Set();
		$woSet = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("targstartdate")->getMboSet();
		$wo = $woSet->getMbo();
		$targstartdate = $wo->getString("targstartdate");
		
		$set = new Set();
		$userMbo = $set->MboSetRemote("mxsusers")->setWhere("personid='{$reservedfor}'")->getMboSet()->getMbo();
		if (!$userMbo || $userMbo->getString('username') !== $loggedInUser) {
			self::sendJsonResponse('error', 'You are not authorized to substitute this reservation.');
			return;
		}
		
		if ($status !== 'WAITING_LIST') {
			self::sendJsonResponse('error', 'Only reservations with WAITING_LIST status can be substituted.');
			return;
		}
		
		// 3. Cek apakah total stok item alternatif MENCUKUPI
		$total_potential_stock = 0;
		foreach ($replacements_array as $alt) {
			$setCheck = new Set();
			$invMbo = $setCheck->MboSetRemote("invfifolifocost")
							  ->setWhere("itemnum='{$alt['itemnum']}' AND warehouse='{$alt['warehouse']}' AND quantity > 0")
							  ->MboRemote("SUM(quantity) as total_available")
							  ->getMboSet()->getMbo();
			$total_potential_stock += $invMbo ? floatval($invMbo->getString('total_available')) : 0;
		}

		if ($total_potential_stock < $original_needed_qty) {
			
			self::sendJsonResponse('error', "The selected alternates do not have enough total stock ({$total_potential_stock}) to fulfill the original need ({$original_needed_qty}).");
			return;
		}

		// =================================================================================
		// 4. EKSEKUSI (Dalam Transaksi Implisit dari Class Set)
		// =================================================================================
		try {
			// --- AKSI A: TUTUP Permintaan Lama ---
			$mainSet = new Set();
			$mainSet->MboSetRemote("invreserve")->setWhere("invreservenum='$original_invnum'")
				->setValue('status', 'SUBSTITUTED')
				->setValue('changeby', $loggedInUser)
				->setValue('changedate', Xbo::setEnterDate())
				->change();

			$remaining_to_fulfill = $original_needed_qty;

			// --- AKSI B: LOOP melalui Strategi Penggantian dan Buat Permintaan Baru ---
			foreach ($replacements_array as $alt) {
				if ($remaining_to_fulfill <= 0) break; // Berhenti jika kebutuhan sudah terpenuhi

				$alt_itemnum = $alt['itemnum'];
				$alt_warehouse = $alt['warehouse'];
				
				// Cek lagi stok real-time HANYA untuk kombinasi ini
				$setRealtime = new Set();
				$realtimeMbo = $setRealtime->MboSetRemote("invfifolifocost")
										   ->setWhere("itemnum='$alt_itemnum' AND warehouse='$alt_warehouse' AND quantity > 0")
										   //->MboRemote("SUM(quantity) as total_available")
										   ->MboRemote("quantity as total_available")
										   ->MboRemote("racknum")
										   ->getMboSet()->getMbo();
				$realtime_stock = $realtimeMbo ? floatval($realtimeMbo->getString('total_available')) : 0;
				$racknum = $realtimeMbo->getString('racknum');
				$qty_to_request_from_this_source = min($realtime_stock, $remaining_to_fulfill);

				if ($qty_to_request_from_this_source > 0) {
					// Buat record invreserve BARU dengan status WAITING_LIST
					$new_invnum = "RES-SUB-" . $original_invnum . "-" . substr(uniqid(), -4);
					
					$setNewReserve = new Set();
					$setNewReserve->MboSetRemote("invreserve")
						->setValue('invreservenum', $new_invnum)
						->setValue('status', 'WAITING_LIST')
						->setValue('quantity', $qty_to_request_from_this_source)
						->setValue('itemnum', $alt_itemnum)
						->setValue('warehouse', $alt_warehouse)
						->setValue('wonum', $wonum)
						->setValue('racknum', $racknum)
						->setValue('targstartdate', $targstartdate)
						->setValue('reservedfor', $reservedfor)
						->setValue('originatorid', $original_invreserveid) // Link ke permintaan asli
						->setValue('enterby', $loggedInUser)
						->setValue('enterdate', Xbo::setEnterDate())
						->add();
					
					self::revalidateall($alt_itemnum); 
					
					$remaining_to_fulfill -= $qty_to_request_from_this_source;
				}
			}
		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred during substitution: ' . $e->getMessage());
			return;
		}

		self::sendJsonResponse('success', 'Substitution request processed successfully. Check notifications for allocation status.');
	}
	
	public static function substituteAndReallocate() {
		// 1. Dapatkan dan Validasi Payload Utama
		$payload = Super::getParam();
		$original_itemnum = $payload['original_itemnum'] ?? null;
		$alternates_array = $payload['substituteAndReallocate'] ?? [];

		if (empty($original_itemnum) || empty($alternates_array) || !is_array($alternates_array)) {
			self::sendJsonResponse('error', 'Invalid payload. Missing original_itemnum or substitute items.');
			return;
		}

		$loggedInUser = Xbo::getLoginUser();
		$processTime = Xbo::setEnterDate();
		
		// =================================================================================
		// 2. KUMPULKAN KEBUTUHAN & SUMBER DAYA (THE DEMAND & SUPPLY)
		// =================================================================================
		
		function setDemand($original_itemnum){
			$setDemand = new Set();
			$waitingListQueueSet = $setDemand->MboSetRemote("invreserve")
			->setWhere("itemnum='{$original_itemnum}' AND status='WAITING_LIST'")
			->order("enterdate ASC")
			->getMboSet();
			
			return $waitingListQueueSet;
		}
		// --- A. Dapatkan "Demand Queue": Semua user yang menunggu item original (FIFO) ---
		$waitingListQueueSet = setDemand($original_itemnum);
	
		if ($waitingListQueueSet->isEmpty()) {
			self::sendJsonResponse('success', "No users are currently on the waiting list for '{$original_itemnum}'. No action was taken.");
			return;
		}
		$countWaitingListQueue = $waitingListQueueSet->counts();
		
		// --- B. Bangun "Supply Pool": Kumpulan batch fisik dari semua item alternatif (FIFO) ---
		$supplyPool = [];
		$error_messages = [];
		foreach ($alternates_array as $alt) {
			
			$alt_itemnum = $alt['itemnum'];
			$alt_warehouse = $alt['warehouse'];
			
			// =================================================================================
			// Cek ketersediaan BERSIH (net availability) untuk item alternatif ini.
			// =================================================================================
			
			$setCheck = new Set();
			$onHandMbo = $setCheck->MboSetRemote("invfifolifocost")
								 ->setWhere("itemnum='{$alt_itemnum}' AND warehouse='{$alt_warehouse}'")
								 ->MboRemote("SUM(quantity) as total_on_hand")
								 ->getMboSet()->getMbo();
			$total_on_hand = $onHandMbo ? floatval($onHandMbo->getString('total_on_hand')) : 0;
			
			
			// Hitung total yang sudah di-RESERVED
			$setCheck = new Set();
			$reservedMbo = $setCheck->MboSetRemote("invreserve")
								   ->setWhere("itemnum='{$alt_itemnum}' AND warehouse='{$alt_warehouse}' AND status='RESERVED'")
								   ->MboRemote("SUM(quantity) as total_reserved")
								   ->getMboSet()->getMbo();
			$total_reserved = $reservedMbo ? floatval($reservedMbo->getString('total_reserved')) : 0;
			

			// Hitung stok yang tersedia 
			$net_available_stock = $total_on_hand - $total_reserved;
			// kalau nggak ada, lewati
		
			if ($net_available_stock <= 0) {
				$error_messages[] = "<li> Insufficient available stock for alternative item <strong>{$alt_itemnum}</strong> in warehouse <strong>{$alt_warehouse}</strong>. Net stock is <strong>0</strong> or less after reservation deduction.</li>";
				continue;
			}
			//--------
	
			$setSupply = new Set();
			 // Ambil issuetype dari item alternatif ini
            $invMbo = $setSupply->MboSetRemote("inventory")->setWhere("itemnum='{$alt_itemnum}' AND warehouse='{$alt_warehouse}'")->getMboSet()->getMbo();
            if (!$invMbo) continue; // Lewati jika item tidak ada di inventory
			$issuetype = $invMbo->getString('issuetype');
            $sorter = "costdate ASC"; // Default FIFO
			if ($issuetype === "LIFO") $sorter = "costdate DESC";
            elseif ($issuetype === "EXPDATE") $sorter = "expdate ASC";
			
			$setSupply = new Set();
			 $fifoStockSet = $setSupply->MboSetRemote("invfifolifocost")
				->setWhere("itemnum='{$alt['itemnum']}' AND warehouse='{$alt['warehouse']}' AND quantity > 0")
				->order($sorter) 
				->getMboSet();
				
				
			if (!$fifoStockSet->isEmpty()) {
				for ($i = 0; $i < $fifoStockSet->counts(); $i++) {
					$mbo = $fifoStockSet->getMbo($i);
					$supplyPool[] = [
						"itemnum" => $mbo->getString('itemnum'),
						"invfifolifocostid" => $mbo->getString('invfifolifocostid'),
						"available_qty" => floatval($mbo->getString('quantity')),
						"warehouse" => $mbo->getString('warehouse'),
						"racknum" => $mbo->getString('racknum'),
						"conditiontype" => $mbo->getString('conditiontype'),
						"costdate"=> $mbo->getString('costdate'),
						"expdate"=> $mbo->getString('expdate')
					];
				}
			}
		} // end iteration
		
		
		
		if (empty($supplyPool)) {
			self::sendJsonResponse('error', 'The selected alternate items have no available stock.');
			return;
		}
		// --- C. Apakah TOTAL stok dari SEMUA alternatif cukup? ---
		$total_potential_stock = 0;
		foreach ($supplyPool as $supply) {
			$total_potential_stock += $supply['available_qty'];
		}
		
		$waitingListQueueSet =  setDemand($original_itemnum);
		$total_needed_from_queue = 0;
		$invreservernumList = [];
		for ($i = 0; $i < $countWaitingListQueue; $i++) {
			$waitingListQueue = $waitingListQueueSet->getMbo($i);
			$invreservenum = $waitingListQueue->getString("invreservenum");
			$invreservernumList [] = ["invreservenum"=>$invreservenum];
			$total_needed_from_queue += floatval($waitinglistQty);
			
		}
		
	
		if ($total_potential_stock < $total_needed_from_queue) {
		
			self::sendJsonResponse('error', 
			"The selected alternates do not have enough total stock ({$total_potential_stock}) to fulfill the entire waiting list demand ({$total_needed_from_queue})." ,
			$invreservernumList);
			return;
		}
		
		// =================================================================================
		// 3. EKSEKUSI PENJODOHAN & REALOKASI (THE MATCHMAKING ENGINE)
		// =================================================================================
		$waitingListQueueSet = setDemand($original_itemnum);
		$processedCount = 0;
		try {
			// Loop melalui setiap user di antrian (Demand Queue)
			for ($i = 0; $i < $countWaitingListQueue; $i++) {
				$userInQueue = $waitingListQueueSet->getMbo($i);
				$original_invreserveid = $userInQueue->getString('invreserveid');
				$remaining_to_fulfill = floatval($userInQueue->getString('quantity'));
				$original_requestedQty = floatval($userInQueue->getString('quantity'));
				$fulfilled_qty = 0;				
				$fulfilled_details = [];
				
				foreach ($supplyPool as $supply_index => $supplyBatch) { 
					if ($remaining_to_fulfill <= 0) break;// Kebutuhan user ini sudah terpenuhi
					if ($supplyBatch['available_qty'] <= 0) continue;// Batch ini sudah habis

					$take_qty = min($remaining_to_fulfill, $supplyBatch['available_qty']);
					// --- BUAT RESERVASI BARU YANG SUDAH DI-APPROVE ---
					$new_invnum = "RES-SUB-" . substr(uniqid(), -6);
					$setNewReserve = new Set();
					$setNewReserve->MboSetRemote("invreserve")
						->setValue('invreservenum', $new_invnum)
						->setValue('status', 'RESERVED') // Langsung RESERVED karena ini aksi otoritatif
						->setValue('quantity', $take_qty)
						->setValue('itemnum', $supplyBatch['itemnum'])
						->setValue('warehouse', $supplyBatch['warehouse'])
						->setValue('racknum', $supplyBatch['racknum'])
						->setValue('wonum', $userInQueue->getString('wonum'))
						->setValue('reservedfor', $userInQueue->getString('reservedfor'))
						->setValue('originatorid', $original_invreserveid)
						->setValue('enterby', $loggedInUser)
						->setValue('enterdate', $processTime)
						->add();
						
					$new_invreserveid = self::getLastInsertInvreserveID("invreservenum='$new_invnum'");
					// --- BUAT ALOKASI FISIK (HARD ALLOCATION) ---
					$setAllocation = new Set();
					$setAllocation->MboSetRemote("invallocations")
						->setValue("invreserveid", $new_invreserveid)
						->setValue("invfifolifocostid", $supplyBatch['invfifolifocostid'])
						->setValue("allocatedqty", $take_qty)
						->setValue("allocationdate", $processTime)
						->add();
					
					// Update state Supply Pool untuk iterasi berikutnya
					$supplyPool[$supply_index]['available_qty'] -= $take_qty; // Kurangi stok di pool
					$remaining_to_fulfill -= $take_qty;
					$fulfilled_qty += $take_qty;
					
					 $fulfilled_details[] = "{$take_qty} pc(s) of {$supplyBatch['itemnum']}";
				}
				 unset($supplyBatch);
				// --- TUTUP ATAU UPDATE PERMINTAAN LAMA ---
				if ($fulfilled_qty > 0) {
					$new_original_qty = $original_requestedQty - $fulfilled_qty;
					$new_status = ($new_original_qty <= 0) ? 'SUBSTITUTED' : 'WAITING_LIST';
					
					$setUpdateOld = new Set();
					$setUpdateOld->MboSetRemote("invreserve")->setWhere("invreserveid='{$original_invreserveid}'")
						->setValue('status', $new_status)
						//->setValue('quantity', $new_original_qty)
						->setValue('changeby', $loggedInUser)
						->setValue('changedate', $processTime)
						->change();
					
					$new_invreserveid = self::getLastInsertInvreserveID("invreservenum='$new_invnum'");
					$fulfilled_summary = implode(', ', $fulfilled_details);
					$fullmessage = "Update from management: Your request for {$original_itemnum} has been fulfilled with: {$fulfilled_summary}.";
					$notificationData = [
						'username'    => self::getUsernameFromInvReserve($new_invnum),
						'message'     => $full_message,
						'objectname'  => 'invreserve',
						'objectid'    => $new_invreserveid,
						'objectnum'   => $new_invnum,
						'processname' => 'FORCE_ALTERNATE_CHANGE' // Proses yang sangat spesifik
					];
					// --- KIRIM NOTIFIKASI ---
					Super::sendNotification($notificationData);
					$processedCount++;
				}
			}
		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred during re-allocation: ' . $e->getMessage());
			return;
		}
		
		if (!empty($error_messages)) {
			$list_error = implode(' ', $error_messages);
			$finalMessage = "Some records cannot be issued. <br> Details: <hr> <ul>{$list_error}</ul>";
			self::sendJsonResponse("error", $finalMessage); 
		}else{
			self::sendJsonResponse('success', "Successfully substituted and re-allocated for {$processedCount} original reservation(s).");
		}
		
	}
	
	public static function notifyWaitingListUsers() {
		
		$payload = Super::getParam();
		$original_itemnum = $payload['original_itemnum'] ?? null;
		$alternates_offered = $payload['alternates_offered'] ?? null;

		if (empty($original_itemnum) || empty($alternates_offered) || !is_array($alternates_offered)) {
			self::sendJsonResponse('error', 'Invalid payload. Missing original_itemnum or alternates_offered.');
			return;
		}

		$loggedInUser = Xbo::getLoginUser(); 
		
		$offer_message_part = "The following alternate item(s) are available: ";
		$alternate_details = [];
		foreach ($alternates_offered as $alt) {
			$alternate_details[] = "{$alt['itemnum']} (Stock: {$alt['quantity']})";
		}
		$offer_message_part .= implode(', ', $alternate_details) . ".";

		// 3. Dapatkan Daftar Semua User di Waiting List untuk Item Original
		$setWaitingList = new Set();
		$waitingListSet = $setWaitingList->MboSetRemote("invreserve")
			->setWhere("itemnum='{$original_itemnum}' AND status='WAITING_LIST'")
			->MboRemote("reservedfor") // Ini berisi personid
			->MboRemote("invreservenum") // Ambil satu contoh invreservenum untuk referensi
			->group("reservedfor")
			->getMboSet();

		if ($waitingListSet->isEmpty()) {
			self::sendJsonResponse('success', 'No users are currently on the waiting list for this item.');
			return;
		}

		$notified_count = 0;

		// 4. Loop Melalui Setiap User dan Kirim Notifikasi Personal
		for ($i = 0; $i < $waitingListSet->counts(); $i++) {
			$waitingMbo = $waitingListSet->getMbo($i);
			$personid = $waitingMbo->getString('reservedfor');
			
			// Dapatkan username dari personid
			$setUser = new Set();
			$userMbo = $setUser->MboSetRemote("mxsusers")->setWhere("personid='{$personid}'")->getMboSet()->getMbo();
			if (!$userMbo) continue; // Lewati jika user tidak ditemukan
			$username = $userMbo->getString('username');
			
			$full_message = "Update for item {$original_itemnum}: An alternative is available. {$offer_message_part} Would you like to consider a substitution?";

			$notificationData = [
				'username'    => $username,
				'message'     => $full_message,
				'objectname'  => 'invreserve',
				'objectid'    => $waitingMbo->getString('invreserveid'), // Referensi ke salah satu WL mereka
				'objectnum'   => $waitingMbo->getString('invreservenum'),
				'processname' => 'ALTERNATE_SUGGESTED' // Proses yang sangat spesifik
			];
			
			if (Super::sendNotification($notificationData)) {
				$notified_count++;
			}
		}

		self::sendJsonResponse('success', "Successfully sent alternate item suggestions to {$notified_count} user(s) on the waiting list.");
	}
	public static function getItemDesc(){
		$itemnum = Super::getParam("getItemDesc");
		echo json_encode(["itemdesc"=>(new Set())->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->getMboSet()->getMbo()->getString("description")]);
	}
	public static function addtoCart() {
		$payload = $_POST;
		$itemnum = $payload["addtoCart"] ?? null;
		
		$warehouse = $payload["warehouse"] ?? null; 
		if (empty($itemnum)) {
			Set::throws(["status"=>"error", "message"=>"Item number is required."]);
			return;
		}

		// =================================================================================
		// LANGKAH 1: KUMPULKAN 4 METRIK KUNCI UNTUK ANALISIS
		// =================================================================================

		// --- Metrik 1 & 2: Waiting & Reserved (dari invreserve) ---
		$setDemand = new Set();
		$demandMbo = $setDemand->MboSetRemote("invreserve")
			->setWhere("itemnum='$itemnum'")
			->MboRemote("SUM(IF(status = 'WAITING_LIST', quantity, 0)) as total_waiting")
			->MboRemote("SUM(IF(status = 'RESERVED', quantity, 0)) as total_reserved")
			->getMboSet()->getMbo();
		
		$total_waiting = floatval($demandMbo->getString('total_waiting'));
		$total_reserved = floatval($demandMbo->getString('total_reserved'));
		
		// --- Metrik 3: On Hand (dari invfifolifocost) ---
		$setSupply = new Set();
		$supplyMbo = $setSupply->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum'")->MboRemote("SUM(quantity) as total_qty")->getMboSet()->getMbo();
		$total_on_hand = floatval($supplyMbo->getString("total_qty"));

		// --- Metrik 4: On Order (menggunakan helper yang sudah ada) ---
		// Pastikan fungsi getQuantityOnTheWay() bisa diakses di sini.
		$total_on_order = self::getQuantityOnTheWay($itemnum);

		// =================================================================================
		// LANGKAH 2: HITUNG KUANTITAS & BUAT REMARKS DINAMIS
		// =================================================================================

		$total_demand = $total_waiting + $total_reserved;
		$total_supply = $total_on_hand + $total_on_order;
		$shortfall = $total_demand - $total_supply;

		// Validasi: Jangan buat PR jika tidak ada kekurangan.
		if ($shortfall <= 0) {
			Set::throws(["status"=>"error", "message"=>"No purchase required. Current supply ({$total_supply}) already meets or exceeds demand ({$total_demand})."]);
			return;
		}

		// Kuantitas yang akan diorder adalah sebesar kekurangannya, dibulatkan ke atas.
		$quantity = ceil($shortfall); 

		// Bangun remarks dinamis menggunakan sprintf untuk kebersihan.
		$remarks = sprintf(
			"Automated PR from Resource Conflict Assessment for item: %s. Justification: Total demand significantly exceeds current and incoming supply. 
			DATA SNAPSHOT --> Waiting: %.2f, Reserved: %.2f, On Hand: %.2f, On Order: %.2f.",
			$itemnum,
			$total_waiting,
			$total_reserved,
			$total_on_hand,
			$total_on_order
		);

		// =================================================================================
		// LANGKAH 3: LENGKAPI DATA LAIN & INSERT KE invcart
		// =================================================================================
		
		
		$whereClause = "itemnum='$itemnum'";
		$mainSet = new Set();
		$itemCondMbo = $mainSet->MboSetRemote("itemcondition")->setWhere("$whereClause AND conditionrate='100'")->getMboSet()->getMbo();
		$conditiontype = $itemCondMbo->getString("conditiontype");
		
		$mainSet = new Set();
		$unitCostMbo = $mainSet->MboSetRemote("invfifolifocost")->setWhere($whereClause)->MboRemote("MAX(unitcost) as unitcost")->getMboSet()->getMbo();
		$unitcost = floatval($unitCostMbo->getString("unitcost"));
		
		$mainSet = new Set();
		$itemMbo = $mainSet->MboSetRemote("item")->setWhere($whereClause)->getMboSet()->getMbo();
		$orderunit = $itemMbo->getString("orderunit");
		
		$linecost = $quantity * $unitcost;
		//$gldebitacct = $warehouse ? Xbo::getwhgl($warehouse, "noprint") : null; // Pastikan warehouse ada
		$gldebitacct = null;
		$setCart = new Set();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$object = "invreserve";
		$objectnum = "CUMMULATIVES";
		$setCart->MboSetRemote("invcart")
			->setValue("itemnum", $itemnum)
			->setValue("remarks", $remarks) 
			->setValue("warehouse", $warehouse)
			->setValue("quantity", $quantity) 
			->setValue("conditiontype", $conditiontype)
			->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)
			->setValue("orderunit", $orderunit)
			->setValue("gldebitacct", $gldebitacct)
			->setValue("enterdate", $enterdate)
			->setValue("enterby", $enterby)
			->setValue("objectnum", $objectnum)
			->setValue("object", $object)
			->setValue("replenishfrom", "BOTTLENECK_RESERVATION") 
			->add();
		
		Set::throws(["status"=>"success", "message"=>"Item {$itemnum} with quantity {$quantity} has been added to the cart. Check your cart from <b>Inventory</b> Application - <b>Replenishment Workbench</b>"]);
	}
	
	/**
 * Menghitung total kuantitas item yang masih "On The Way" dari Purchase Orders
 * yang statusnya belum diterima penuh.
 */
	private static function getQuantityOnTheWay($itemnum) {
		$set = new Set();
		// Query ini menggabungkan PO Line dan penerimaan 
		$polineSet = $set->MboSetRemote("poline pl")
			->setWhere("pl.itemnum='$itemnum' AND po.status IN ('APPR', 'SENT') AND pl.receiptstatus != 'COMPLETE'")
			->setRelationship("po", "po.ponum = pl.ponum")
			->MboRemote("pl.orderqty")
			->MboRemote("IFNULL((SELECT SUM(quantity) FROM matrectrans mt WHERE mt.ponum = pl.ponum AND mt.polinenum = pl.polinenum), 0) AS receivedqty")
			->getMboSet();

		$stockontheway = 0;
		if (!$polineSet->isEmpty()) {
			for ($i = 0; $i < $polineSet->counts(); $i++) {
				$poline = $polineSet->getMbo($i);
				$orderqty = floatval($poline->getString("orderqty"));
				$receivedqty = floatval($poline->getString("receivedqty"));
				$stockontheway += ($orderqty - $receivedqty);
			}
		}
		return $stockontheway;
	}
	
	public static function resourceConflict() {
	
		// 1. Dapatkan "Problem Items": Semua item yang ada di Waiting List
		// Kita gunakan satu query cerdas untuk mendapatkan total waiting & reserved sekaligus.
		$setProblems = new Set();
		$problemItemsSet = $setProblems->MboSetRemote("invreserve")
			->setWhere("status IN ('WAITING_LIST', 'RESERVED')")
			->MboRemote("itemnum")
			->MboRemote("SUM(IF(status = 'WAITING_LIST', quantity, 0)) as total_waiting")
			->MboRemote("SUM(IF(status = 'RESERVED', quantity, 0)) as total_reserved")
			->group("itemnum")
			->order("total_waiting DESC") // Langsung diurutkan berdasarkan yang paling kritis
			->getMboSet();

		if ($problemItemsSet->isEmpty()) {
			echo json_encode(['categories' => [], 'series' => []]); // Kirim data kosong jika tidak ada masalah
			return;
		}

		// 2. Siapkan array untuk menampung data chart
		$chartData = [
			'categories' => [], // Untuk label sumbu X (nama item)
			'series' => [
				'reserved' => [],
				'waiting' => [],
				'balance' => [],
				'on_order' => []
			]
		];

		// 3. Loop melalui "Problem Items" untuk melengkapi data
		for ($i = 0; $i < $problemItemsSet->counts(); $i++) {
			$problemMbo = $problemItemsSet->getMbo($i);
			$itemnum = $problemMbo->getString('itemnum');

			// --- Data dari query pertama ---
			$total_reserved = floatval($problemMbo->getString('total_reserved'));
			$total_waiting = floatval($problemMbo->getString('total_waiting'));

			// --- Dapatkan Current Balance (Stok Fisik) ---
			$setInv = new Set();
			$invMbo = $setInv->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum'")->MboRemote("SUM(quantity) as total_qty")->getMboSet()->getMbo();
			$current_balance = $invMbo ? floatval($invMbo->getString("total_qty")) : 0;

			// --- Dapatkan Quantity On The Way (menggunakan helper kita) ---
			$qty_on_the_way = self::getQuantityOnTheWay($itemnum);

			// --- Isi array data chart ---
			$chartData['categories'][] = $itemnum;
			$chartData['series']['reserved'][] = $total_reserved;
			$chartData['series']['waiting'][] = $total_waiting;
			$chartData['series']['balance'][] = $current_balance;
			$chartData['series']['on_order'][] = $qty_on_the_way;
		}

		// 4. Ubah struktur data menjadi format yang disukai eCharts
		$finalChartData = [
			'categories' => $chartData['categories'],
			'series' => [
				[
					'name' => 'Reserved',
					'type' => 'bar',
					'stack' => 'Demand', // Tumpuk permintaan
					'data' => $chartData['series']['reserved']
				],
				[
					'name' => 'Waiting List',
					'type' => 'bar',
					'stack' => 'Demand',
					'data' => $chartData['series']['waiting']
				],
				[
					'name' => 'Current Balance',
					'type' => 'line',
					'data' => $chartData['series']['balance']
				],
				[
					'name' => 'On Order',
					'type' => 'line',
					'data' => $chartData['series']['on_order']
				]
			]
		];

		// 5. Kirim response JSON
		header('Content-Type: application/json');
		echo json_encode($finalChartData);
		return;
	}
	
	public static function showHideButtons() {
    // Helper untuk response
    function sendButtonResponse($negotiate, $reply, $changeStatus) {
        echo json_encode([
            'showNegotiate' => $negotiate,
            'showReply' => $reply,
            'showChangeStatus' => $changeStatus
        ]);
        exit();
    }

    $loggedInUser = Xbo::getLoginUser();
    // Asumsi payload dari JS dikirim dengan benar, misal: $_POST['showHideButtons']
    $viewed_invreservenum = $_POST['showHideButtons'] ?? null; 

    if (!$viewed_invreservenum) {
        sendButtonResponse(false, false, false); // Jika tidak ada nomor reservasi, sembunyikan semua
    }

    // Ambil Data Reservasi yang Sedang Dilihat
    $setViewed = new Set();
    $viewedReservationSet = $setViewed->MboSetRemote("invreserve")->setWhere("invreservenum='$viewed_invreservenum'")->getMboSet();
    if ($viewedReservationSet->isEmpty()) {
        sendButtonResponse(false, false, false); // Reservasi tidak ditemukan
    }
    $viewedReservation = $viewedReservationSet->getMbo();
    
    // Dapatkan detail penting dari reservasi yang dilihat
    $viewed_itemnum = $viewedReservation->getString('itemnum');
    $viewed_status = $viewedReservation->getString('status');
    $viewed_owner_personid = $viewedReservation->getString('reservedfor');
    $viewed_negotiation_status = $viewedReservation->getString('negotiationstatus');

    // Dapatkan username pemilik reservasi
    $setOwnerUser = new Set();
    $viewed_owner_username = $setOwnerUser->MboSetRemote("mxsusers")->setWhere("personid='$viewed_owner_personid'")->getMboSet()->getMbo()->getString('username');
   
    // Inisialisasi Keputusan (Default: Sembunyikan Semua)
    $showNegotiate = false;
    $showReply = false;
    $showChangeStatus = false;

    
    // --- LOGIKA untuk tombol "Negotiate This Reservation" ---
    // Muncul jika:
    // 1. User yang login BUKAN pemilik reservasi yang dilihat.
    // 2. Status reservasi yang dilihat adalah RESERVED.
    // 3. User yang login punya kebutuhan (WAITING_LIST) untuk item yang sama.
    if ($loggedInUser !== $viewed_owner_username && $viewed_status === 'RESERVED') {
    
		// =========================================================================
		// Dapatkan personid dari username yang login
		// =========================================================================
		$setPerson = new Set();
		$personMbo = $setPerson->MboSetRemote("mxsusers")->setWhere("username='$loggedInUser'")->getMboSet()->getMbo();
		
		// Tambahkan pengecekan jika user tidak ditemukan
		if ($personMbo) {
			$personLoggedIn = $personMbo->getString("personid");
			
			// Sekarang, gunakan $personLoggedIn untuk mencari kebutuhan user
			$setCheckNeed = new Set();
			$userNeedSet = $setCheckNeed->MboSetRemote("invreserve")
										->setWhere("reservedfor='$personLoggedIn' AND itemnum='$viewed_itemnum' AND status='WAITING_LIST'")
										->getMboSet();
										
			if (!$userNeedSet->isEmpty()) {
				$showNegotiate = true;
			}
		}
	}

    // --- LOGIKA untuk tombol "Reply" dan "Change Status" ---
	   $activeNegoStatuses = [
			'NEGOTIATION_PENDING',
			'NEGOTIATING',
			'AWAITING_RESPONSE_FROM_YOU', // Status dari Manager Inquiry
			'MGR_INQUIRY_SENT' // Status alternatif lain yang mungkin
		];
    if (in_array($viewed_negotiation_status, $activeNegoStatuses)) {
        // harus tahu siapa saja yang terlibat dalam negosiasi ini.
        // asumsikan kedua pihak sudah ditandai dengan status negosiasi yang sama.
        
        if ($loggedInUser === $viewed_owner_username) {
            // Jika yang login adalah PEMILIK BARANG (Target)
            $showReply = true;
            $showChangeStatus = true; // Dia punya kuasa untuk Accept/Reject
        } else {
            // Jika yang login BUKAN pemilik barang, kita cek apakah dia adalah requester yang sah
            // (Untuk simpelnya, asumsikan jika status nego aktif, dia pasti terlibat)
            $showReply = true;
            $showChangeStatus = false; // Requester tidak bisa Accept/Reject
        }
    }
    
    sendButtonResponse($showNegotiate, $showReply, $showChangeStatus);
}
	
	
	public static function revalidateall($for_itemnum = null) {
		$loggedInUser = Xbo::getLoginUser();
		$validationTime = Xbo::setEnterDate();
		$url = Super::getApplicationURL("socialreservation");
			
		$whereClause = "status='WAITING_LIST'";		
		if($for_itemnum != null){
			$whereClause .= " and itemnum = '$for_itemnum'";
		}
		// 1. Dapatkan daftar ITEM unik yang ada di waiting list. 
		$setItems = new Set();
		$itemsInWaitingListSet = $setItems->MboSetRemote("invreserve")
										->setWhere($whereClause)
										->MboRemote("itemnum")
										->MboRemote("warehouse")
										->MboRemote("wonum")
										->getMboSet("distinct");
		
		if ($itemsInWaitingListSet->isEmpty()) {
			self::sendJsonResponse("success", "No items in waiting list to process."); 
			return;
		}

		// 2. Loop untuk SETIAP ITEM UNIK
		for ($i = 0; $i < $itemsInWaitingListSet->counts(); $i++) {
			$itemMbo = $itemsInWaitingListSet->getMbo($i);
			$itemnum = $itemMbo->getString("itemnum");
			$warehouse =  $itemMbo->getString("warehouse");
			$wonum =  $itemMbo->getString("wonum");
			// --- Langkah A: Hitung Stok Tersedia untuk item ini ---
			$setInv = new Set();
			$invMbo = $setInv->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum'")->MboRemote("SUM(quantity) as total_qty")->getMboSet()->getMbo();
			// No need null check, just use floatval
			$total_qty_on_hand = floatval($invMbo->getString("total_qty"));
			
			$setRes = new Set();
			$resMbo = $setRes->MboSetRemote("invreserve")->setWhere("itemnum='$itemnum' AND status='RESERVED'")->MboRemote("SUM(quantity) as total_qty_reserved")->getMboSet()->getMbo();
			// No need null check, just use floatval
			$total_qty_reserved = floatval($resMbo->getString("total_qty_reserved"));
			
			$available_stock = $total_qty_on_hand - $total_qty_reserved;
			
			// --- Langkah B: Ambil SEMUA antrian untuk item ini (FIFO) ---
			$queueWhereClause = "status='WAITING_LIST' AND itemnum='$itemnum'";
			$setQueue = new Set();
			$queueSet = $setQueue->MboSetRemote("invreserve")
								 ->setWhere($queueWhereClause) 
								 ->order("enterdate ASC")
								 ->getMboSet();

			// --- Langkah C: Loop melalui antrian dan alokasikan stok ---
			for ($j = 0; $j < $queueSet->counts(); $j++) {
				$queueMbo = $queueSet->getMbo($j);
				$invreserveid = $queueMbo->getString("invreserveid");
				$invreservenum = $queueMbo->getString("invreservenum");
				$quantity_needed = floatval($queueMbo->getString("quantity"));
				
				$setCheckCostType = new Set();
				$issuetype = $setCheckCostType->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("issuetype")->getMboSet()->getMbo()->getString("issuetype");
				$sorter = "costdate ASC";
				if($issuetype == "LIFO") $sorter = "costdate DESC";
				elseif($issuetype == "EXPDATE") $sorter = "expdate ASC";
				if ($available_stock >= $quantity_needed) {
					
					// =================================================================================
					// BLOK HARD ALLOCATION 
					// =================================================================================
					$setFifo = new Set();
					$fifoStockSet = $setFifo->MboSetRemote("invfifolifocost")
											->setWhere("itemnum='$itemnum' AND quantity > 0")
											->order($sorter)
											->getMboSet();					
					
					$remaining_needed = $quantity_needed;
					// 2. Loop melalui batch fisik untuk "memanen" stok
					
					$fifo_counts = $fifoStockSet->counts();
					for ($k = 0; $k < $fifo_counts; $k++) {
						if ($remaining_needed <= 0) break; 
						$fifoMbo = $fifoStockSet->getMbo();
						$expdate = $fifoMbo->getString("expdate");
						$invfifolifocostid = $fifoMbo->getString('invfifolifocostid');
						
						if($issuetype == "EXPDATE"){
							
							if(empty($expdate)) {
								self::sendJsonResponse('error', 'The issue type for item $itemnum is "Expired Date", but no date has been entered. Please input the appropriate expiration date.');
							}
						}
						$take_from_this_batch = min(floatval($fifoMbo->getString('quantity')), $remaining_needed);
						
						// 3. INSERT ke tabel invallocations
						$setAllocation = new Set();
						$setAllocation->MboSetRemote("invallocations")
							->setValue("invreserveid", $invreserveid)
							->setValue("invfifolifocostid", $invfifolifocostid)
							->setValue("allocatedqty", $take_from_this_batch)
							->setValue("allocationdate", $validationTime)
							->add();
						
						$remaining_needed -= $take_from_this_batch;
					}
					
					// 4. Setelah alokasi selesai, update status invreserve utama
					if ($remaining_needed <= 0) {
					
						$setUpdate = new Set();
						$setUpdate->MboSetRemote("invreserve")->setWhere("invreservenum='$invreservenum'")
							->setValue('status', 'RESERVED')
							->setValue('lastvalidationstatus', 'ALLOCATED')
							->setValue('lastvalidationstatusdate', $validationTime)
							->setValue('changeby', $loggedInUser)
							->setValue('changedate', $validationTime)
							->change();
							
						$available_stock -= $quantity_needed;
						
						$recipient_username = self::getUsernameFromInvReserve($invreservenum); 
						$notificationData = [
							'username'    => $recipient_username,
							'message'     => "GOOD NEWS! {$quantity_needed} pc(s) of item {$itemnum} you were waiting for has been allocated.",
							'objectname'  => 'invreserve',
							'objectid'    => $invreserveid, // ID dari record invreserve yang baru di-update
							'objectnum'   => $invreservenum, // Nomor reservasi yang sekarang sudah RESERVED
							'processname' => 'RESERVATION_ALLOCATED' // Nama proses yang jelas
						];
						
						Super::sendNotification($notificationData);
						
					} else {
						// Kasus langka: Stok tersedia cukup, tapi saat di-loop habis (race condition).
						// Rollback alokasi yang sudah dibuat untuk record ini.
						$setInvAlloc = new Set();
						$setInvAlloc->MboSetRemote("invallocations")->setWhere("invreserveid='$invreserveid'")->remove();
					}
				} else {
					// KASUS: STOK TIDAK CUKUP
					$lastValidationStatus = ($available_stock > 0) ? 'INSUFFICIENT_STOCK' : 'SKIPPED_IN_QUEUE';
					
					$setUpdate = new Set();
					$setUpdate->MboSetRemote("invreserve")->setWhere("invreservenum='$invreservenum'")
						->setValue('lastvalidationstatus', $lastValidationStatus)
						->setValue('lastvalidationstatusdate', $validationTime)
						->change();
				}
			} // End of inner loop (antrian)
		} // End of outer loop (item)
		self::sendJsonResponse('success', 'Revalidation process completed.');
	}
	public static function rejectNegotiation() {
		
		$loggedInUser = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$payload = $_POST['rejectNegotiation'] ?? null;

		if (!$payload) {
			self::sendJsonResponse('error', 'Invalid input data.');
		}

		// Ekstrak data dari payload
		$requester_invnum = $payload['requester_invreservenum'] ?? null;
		$target_invnum    = $payload['target_invreservenum'] ?? null;
		$remarks          = $payload['remarks'] ?? 'Rejected without remarks.'; // Default jika kosong

		// =================================================================================
		// 2. VALIDASI
		// =================================================================================
		
		$setRequester = new Set();
		$requesterReservationSet = $setRequester->MboSetRemote("invreserve")->setWhere("invreservenum='$requester_invnum'")->getMboSet();
		if ($requesterReservationSet->isEmpty()) {
			self::sendJsonResponse('error', 'Rejection failed. Requester Reservation no longer exists.');
		}
		$requesterReservation = $requesterReservationSet->getMbo();
		$requesterOwnerPersonId = $requesterReservation->getString('reservedfor');
		$requester_invreserveid = $requesterReservation->getString("invreserveid");	
		
		$requester_username = self::getUsernameFromInvReserve($requester_invnum);
		
		// Validasi A: Otorisasi. Apakah user yang login adalah pemilik target?
		$setTarget = new Set();
		$targetReservationSet = $setTarget->MboSetRemote("invreserve")->setWhere("invreservenum='$target_invnum'")->getMboSet();
		if ($targetReservationSet->isEmpty()) {
			self::sendJsonResponse('error', 'Rejection failed. Target Reservation no longer exists.');
		}
		$targetReservation = $targetReservationSet->getMbo();
		$targetOwnerPersonId = $targetReservation->getString('reservedfor');
		$invreserveid = $targetReservation->getString("invreserveid");
		
		$setTargetUser = new Set();
		$targetUsername = $setTargetUser->MboSetRemote("mxsusers")->setWhere("personid='$targetOwnerPersonId'")->getMboSet()->getMbo()->getString('username');
		if ($loggedInUser !== $targetUsername) {
			self::sendJsonResponse('error', 'You are not authorized to reject this request.');
		}
		
		// Validasi B: Cek apakah negosiasi masih aktif dan bisa ditolak.
		$target_negotiationstatus = $targetReservationSet->getMbo()->getString('negotiationstatus');
		$finalStatuses = ['SWAP_ACCEPTED', 'SWAP_REJECTED', 'SWAP_CANCELLED'];
		if (in_array($target_negotiationstatus, $finalStatuses)) {
			self::sendJsonResponse('error', 'This negotiation has already been finalized.');
			return;
		}

		// =================================================================================
		// 3. EKSEKUSI 2 AKSI INTI (Lebih Sederhana dari Approve)
		// =================================================================================
		
		try {
			// --- AKSI A: INSERT Catatan Penolakan di Thread Negosiasi ---
			$setInsertFinalSwap = new Set();
			$setInsertFinalSwap->MboSetRemote("reservationswap")
				->setValue("invreservenum", $target_invnum)
				->setValue("requester_invreservenum", $requester_invnum)
				->setValue("target_invreservenum", $target_invnum)
				->setValue("requestermsg", $remarks) // Remarks penolakan dari approver
				->setValue("status", "REJECTED") // Status final
				->setValue("enterby", $loggedInUser)
				->setValue("enterdate", $enterdate)
				->add();
			
			$set = new Set();
			$reservationswapSet = $set->MboSetRemote("reservationswap")->setWhere("invreservenum='$target_invnum' and requester_invreservenum='$requester_invnum'")->MboRemote("max(reservationswapid) as reservationswapid")->getMboSet();
			$reservationswap = $reservationswapSet->getMbo();
			$last_swap_id = $reservationswap->getString("reservationswapid");
			

			// --- AKSI B: UPDATE Status di invreserve untuk KEDUA BELAH PIHAK ---
			$finalNegotiationStatus = 'SWAP_REJECTED';
			$setUpdateInvreserve = new Set();
			$setUpdateInvreserve->MboSetRemote("invreserve")
				->setWhere("invreservenum IN ('$target_invnum', '$requester_invnum')")
				->setValue("negotiationstatus", $finalNegotiationStatus)
				->setValue("negotiationrefid", $last_swap_id) // Referensi ke pesan penolakan
				->change();
				
			
			
			// =================================================================================
			// --- AKSI Kirim Notifikasi PENOLAKAN ke REQUESTER (Anja) ---
			// =================================================================================
			$notificationData = [
				'username'    => $requester_username, // Penerimanya adalah si requester
				'message'     => "Update: Your negotiation for {$target_invnum} has been REJECTED by {$loggedInUser}.",
				'objectname'  => 'invreserve',
				'objectid'    => $requester_invreserveid, // ID record Anja
				'objectnum'   => $requester_invnum, // Arahkan ke reservasi Anja agar dia tahu nasibnya
				'processname' => 'NEGOTIATION_REJECTED'
			];

			Super::sendNotification($notificationData);	
			
		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred during the rejection process. ' . $e->getMessage());
		}

		// 4. Kirim Response Sukses
		self::sendJsonResponse('success', 'Negotiation has been rejected successfully.');
	}
	public static function approveNegotiation() {
   
		// 1. Dapatkan User & Data Input		
		$loggedInUser = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$payload = $_POST['approveNegotiation'] ?? null; 

		if (!$payload) {
			self::sendJsonResponse('error', 'Invalid input data.'); //400
		}

		// Ekstrak data dari payload
		$requester_invnum = $payload['requester_invreservenum'] ?? null;
		$target_invnum    = $payload['target_invreservenum'] ?? null;
		$givenqty         = floatval($payload['givenqty'] ?? 0);
		$remarks          = $payload['remarks'] ?? 'Approved';

		// =================================================================================
		// 2. JUST-IN-TIME VALIDATION (Mencegah Race Condition)		
		// =================================================================================

		// Validasi A: Ambil dan validasi reservasi TARGET (milik Jona)
		$setTarget = new Set();
		$targetReservationSet = $setTarget->MboSetRemote("invreserve")->setWhere("invreservenum='$target_invnum'")->getMboSet();
		if ($targetReservationSet->isEmpty()) {
			self::sendJsonResponse('error', 'Approval failed. Target reservation no longer exists.'); //404
		}
		$targetReservation = $targetReservationSet->getMbo();

		// Validasi B: Otorisasi. Apakah user yang login adalah pemilik target?
		$targetOwnerPersonId = $targetReservation->getString('reservedfor');
		$setTargetUser = new Set();
		$targetUsername = $setTargetUser->MboSetRemote("mxsusers")->setWhere("personid='$targetOwnerPersonId'")->getMboSet()->getMbo()->getString('username');
		if ($loggedInUser !== $targetUsername) {
			self::sendJsonResponse('error', 'You are not authorized to approve this request.'); //403
		}
		
		// Validasi C: Cek kondisi terakhir dari reservasi TARGET
		if ($targetReservation->getString('status') !== 'RESERVED') {
			sendJsonResponse('error', 'Approval failed. Target item is no longer in RESERVED status.'); // 409 Conflict
		}
		
		 if ($givenqty <= 0) {
			self::sendJsonResponse('error', 'Approval failed. Quantity to give must be a positive number greater than zero.'); // 400 Bad Request
		}
		
		$targetCurrentQty = floatval($targetReservation->getString('quantity'));
		if ($targetCurrentQty < $givenqty) {
			self::sendJsonResponse('error', 'Approval failed. Not enough quantity available in the target reservation.'); // 409 Conflict
		}

		// Validasi D: Ambil dan validasi reservasi REQUESTER 
		$setRequester = new Set();
		$requesterReservationSet = $setRequester->MboSetRemote("invreserve")->setWhere("invreservenum='$requester_invnum'")->getMboSet();
		if ($requesterReservationSet->isEmpty()) {
			self::sendJsonResponse('error', 'Approval failed. Requester reservation no longer exists.');// 404
		}
		$requesterReservation = $requesterReservationSet->getMbo();		
		 // =========================================================================
		// VALIDASI E: Memastikan Kuantitas yang Diberikan Sesuai Kebutuhan Requester
		// =========================================================================
		$requesterOriginalQty = floatval($requesterReservation->getString('quantity'));
		
		if ($givenqty > $requesterOriginalQty) {
			$errorMessage = "Approval failed. The quantity being given ({$givenqty}) exceeds the requester's original needed quantity ({$requesterOriginalQty}).";
			self::sendJsonResponse('error', $errorMessage); // 409 Conflict
		}
		

		// =================================================================================
		// 3. EKSEKUSI 3-AKSI INTI + 1 AKSI PENUTUP (The Transaction Block)
		//    Dibungkus try-catch untuk menangani kegagalan dari framework Set()
		// =================================================================================
		
		try {
			// --- AKSI 1: UPDATE TARGET (Jona's Reservation) ---
			$newTargetQty = $targetCurrentQty - $givenqty;
			$newTargetStatus = ($newTargetQty <= 0) ? 'TAKEN_BY_SWAP' : 'RESERVED';			
			$setUpdateTarget = new Set();
			$setUpdateTarget->MboSetRemote("invreserve")->setWhere("invreservenum='$target_invnum'")
				->setValue('quantity', $newTargetQty)
				->setValue('status', $newTargetStatus)
				->setValue('changeby', $loggedInUser)
				->setValue('changedate', date("Y-m-d H:i:s"))
				->change();

			// --- AKSI 2: INSERT BARU UNTUK REQUESTER (Requester's New Reserved Item) ---
			$newSwapInvreserveNum = "RES-SWAP-" . $target_invnum . "-" . substr(uniqid(), -4);
			
			$wonum = $requesterReservation->getString('wonum');
			//get Work Order 
			$set = new Set();
			$targstartdate = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("targstartdate")->getMboSet()->getMbo()->getString("targstartdate");
			$setInsertRequester = new Set();
			$setInsertRequester->MboSetRemote("invreserve")
				->setValue("invreservenum", $newSwapInvreserveNum)
				->setValue("status", "RESERVED")
				->setValue("quantity", $givenqty)
				->setValue("reservedfor", $requesterReservation->getString('reservedfor'))
				->setValue("wonum", $wonum)
				->setValue("targstartdate", $targstartdate)
				->setValue("mrnum", $requesterReservation->getString('mrnum'))
				->setValue("itemnum", $requesterReservation->getString('itemnum'))
				->setValue("warehouse", $requesterReservation->getString('warehouse'))
				->setValue("conditiontype", $requesterReservation->getString('conditiontype'))
				->setValue("racknum", $requesterReservation->getString('racknum'))
				->setValue("swappedfrom", $target_invnum) // Jejak audit
				->setValue("enterby", $loggedInUser)
				->setValue("enterdate", $enterdate)
				->add();

			// --- AKSI 3: UPDATE REQUESTER'S OLD WAITING LIST ---
			$originalRequesterQty = floatval($requesterReservation->getString('quantity'));
			$newRequesterNeededQty = $originalRequesterQty - $givenqty;
			$newRequesterStatus = ($newRequesterNeededQty <= 0) ? 'FULFILLED' : 'WAITING_LIST';

			$setUpdateRequesterOld = new Set();
			$setUpdateRequesterOld->MboSetRemote("invreserve")->setWhere("invreservenum='$requester_invnum'")
				->setValue('quantity', $newRequesterNeededQty)
				->setValue('status', $newRequesterStatus)
				->setValue('changeby', $loggedInUser)
				->setValue('changedate', $enterdate)
				->change();

			// =================================================================================
			// AKSI 4 (REVISI): INSERT Catatan Penutup di Thread Negosiasi
			// =================================================================================
			$setInsertFinalSwap = new Set();
			$setInsertFinalSwap->MboSetRemote("reservationswap")
				->setValue("invreservenum", $target_invnum)
				->setValue("requester_invreservenum", $requester_invnum)
				->setValue("target_invreservenum", $target_invnum)
				->setValue("swaptype", $requesterReservation->getString('swaptype')) // Ambil tipe dari request asli
				->setValue("requestermsg", $remarks) // Remarks/pesan final dari approver
				->setValue("status", "ACCEPTED") // Status final
				->setValue("enterby", $loggedInUser)
				->setValue("enterdate", $enterdate)
				->add();
				
			// =================================================================================
			// AKSI 5 & 6 (REVISI): UPDATE Status di invreserve untuk KEDUA BELAH PIHAK
			// =================================================================================
			//5. Get The Max ID for negotiation record
			$set = new Set();
			$reservationswapSet = $set->MboSetRemote("reservationswap")->setWhere("invreservenum='$target_invnum' and requester_invreservenum='$requester_invnum'")->MboRemote("max(reservationswapid) as reservationswapid")->getMboSet();
			$reservationswap = $reservationswapSet->getMbo();
			$last_swap_id = $reservationswap->getString("reservationswapid");
			
			//6. Update invreserve
			$finalNegotiationStatus = 'SWAP_ACCEPTED'; // Status yang jelas dan deskriptif
	
			$setUpdateInvreserve = new Set();
			// Gunakan klausa WHERE IN (...) untuk mengupdate kedua record sekaligus. Lebih efisien.
			$setUpdateInvreserve->MboSetRemote("invreserve")
				->setWhere("invreservenum IN ('$target_invnum', '$requester_invnum')")
				->setValue("negotiationstatus", $finalNegotiationStatus)
				->setValue("negotiationrefid", $last_swap_id) // Referensi ke pesan penutup
				->change();
			
			// =================================================================================
			//  AKSI 4 : RE-QUEUE UNTUK TARGET (Nasib Target) ---
			//    Ini adalah aksi "balas budi" sistem yang memastikan kebutuhan Jona
			//    tetap tercatat tanpa pengecekan stok yang berisiko (race condition).
			// =================================================================================
			$unique_part = substr(uniqid(), -6); 
			$requeueInvreserveNum = "RES-REQUEUE-" . $target_invnum . "-" . $unique_part;
			
			
			$setRequeueTarget = new Set();
			$setRequeueTarget->MboSetRemote("invreserve")
				->setValue("invreservenum", $requeueInvreserveNum)
				->setValue("status", "WAITING_LIST") // Selalu WAITING_LIST, aman dan logis.
				->setValue("quantity", $givenqty) // Dia butuh kembali sejumlah yang dia berikan.
				->setValue("reservedfor", $targetReservation->getString('reservedfor')) // Pemiliknya tetap Jona
				->setValue("wonum", $targetReservation->getString('wonum'))
				->setValue("targstartdate", $targetReservation->getString('targstartdate'))
				->setValue("mrnum", $targetReservation->getString('mrnum'))
				->setValue("itemnum", $targetReservation->getString('itemnum'))
				->setValue("warehouse", $targetReservation->getString('warehouse'))
				->setValue("conditiontype", $targetReservation->getString('conditiontype'))
				->setValue("racknum", $targetReservation->getString('racknum'))
				->setValue("swappedfrom", $target_invnum) // Bisa ditambahkan jejak juga
				->setValue("enterby", $loggedInUser)
				->setValue("enterdate", $enterdate)
				->add();
				
			// =================================================================================
			// --- AKSI 5 (REVISI): AUTO-REJECT SEMUA REQUEST LAIN UNTUK ITEM TARGET ---
			// =================================================================================
			$rejectedStatus = 'SWAP_REJECTED_AUTO'; // Status penolakan otomatis yang jelas

			// A. Dapatkan daftar SEMUA requester lain yang masih PENDING/NEGOTIATING
			$setOtherRequesters = new Set();
			$otherRequestersSet = $setOtherRequesters->MboSetRemote("reservationswap")
				->setWhere("target_invreservenum = '$target_invnum' AND requester_invreservenum != '$requester_invnum' AND status IN ('PENDING', 'NEGOTIATING')")
				->MboRemote("requester_invreservenum")
				->getMboSet("distinct");

			if (!$otherRequestersSet->isEmpty()) {
				$rejectedInvreserveNums = []; 
				for ($i = 0; $i < $otherRequestersSet->counts(); $i++) {
					$mbo = $otherRequestersSet->getMbo();
					$rejectedInvreserveNums[] = "'" . $mbo->getString('requester_invreservenum') . "'";
				}

				if (!empty($rejectedInvreserveNums)) {
					// Gabungkan semua nomor reservasi menjadi satu string untuk klausa IN
					$rejectedListStr = implode(',', $rejectedInvreserveNums);

					// B. Buat catatan penutup sebagai INSERT BARU untuk setiap requester yang ditolak
					$finalRemarksForRejected = 'This request was automatically rejected because the item was allocated to another priority request.';
					$rejectedStatus = 'REJECTED'; // Status final untuk thread ini
					$systemUser = 'SYSTEM'; // Aktor yang melakukan penolakan otomatis

					// Loop melalui setiap requester yang kalah
					foreach ($rejectedInvreserveNums as $rejectedNum) {
						// Hilangkan tanda kutip yang kita tambahkan tadi untuk query IN
						$cleanRejectedNum = str_replace("'", "", $rejectedNum);

						$setInsertReject = new Set();
						$setInsertReject->MboSetRemote("reservationswap")
							->setValue("requester_invreservenum", $cleanRejectedNum)
							->setValue("target_invreservenum", $target_invnum)
							->setValue("swaptype", "AVAILABILITY_SWAP")
							->setValue("requestermsg", $finalRemarksForRejected)
							->setValue("status", $rejectedStatus)
							->setValue("enterby", $systemUser)
							->setValue("enterdate", $enterdate)
							->add();
							
							
						// =================================================================================
						// --- AKSI NOTIFIKASI #3: Kirim Kabar Buruk ke SEMUA YANG KALAH ---
						// =================================================================================
						$loser_username = self::getUsernameFromInvReserve($cleanRejectedNum); // Asumsi ada helper function

						$notificationDataForLoser = [
							'username'    => $loser_username,
							'message'     => "Update: Your negotiation for {$itemnum} was automatically closed because the item has been allocated to another priority request.",
							'objectname'  => 'invreserve',
							'objectid'    => self::getInvreserveIdFromNum($cleanRejectedNum),
							'objectnum'   => $cleanRejectedNum,
							'processname' => 'NEGOTIATION_AUTO_REJECTED'
						];
						Super::sendNotification($notificationDataForLoser);	
					}
						
					// Dapatkan ID dari swap yang di-approve untuk referensi
					// Asumsi $last_swap_id sudah didapatkan dari Aksi Penutup sebelumnya
					
					// C. UPDATE massal status di 'invreserve' untuk SEMUA yang ditolak
					$setRejectInvreserves = new Set();
					$setRejectInvreserves->MboSetRemote("invreserve")
						->setWhere("invreservenum IN ($rejectedListStr)") 
						->setValue("negotiationstatus", $rejectedStatus)
						->setValue("negotiationrefid", $last_swap_id) // Arahkan ke bukti approval pemenang
						->change();
				}
			}
			
			$requester_username =  self::getUsernameFromInvReserve($requester_invnum);
			$target_username = self::getUsernameFromInvReserve($target_invnum);
			$notificationDataForLoser = [
				'username'    => $requester_username,
				'message'     => "GOOD NEWS! The negotiation request for {$itemnum} has been approved by {$target_username}. Request Number: {$requester_invnum}",
				'objectname'  => 'invreserve',
				'objectid'    => self::getInvreserveIdFromNum($requester_invnum),
				'objectnum'   => $requester_invnum,
				'processname' => 'NEGOTIATION_APPROVED'
			];
			Super::sendNotification($notificationDataForLoser);	
		} catch (Exception $e) {
			self::sendJsonResponse('error', 'A critical error occurred during the approval process. ' . $e->getMessage());
		}

		// 4. Kirim Response Sukses
		self::sendJsonResponse('success', 'Negotiation has been approved successfully. Inventory updated.');
	}
	
	public static function getInvreserveIdFromNum($invreservenum) {
		$set = new Set();
		$invreserveSet = $set->MboSetRemote("invreserve")->setWhere("invreservenum='$invreservenum'")->getMboSet();
		if($invreserveSet->isEmpty()){
			return null;
		}
		$invreserve = $invreserveSet->getMbo();
		$invreserveid = $invreserve->getString("invreserveid");
		return $invreserveid;
	}
	public static function getUsernameFromInvReserve($invreservenum) {
		// Cari personid dari invreserve
		$set = new Set();
		$invreserveSet = $set->MboSetRemote("invreserve")->setWhere("invreservenum='$invreservenum'")->getMboSet();
		if($invreserveSet->isEmpty()){
			return null;
		}
		$invreserve = $invreserveSet->getMbo();
		$personid = $invreserve->getString("reservedfor");
		
		// Cari username dari mxsusers
		$set = new Set();
		$userSet = $set->MboSetRemote("mxsusers")->setWhere("personid='$personid'")->getMboSet();
		
		if($userSet->isEmpty()){
			return null;
		}
		$user = $userSet->getMbo();
		$username = $user->getString("username");
		return $username;
	}
	
	public static function replyToNegotiation() {
		// 1. Ambil user yang login dari session
		$loggedInUser = Xbo::getLoginUser();
		
		// 2. Ambil dan decode JSON yang disederhanakan
		$payload = $_POST["replyToNegotiation"];
		$originalSwapId = $payload['original_swap_id'];
		$replyMessage = $payload['reply_message'];

		// 3. VALIDASI & AMBIL KONTEKS DARI DATABASE (Sumber Kebenaran)
		$setOriginal = new Set();
		$originalSwapSet = $setOriginal->MboSetRemote("reservationswap")
										->setWhere("reservationswapid='$originalSwapId'")
										->getMboSet();
		// Validasi: Apakah negosiasi ini ada?
		if ($originalSwapSet->isEmpty()) {
			self::sendJsonResponse("error", "Negotiation thread not found");
			return;
		}
		$originalSwap = $originalSwapSet->getMbo();
		
		$target_invreservenum = $originalSwap->getString("target_invreservenum");
		$requester_invreservenum = $originalSwap->getString("requester_invreservenum");
		
		//Validasi: Apakah Invreserve sudah selesai negosiasi.
		
		$set = new Set();
		// OPTIMASI: Ambil kedua status dalam SATU kali query untuk efisiensi.
		$invreserveSet = $set->MboSetRemote("invreserve")
							 ->setWhere("invreservenum IN ('$target_invreservenum', '$requester_invreservenum')")
							 ->getMboSet();

		// Inisialisasi variabel
		$negotiationStatuses = [];
		if (!$invreserveSet->isEmpty()) {
			for ($i = 0; $i < $invreserveSet->counts(); $i++) {
				$mbo = $invreserveSet->getMbo($i);
				// Simpan status ke array asosiatif untuk akses mudah
				$negotiationStatuses[$mbo->getString('invreservenum')] = $mbo->getString('negotiationstatus');
			}
		}

		$target_negotiationstatus = $negotiationStatuses[$target_invreservenum] ?? null;
		$requester_negotiationstatus = $negotiationStatuses[$requester_invreservenum] ?? null;

		// =========================================================================
		// VALIDASI BARU: Cek apakah thread sudah ditutup (Finalized)
		// =========================================================================
		$finalStatuses = ['SWAP_ACCEPTED', 'SWAP_REJECTED', 'SWAP_CANCELLED'];

		// Jika status TARGET atau status REQUESTER sudah final, maka tolak.
		if (in_array($target_negotiationstatus, $finalStatuses) || in_array($requester_negotiationstatus, $finalStatuses)) {
			self::sendJsonResponse('error', 'This negotiation has already been finalized and cannot be replied to.'); // 409 Conflict
			return;
		}
	
		
		$target_username = self::getUsernameFromInvReserve($target_invreservenum);
		$requester_username = self::getUsernameFromInvReserve($requester_invreservenum);
		
		
		if (!$target_username || !$requester_username) {			
			self::sendJsonResponse("error", "Could not identify one of the parties in the negotiation.");
			return;
		}
		if ($loggedInUser !== $requester_username && $loggedInUser !== $target_username) {
			self::sendJsonResponse("error", "You are not authorized to reply to this negotiation."); 
			return;
		}
		
		// Ambil semua konteks dari record asli, BUKAN dari payload
		$requester_invnum = $originalSwap->getString('requester_invreservenum');
		$target_invnum = $originalSwap->getString('target_invreservenum');
		$swaptype = $originalSwap->getString('swaptype');
		
		//GET ID Target Invreserve
		
		$set = new Set();
		$target_invreserveid = $set->MboSetRemote("invreserve")->setWhere("invreservenum='$target_invnum'")->MboRemote("invreserveid")->getMboSet()->getMbo()->getString("invreserveid");

		// 4. INSERT BALASAN SEBAGAI RECORD BARU
		$enterdate = Xbo::setEnterDate();
		$setReply = new Set();
		$setReply->MboSetRemote("reservationswap")
			->setValue("invreservenum", $target_invnum)
			->setValue("requester_invreservenum", $requester_invnum) // Diambil dari DB
			->setValue("target_invreservenum", $target_invnum)   // Diambil dari DB
			->setValue("swaptype", $swaptype)                     // Diambil dari DB
			->setValue("requestermsg", $replyMessage)             // Pesan baru dari payload
			->setValue("status", "NEGOTIATING")                   // Status baru
			->setValue("enterby", $loggedInUser)                  // User dari session
			->setValue("enterdate", $enterdate)
			->add();

		//update negotiation status and max id from reservationswap 
		//5. Get The Max ID for negotiation record
		$set = new Set();
		$reservationswapSet = $set->MboSetRemote("reservationswap")->setWhere("invreservenum='$target_invreservenum' and requester_invreservenum='$requester_invreservenum'")->MboRemote("max(reservationswapid) as reservationswapid")->getMboSet();
		$reservationswap = $reservationswapSet->getMbo();
		$reservationswapid = $reservationswap->getString("reservationswapid");
		
		// =================================================================================
		// --- AKSI Kirim Notifikasi ke "PIHAK LAIN" dalam Percakapan ---
		// =================================================================================

		// Kita sudah punya variabel-variabel ini dari validasi sebelumnya:
		// $loggedInUser, $requester_username, $target_username

		// Tentukan siapa penerima notifikasi
		$recipient_username = '';
		if ($loggedInUser === $requester_username) {
			// Jika yang sedang membalas adalah si REQUESTER,
			// maka penerimanya adalah si TARGET.
			$recipient_username = $target_username;
		} else {
			// Jika yang sedang membalas adalah si TARGET,
			// maka penerimanya adalah si REQUESTER.
			$recipient_username = $requester_username;
		}

		// Siapkan paket data notifikasi
		$notificationData = [
			'username'    => $recipient_username, // Penerima yang sudah ditentukan secara dinamis
			'message'     => "{$loggedInUser} has replied to your negotiation for item {$itemnum}.",
			'objectname'  => 'invreserve',
			'objectid'    => $target_invreserveid,
			'objectnum'   => $target_invnum, 
			'processname' => 'NEGOTIATION_REPLIED'
		];

		// Kirim notifikasi
		Super::sendNotification($notificationData);

		
		//6. UPDATE STATUS DI invreserve (untuk kedua belah pihak)
		$set = new Set();
		$set->MboSetRemote("invreserve")->setWhere("invreservenum='$target_invreservenum' or invreservenum='$requester_invreservenum'")
		->setValue("negotiationstatus", "negotiating")
		->setValue("negotiationrefid", $reservationswapid)
		->change();		
		self::sendJsonResponse("success", "Reply has been submited."); 
		
	}
	
	public static function negotiateReservation() {
		// Helper function untuk mengirim response JSON yang konsisten
				
		$loggedInUser = Xbo::getLoginUser();

		// Ambil raw JSON dari body request
		
		$data = $_POST;

		// Cek jika JSON valid dan memiliki struktur yang diharapkan
		if (json_last_error() !== JSON_ERROR_NONE || !isset($data['negotiateReservation'])) {
			self::sendJsonResponse('error', 'Invalid JSON payload.'); // 400 Bad Request
		}
		$payload = $data['negotiateReservation'];
		

		// 2. Extract & Sanitize Payload
		// Ambil semua data yang kita butuhkan dari payload
		$requester =  $payload['requester'];
		$target_invreservenum = $payload['target_invreservenum'] ?? null;		
		$requester_invreservenum = $payload['requesterdata']['requester_invreservenum'] ?? null;		
		$quantity_to_swap = $payload['requesterdata']['quantity_to_swap'] ?? null;
		$swaptype = $payload['requesterdata']['swaptype'] ?? null;
		//echo "$quantity_to_swap $swaptype $requester_invreservenum >> $target_invreservenum";
		$comments = $payload['comments'] ?? ''; // Jadikan string kosong jika tidak ada

		// Pastikan semua field wajib ada
		if (!$target_invreservenum || !$requester_invreservenum || !$quantity_to_swap || !$swaptype) {
			self::sendJsonResponse('error', 'Missing required fields in payload.'); //400
		}
		
		// =================================================================================
		// 3. CRITICAL VALIDATION 
		// =================================================================================

		// Validasi A: Apakah requester_invreservenum benar-benar milik user yang login?
		$setRequester = new Set();
		$requesterReservationSet = $setRequester->MboSetRemote("invreserve")
												->setWhere("invreservenum='$requester_invreservenum'")
												->getMboSet();
		$requesterReservation = $requesterReservationSet->getMbo();
		
		//if (!$requesterReservation || $requesterReservation->getString('reservedfor') !== $loggedInUser) {
		if (strtolower($requester) !==strtolower($loggedInUser)) {
			//sendJsonResponse('error', 'Invalid requester reservation number or you do not have permission.', 403); // 403 Forbidden
			self::sendJsonResponse('error', "Invalid requester reservation number or you do not have permission. The user <b>$requester</b> is not currently logged in.");
		}

		// Validasi B: Apakah target_invreservenum ada, aktif, dan siap dinegosiasikan?
		$setTarget = new Set();
		$targetReservationSet = $setTarget->MboSetRemote("invreserve")
											->setWhere("invreservenum='$target_invreservenum'")
											->getMboSet();
		$targetReservation = $targetReservationSet->getMbo();
		$itemnum = $targetReservation->getString('itemnum');
		if (!$targetReservation) {
			self::sendJsonResponse('error', 'Target reservation not found.'); // 404 Not Found
		}
		if ($targetReservation->getString('status') !== 'RESERVED') {
			self::sendJsonResponse('error', 'Target reservation is not in RESERVED status and cannot be negotiated.'); // 409 Conflict
		}

		// Validasi C: Apakah kuantitas yang diminta valid?
		$targetQty = floatval($targetReservation->getString('quantity'));
		$requestedQty = floatval($quantity_to_swap);

		if ($requestedQty <= 0) {
			self::sendJsonResponse('error', 'Quantity to swap must be greater than zero.'); //400 
		}
		if ($requestedQty > $targetQty) {
			self::sendJsonResponse('error', 'Cannot request more quantity than the target has.'); // 409 Conflict
		}

		// 4. Prepare & Execute Database Insert
		// Jika semua validasi lolos, kita siap untuk menyimpan permintaan negosiasi.
		
		$enterdate = date("Y-m-d H:i:s");

		try {
			
			$set = new Set();
			$set->MboSetRemote("reservationswap")
				->setValue("invreservenum", $target_invreservenum)
				->setValue("requester_invreservenum", $requester_invreservenum)
				->setValue("target_invreservenum", $target_invreservenum)
				->setValue("swaptype", $swaptype)
				->setValue("requestermsg", $comments) 
				->setValue("swapqty", $requestedQty) 
				->setValue("status", "PENDING") 
				->setValue("enterby", $loggedInUser) 
				->setValue("enterdate", $enterdate)
				->add();
				
				
			//update negotiation status and max id from reservationswap 
			//1. Get The Max id
			$set = new Set();
			$reservationswapSet = $set->MboSetRemote("reservationswap")->setWhere("invreservenum='$target_invreservenum' and requester_invreservenum='$requester_invreservenum'")->MboRemote("max(reservationswapid) as reservationswapid")->getMboSet();
			$reservationswap = $reservationswapSet->getMbo();
			$reservationswapid = $reservationswap->getString("reservationswapid");
			
			// Update for target invreservenum
			$set = new Set();
			$set->MboSetRemote("invreserve")->setWhere("invreservenum='$target_invreservenum'")
			->setValue("negotiationstatus", 'negotiating')
			->setValue("negotiationrefid", $reservationswapid)
			->change();
			
			// Update for requester invreservenum
			$set = new Set();
			$set->MboSetRemote("invreserve")->setWhere("invreservenum='$requester_invreservenum'")
			->setValue("negotiationstatus", 'swap-pending')
			->setValue("negotiationrefid", $reservationswapid)
			->change();

			// =================================================================================
			// --- AKSI Kirim Notifikasi ke TARGET ---
			// =================================================================================
			$targetUsername= self::getUsernameFromInvReserve($target_invreservenum);
			$notificationData = [
				'username'    => $targetUsername, // Penerimanya adalah si target
				'message'     => "New negotiation request from {$loggedInUser} for item {$itemnum}.",
				'objectname'  => 'invreserve',
				'objectid'    => $targetReservation->getString("invreserveid"),
				'objectnum'   => $target_invreservenum,
				'processname' => 'NEGOTIATION_REQUESTED'
			];
			
			// Kirim notifikasi
			Super::sendNotification($notificationData);
		} catch (Exception $e) {
			// Tangani jika ada error dari database
			self::sendJsonResponse('error', 'Failed to save negotiation request. DB Error: ' . $e->getMessage()); // 500 Internal Server Error
		}
	
		// 5. Send Success JSON Response
		self::sendJsonResponse('success', 'Negotiation request has been sent successfully.'); // 201 Created
	}

} 

?>