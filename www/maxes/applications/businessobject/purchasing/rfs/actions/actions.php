<?php
require_once ("../../../eat.php");
require_once ("../../../purchasing/receiving/actions/CommonActions.php");

Class ActionSet {
	public static function isprocessed(){
		$set = new Set();
		$table = $_POST['table'];
		$rfsnum = $_POST['isprocessed'];
		$id = $_POST['id'];
		$tableid = $table."id";
		$rfsinsSet = $set->MboSetRemote($table)->setWhere("$tableid = '$id'")->MboRemote("isprocessed")->getMboSet();
		$rfsins = $rfsinsSet->getMbo();
		$isprocessed = $rfsins->getString("isprocessed");
		$ss= new stdClass;
		$ss->isprocessed = $isprocessed;
		Set::throws($ss);
	}
	public static function getPOEstimatedDateArrival(){
		$rfsnum = $_POST['getPOEstimatedDateArrival'];
		$ponum = $_POST["ponum"];
		
		$ss = new stdClass;
		$estdatearrival =  (new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->getMboValue("estdatearrival");
		
		
		$ss->estdatearrival = $estdatearrival;
		
		Set::throws($ss);
	}
	
	function escape_string($conn, $string) {
		return mysqli_real_escape_string($conn, $string);
	}

	public static function validate_returnqty() 
	{
		try {
			// 1. Ambil Parameter dengan Aman
			$ponum = Super::getParam('ponum');
			$polinenum = Super::getParam('polinenum');
			$itemnum = Super::getParam('itemnum');
			$returnQty = (float) Super::getParam('returnqty');

			// 2. Validasi Input Dasar
			if (empty($ponum) || empty($polinenum) || empty($itemnum)) {
				Set::throws(["msg" => "Error: Missing required parameters (ponum, polinenum, itemnum)."]);
				return;
			}

			if ($returnQty <= 0) {
				Set::throws(["msg" => "Error: Return quantity must be greater than zero."]);
				return;
			}

			// 3. Query untuk Menghitung Kuantitas Tersedia (Net Receipt)
			// Logika: Total RECEIPT - Total RETURN = Barang yang masih ada dari PO ini
			$sql = "
				SELECT
					COALESCE(SUM(CASE WHEN mt.transtype = 'RECEIPT' THEN mt.quantity ELSE 0 END), 0) -
					COALESCE(SUM(CASE WHEN mt.transtype = 'RETURN' THEN ABS(mt.quantity) ELSE 0 END), 0) AS qty_available
				FROM
					matrectrans mt
				WHERE
					mt.ponum = ?
					AND mt.polinenum = ?
					AND mt.itemnum = ?
			";

			$set = new Set();
			$result = $set::fetchAll($sql, [$ponum, $polinenum, $itemnum]);
			
			$qtyAvailable = 0;
			if (!empty($result)) {
				$qtyAvailable = (float)$result[0]['qty_available'];
			}

			// 4. Validasi Stok
			if ($returnQty > $qtyAvailable) {
				Set::throws(["msg" => "Error: Return quantity ($returnQty) exceeds the net received quantity from this PO ($qtyAvailable)."]);
				return;
			}

			// 5. Sukses
			Set::throws(["msg" => "ok"]);

		} catch (Exception $e) {
			// Tangani error database atau lainnya
			Set::throws(["msg" => "Error: " . $e->getMessage()]);
		}
	}


	public static function ValidateInspectedQty(){
		
		header('Content-Type: application/json');
		$conn = DB::getMXConnection();
		// Ambil data dari POST dan bersihkan
		$rfsnum = Super::getParam("inspchangeorderqty");
		$rfsinslinenum = Super::getParam("rfsinslinenum");
		$itemnum = Super::getParam("itemnum");
		$inspectqty = Super::getParam("inspectqty");
		$ponum = Super::getParam("ponum");
		$previous_inspectqty = Super::getParam("previous_inspectqty");
		// Validasi bahwa $inspectqty adalah angka
		if (!is_numeric($inspectqty)) {
			$response = array('status' => 'error', 'message' => 'Inspect quantity harus berupa angka.');
			echo json_encode($response);
			exit;
		}

		$msg = 'OK'; // Default
		$remarks = "OK"; // Default

		 $sql = "SELECT
                pl.ponum,
                pl.polinenum,
                pl.itemnum,
                pl.orderqty,
                COALESCE(SUM(mt.quantity), 0) AS total_received_qty,
                (SELECT COALESCE(SUM(ri.inspectqty), 0)
                 FROM rfsinsitem ri
                 LEFT JOIN rfs r ON ri.rfsnum = r.rfsnum
                 WHERE r.ponum = pl.ponum
                   AND ri.rfsinslinenum = pl.polinenum
                   AND ri.itemnum = pl.itemnum
                   AND ri.inspectiontype <> 'RETURN'
                ) AS total_inspected_qty
            FROM
                poline pl
            LEFT JOIN
                matrectrans mt ON pl.ponum = mt.ponum AND pl.polinenum = mt.polinenum AND pl.itemnum = mt.itemnum
            WHERE pl.ponum = '$ponum' and pl.polinenum='$rfsinslinenum'
            GROUP BY
                pl.ponum, pl.polinenum, pl.itemnum, pl.orderqty
            ORDER BY
                pl.ponum, pl.polinenum";

		$result = mysqli_query($conn, $sql);

		if ($result) {
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				$ponum = $row['ponum'];
				$polinenum = $row['polinenum'];
				$itemnum = $row['itemnum'];
				$orderqty = floatval($row['orderqty']);
				$total_received_qty = floatval($row['total_received_qty']);
				$total_inspected_qty = $row['total_inspected_qty'];
				$previous_total_inspected_qty = $total_inspected_qty-$previous_inspectqty;
				$remaining_qty = $orderqty - $total_received_qty;
				
				// Hitung total setelah penambahan inspectqty
				$new_total_inspected_qty = $inspectqty; 
				
				//$new_total_inspected_qty = $inspectqty; 
				if ($new_total_inspected_qty === null) {
					$msg = 'NOTYET_RECEIVED';
					$remarks = "Error: Item $itemnum on PO $ponum, line $polinenum not yet received.";
				} elseif ($new_total_inspected_qty > $remaining_qty) {
					$msg = 'OVER_ORDER';
					$remarks = "Error: Total inspect quantity for item $itemnum on PO $ponum, line $polinenum exceeds order quantity ($orderqty).";
				
				} else {
					$msg = 'VALID';
					$remarks = "OK";
				}
			} else {
				$msg = 'NOT_FOUND';
				$remarks = "PO $ponum on line $rfsinslinenum cannot be found.";
			}
		} else {
			$msg = 'ERROR';
			$remarks = "Error: " . mysqli_error($conn);
		}

		$response = array('msg' => $msg, 'remarks' => $remarks, 
		'sql' => $sql, 
		"orderqty" => $orderqty,
		"total_received_qty" => $total_received_qty,
		"new_total_inspected_qty" => $new_total_inspected_qty,
		"remaining_qty" => $remaining_qty);

		echo json_encode($response);
	}
	
	public static function ReturnTransaction($value){
		$rfsnum = Super::getParam("rfsnum");
		$retQty =  Super::getParam("quantity"); 
		$racknum = Super::getParam("racknum"); 
		$warehouse = Super::getParam("warehouse"); 
		$conditiontype = Super::getParam("conditiontype"); 
		$polinenum = Super::getParam("rfslinenum"); 
		
		$set = new Set();
		$transtableSet = $set->useTransactionManager()->MboSetRemote("matrectrans")->setWhere("rfsnum='$rfsnum' and polinenum='$polinenum' and transtype='RECEIPT'")->MboRemote("ponum")->MboRemote("polinenum")
		->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")->MboRemote("warehouse")->MboRemote("orgcode")
		->MboRemote("conditiontype")->MboRemote("racknum")->MboRemote("batchnum")->MboRemote("costdate")
		->MboRemote("sitecode")->getMboSet()->getMbo();
		
		$ponum = $transtableSet->getString("ponum");
		$polinenum = $transtableSet->getString("polinenum");
		$orgcode = $transtableSet->getString("orgcode");
		$sitecode = $transtableSet->getString("sitecode");
		$batchnum  = $transtableSet->getString("batchnum");
		$costdate  = $transtableSet->getString("costdate");
		$value["sourcetable"] = "matrectrans";
		$value["ponum"] = $ponum;
		$value["polinenum"] = $polinenum;
		$value["restqty"] = $value["quantity"];
		$value["actualdate"] = Xbo::setEnterDate();
		$value["remarks"] = "RFS Num: $rfsnum";
		$value["warehouse"] = $warehouse;
		$value["racknum"] = $racknum;
		$value["conditiontype"] = $conditiontype;
		$value["costdate"] = $costdate;
		$value["batchnum"] = $batchnum;
		
		CommonActions::ReturnTransaction($value);
		
	}
	public static function ReturnItems()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 1. Validasi Input
			$rfsnum = Super::getParam("rfsnum");
			$returnInspItems = Super::getParam("returninspitem");

			if (empty($rfsnum) || empty($returnInspItems) || !is_array($returnInspItems)) {
				throw new InvalidArgumentException("RFS Number and valid Return Items array are required.");
			}

			// 2. Ambil Data Umum (PONUM)
			// Gunakan useTransactionManager agar konsisten
			$ponum = (new Set())->useTransactionManager()
				->MboSetRemote("rfs")
				->where("rfsnum = ?", $rfsnum)
				->getMboValue("ponum");

			if (!$ponum) {
				throw new Exception("PONUM cannot be found for RFS: $rfsnum");
			}

			// 3. Persiapan Data Batch (Mengatasi N+1)
			// Kumpulkan semua itemnum dan polinenum untuk kueri massal
			$itemNums = array_unique(array_column($returnInspItems, 'itemnum'));
			$poLineNums = array_unique(array_column($returnInspItems, 'polinenum'));

			// Ambil data ITEM sekaligus
			$itemData = (new Set())->useTransactionManager()
				->MboSetRemote("item")
				->whereIn("itemnum", $itemNums)
				->getMboSet()
				->fetchDataAssoc('itemnum');

			// Ambil data POLINE sekaligus
			$poLineData = (new Set())->useTransactionManager()
				->MboSetRemote("poline")
				->where("ponum = ?", $ponum)
				->whereIn("polinenum", $poLineNums)
				->getMboSet()
				->fetchDataAssoc('polinenum');

			// 4. Proses Data di PHP (Looping Cepat)
			$rfsReturnData = [];
			$enterBy = Xbo::getLoginUser();
			$enterDate = date('Y-m-d H:i:s');
			
			// Dapatkan line number awal secara efisien
			// Kita bisa menggunakan getNextLineNumber yang sudah kita buat sebelumnya
			$startLineNum = (new Set())->useTransactionManager()
				->MboSetRemote("rfsreturn")
				->where("rfsnum = ?", $rfsnum)
				->getNextLineNumber("rfsreturnlinenum");

			$currentLineNum = $startLineNum;

			foreach ($returnInspItems as $item) {
				$itemnum = $item['itemnum'];
				$polinenum = $item['polinenum'];
				$returnQty = (float)$item['quantity'];

				// Validasi keberadaan data referensi
				if (!isset($itemData[$itemnum])) {
					throw new Exception("Item data not found for ITEMNUM: $itemnum");
				}
				if (!isset($poLineData[$polinenum])) {
					throw new Exception("POLINE data not found for PONUM: $ponum, POLINENUM: $polinenum");
				}

				$lineInfo = $poLineData[$polinenum];
				$itemInfo = $itemData[$itemnum];
				$unitCost = (float)$lineInfo['unitcost'];
				$lineCost = $unitCost * $returnQty;

				// Siapkan array untuk addStream
				$rfsReturnData[] = [
					"rfsnum" => $rfsnum,
					"rfsreturnlinenum" => $currentLineNum,
					"ponum" => $ponum,
					"polinenum" => $polinenum,
					"itemnum" => $itemnum,
					"description" => $lineInfo['description'],
					"conditiontype" => $item['conditiontype'],
					"unitcost" => $unitCost,
					"linecost" => $lineCost,
					"returnqty" => $returnQty,
					"conversionfactor" => 1,
					"returnunit" => $itemInfo['issueunit'], // issueunit dari tabel item
					"warehouse" => $item['warehouse'],
					"racknum" => $item['racknum'],
					// returndate & remarks null sesuai request
					"enterby" => $enterBy,
					"enterdate" => $enterDate,
					"returnby" => $enterBy,
					"gldebitacct" => $lineInfo['glcreditacct'], // DITUKAR (Debit = Credit PO)
					"glcreditacct" => $lineInfo['gldebitacct'], // DITUKAR (Credit = Debit PO)
					"isprocessed" => 'N',
					"changeby" => $enterBy,
					"changedate" => $enterDate
				];

				$currentLineNum += 10; // Increment manual di PHP
			}

			// 5. Bulk Insert dengan addStream
			if (!empty($rfsReturnData)) {
				(new Set())->useTransactionManager()
					->MboSetRemote("rfsreturn")
					->addStream($rfsReturnData);
			}

			$tm->commit();
			Set::throws(["msg" => "ok"]);

		} catch (Exception $e) {
			$tm->rollback();
			Set::throws(["msg" => "Error: " . $e->getMessage()]);
		}
	}

	/**
	 * Mengeluarkan item dari inventaris menggunakan metode FIFO, LIFO, atau EXPDATE.
	 * Fungsi ini berpartisipasi dalam transaksi aktif dan aman dari SQL Injection.
	 *
	 * @param string $itemnum Nomor item.
	 * @param string $warehouse Kode gudang.
	 * @param float $quantity Kuantitas yang akan dikeluarkan (harus positif).
	 * @param string $issuetype Jenis pengeluaran ('FIFO', 'LIFO', 'EXPDATE').
	 * @return array Array informasi item dari batch PERTAMA yang digunakan.
	 * @throws Exception Jika stok tidak cukup atau terjadi kesalahan database.
	 */
	public static function issueItemByFIFOLIFO($itemnum, $warehouse, $quantity, $issuetype) 
	{
		
		// Validasi Kuantitas
		if ($quantity <= 0) {
			throw new Exception("Error: Quantity to issue must be positive.");
		}

		// Tentukan Urutan (ORDER BY) Berdasarkan $issuetype
		switch (strtoupper($issuetype)) {
			case 'FIFO':
				$orderby = "costdate ASC";
				break;
			case 'LIFO':
				$orderby = "costdate DESC";
				break;
			case 'EXPDATE':
				$orderby = "expdate ASC, costdate ASC";
				break;
			default:
				throw new Exception("Error: Invalid issuetype: " . $issuetype);
		}

		// Kita TIDAK memulai transaksi baru. Kita gunakan transaksi yang sudah ada.

		try {
			// 1. Cek Total Stok (Optional tapi bagus untuk fail-fast)
			// Kita gunakan COALESCE agar aman jika null
			$totalQtySet = (new Set())->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				->where("itemnum = ? AND warehouse = ?", $itemnum, $warehouse)
				->MboRemote("COALESCE(SUM(quantity), 0) as total_quantity")
				->getMboSet();
				
			$totalQtyRow = $totalQtySet->fetch();
			$totalQuantity = (float)($totalQtyRow['total_quantity'] ?? 0);
			echo "$quantity...............  $totalQuantity";
			exit;
			if ($quantity > $totalQuantity) {
				// Jangan rollback di sini, biarkan naik ke atas
				throw new Exception("Error: Not enough quantity available to issue $quantity of item $itemnum from warehouse $warehouse. Only $totalQuantity available.");
			}

			// 2. Ambil Daftar Batch Stok yang Tersedia
			$stockBatchesSet = (new Set())->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				->where("itemnum = ? AND warehouse = ? AND quantity > 0", $itemnum, $warehouse)
				->order($orderby) // Urutkan sesuai FIFO/LIFO
				->MboRemote("invfifolifocostid, quantity, batchnum, costdate, expdate, ponum, polinenum, rfsnum")
				->getMboSet();

			$qtyToIssue = $quantity;
			$issuedData = null; // Data dari batch pertama yang diambil

			// 3. Loop dan Kurangi Stok (Algoritma Greedy)
			while ($qtyToIssue > 0 && ($batch = $stockBatchesSet->fetch())) {
				
				$batchId = $batch['invfifolifocostid'];
				$qtyOnHand = (float)$batch['quantity'];

				// Simpan data dari batch PERTAMA saja (sesuai spesifikasi fungsi)
				if ($issuedData === null) {
					$issuedData = [
						'batchnum' => $batch['batchnum'],
						'costdate' => $batch['costdate'],
						'expdate' => $batch['expdate'],
						'ponum' => $batch['ponum'],
						'polinenum' => $batch['polinenum'],
						'rfsnum' => $batch['rfsnum']
					];
				}

				if ($qtyOnHand <= $qtyToIssue) {
					// Skenario A: Batch ini habis, tapi mungkin masih kurang
					$qtyToIssue -= $qtyOnHand;

					// Hapus batch ini karena stoknya jadi 0
					(new Set())->useTransactionManager()
						->MboSetRemote("invfifolifocost")
						->where("invfifolifocostid = ?", $batchId)
						->delete();

				} else {
					// Skenario B: Batch ini cukup, sisakan stoknya
					$newQty = $qtyOnHand - $qtyToIssue;
					
					(new Set())->useTransactionManager()
						->MboSetRemote("invfifolifocost")
						->where("invfifolifocostid = ?", $batchId)
						->setValue("quantity", $newQty)
						->change();

					$qtyToIssue = 0; // Selesai
				}
			}

			// Jaring pengaman (seharusnya tidak tercapai karena cek total di awal)
			if ($qtyToIssue > 0) {
				throw new Exception("Unexpected error: Stock ran out during issue process for item $itemnum.");
			}

			return $issuedData;

		} catch (Exception $e) {
			// Biarkan exception naik agar ditangkap oleh CompleteReturn
			throw $e;
		}
	}
		/**
	 * Fungsi untuk mengeluarkan item dari inventaris berdasarkan PONUM dan POLINENUM.
	 *
	 * @param string $itemnum Nomor item.
	 * @param string $warehouse Kode gudang.
	 * @param float $quantity Kuantitas yang akan dikeluarkan (harus positif).
	 * @param string $ponum Nomor PO.
	 * @param string $polinenum Nomor Baris PO.
	 * @return array|null Array informasi item yang dikeluarkan, atau null jika tidak ada data/stok cukup.
	 * @throws Exception Jika terjadi kesalahan database.
	 */
	public static function issueItemByPONUM($itemnum, $warehouse, $quantity, $ponum, $polinenum) 
	{

		try {
			// 1. Ambil Data Dari Tabel invfifolifocost
			// Gunakan useTransactionManager agar menggunakan koneksi yang sama
			$invSet = (new Set())->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				->where("itemnum = ? AND warehouse = ? AND ponum = ? AND polinenum = ?", 
						$itemnum, $warehouse, $ponum, $polinenum)
				->MboRemote("invfifolifocostid, quantity, batchnum, costdate, expdate, ponum, polinenum, rfsnum")
				->getMboSet();

			$row = $invSet->fetch();

			if (!$row) {
				return null; 
			}

			$invfifolifocostid = $row['invfifolifocostid'];
			$qty_on_hand = (float)$row['quantity'];

			// 2. Validasi Kuantitas Cukup
			// Kita bandingkan float dengan float, jauh lebih aman daripada string
			if ($quantity > $qty_on_hand) {
				// Opsi A: Lempar Exception (Strict)
				// throw new Exception("Error: Not enough quantity on hand for item $itemnum with PONUM $ponum.");
				
				// Opsi B: Kembalikan null (Sesuai dengan komentar Anda di kode lama)
				// Biarkan pemanggil yang memutuskan apakah ini error fatal atau tidak.
				return null; 
			}

			// 3. Kurangi Kuantitas
			$new_qty = $qty_on_hand - $quantity;

			// Lakukan UPDATE yang aman
			(new Set())->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				->where("invfifolifocostid = ?", $invfifolifocostid)
				->setValue("quantity", $new_qty)
				->change();

			// 4. Return Data
			return [
				'batchnum' => $row['batchnum'],
				'costdate' => $row['costdate'],
				'expdate' => $row['expdate'],
				'ponum' => $row['ponum'],
				'polinenum' => $row['polinenum'],
				'rfsnum' => $row['rfsnum']
			];

		} catch (Exception $e) {
			throw $e;
		}
	}

	public static function CompleteReturn()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$rfsnum = Super::getParam("CompleteReturn");
			if (empty($rfsnum)) {
				throw new InvalidArgumentException("RFS Number is required.");
			}

			// 1. Ambil Data RFS Return yang belum diproses dalam SATU kueri
			$rfsReturnData = (new Set())->useTransactionManager()
				->MboSetRemote("rfsreturn rr")
				->setRelationship("rfs r", "rr.rfsnum = r.rfsnum")
				->where("rr.rfsnum = ? AND rr.isprocessed = 'N'", $rfsnum)
				->MboRemote("rr.*, r.orgcode, r.sitecode") 
				->getMboSet()
				->fetchData();
			// 2. Ambil data Exchange Rate

			$exchangerate = (new Set())->useTransactionManager()->MboSetRemote("rfs")
			->setRelationship("po", "rfs.ponum = po.ponum")
			->where("rfs.rfsnum=?", $rfsnum)->getMboValue("exchangerate");


			if (empty($rfsReturnData)) {
				$tm->commit(); 
				Set::throws(["msg" => "Error: No unprocessed data found for Return."]);
				return;
			}

			$ponum = $rfsReturnData[0]['ponum']; 
			$enterDate = date('Y-m-d H:i:s');
			$enterBy = Xbo::getLoginUser();

			// 2. Loop melalui setiap item yang akan dikembalikan
			foreach ($rfsReturnData as $row) {
				$itemnum = $row['itemnum'];
				$warehouse = $row['warehouse'];
				$returnQty = (float)$row['returnqty'];
				$userBatchNum = $row['batchnum']; // Batch yang diinput user

				$issueData = [];
				$issuetype = 'FIFO'; // Default

				// LOGIKA BARU: JIKA BATCHNUM DIISI OLEH USER

				if (!empty($userBatchNum)) {
					
					// Cari batch spesifik
					$batchStockSet = (new Set())->useTransactionManager()
						->MboSetRemote("invfifolifocost")
						->where("itemnum = ? AND warehouse = ? AND batchnum = ?", $itemnum, $warehouse, $userBatchNum)
						->getMboSet();
					
					$batchStock = $batchStockSet->fetch();

					if (!$batchStock) {
						throw new Exception("Error: Batch number '$userBatchNum' not found for item $itemnum in warehouse $warehouse.");
					}

					$qtyOnHand = (float)$batchStock['quantity'];
					
					// Validasi stok di batch ini
					if ($qtyOnHand < $returnQty) {
						throw new Exception("Error: Not enough quantity in batch '$userBatchNum'. Available: $qtyOnHand, Required: $returnQty.");
					}

					// Kurangi stok dari batch ini
					$newQty = $qtyOnHand - $returnQty;
					(new Set())->useTransactionManager()
						->MboSetRemote("invfifolifocost")
						->where("invfifolifocostid = ?", $batchStock['invfifolifocostid'])
						->setValue("quantity", $newQty)
						->change();

					// Siapkan data issue
					$issueData = [
						'batchnum' => $userBatchNum,
						'costdate' => $batchStock['costdate'],
						'expdate' => $batchStock['expdate'],
						// Kita pakai data PO dari input return, atau dari batch jika null
						'ponum' => $row['ponum'] ?: $batchStock['ponum'],
						'polinenum' => $row['polinenum'] ?: $batchStock['polinenum']
					];

				} 
				// LOGIKA LAMA: JIKA BATCHNUM KOSONG (RETURN OTOMATIS)
				else {
					// Ambil Issue Type dari Inventory
					$inventoryData = (new Set())->useTransactionManager()
						->MboSetRemote("inventory")
						->where("itemnum = ? AND warehouse = ?", $itemnum, $warehouse)
						->MboRemote("issuetype")
						->getMboSet()
						->fetch();

					if (!$inventoryData) {
						throw new Exception("Inventory data not found for item $itemnum in warehouse $warehouse");
					}
					$issuetype = $inventoryData['issuetype'];

					// Cek stok FIFO/LIFO yang spesifik untuk PO/Line ini
					$poSpecificStock = (new Set())->useTransactionManager()
						->MboSetRemote("invfifolifocost")
						->where("itemnum = ? AND warehouse = ? AND ponum = ? AND polinenum = ?", 
								$itemnum, $warehouse, $row['ponum'], $row['polinenum'])
						->MboRemote("SUM(quantity) AS total_quantity")
						->getMboSet()
						->fetch();
					
					$totalPoQty = (float)($poSpecificStock['total_quantity'] ?? 0);

					// Logika Pengeluaran Otomatis (Sama seperti sebelumnya)
					$qtyToIssue = $returnQty;

					if ($totalPoQty > 0) {
						if ($totalPoQty >= $qtyToIssue) {
							$issueData = ActionSet::issueItemByPONUM($itemnum, $warehouse, $qtyToIssue, $row['ponum'], $row['polinenum']);
						} else {
							$result1 = ActionSet::issueItemByPONUM($itemnum, $warehouse, $totalPoQty, $row['ponum'], $row['polinenum']);
							$qtyToIssue -= $totalPoQty;
							$result2 = ActionSet::issueItemByFIFOLIFO($itemnum, $warehouse, $qtyToIssue, $issuetype);
							
							// Perhatian: Menggabungkan data dari 2 issue bisa rumit. 
							// Untuk penyederhanaan matrectrans, kita ambil data utama dari issue pertama (PO).
							$issueData = $result1; 
							// Catatan: Idealnya matrectrans harus di-split jika sumbernya beda, 
							// tapi untuk return PO, data PO lebih prioritas.
						}
					} else {
						$issueData = ActionSet::issueItemByFIFOLIFO($itemnum, $warehouse, $qtyToIssue, $issuetype);
					}

					if (!$issueData) {
						throw new Exception("Failed to automatically issue item $itemnum. Check stock.");
					}
				}

				// 6. Posting ke MATRECTRANS
				$unitCost = $row['unitcost']*$exchangerate;
				$lineCost = -($returnQty * $unitCost);
				$financialPeriod = date('Ym', strtotime($row['returndate']));

				// Pastikan issueData tidak kosong dan memiliki key yang dibutuhkan
				$finalBatchNum = $issueData['batchnum'] ?? null;
				$finalCostDate = $issueData['costdate'] ?? null;
				$finalExpDate = $issueData['expdate'] ?? null;

				(new Set())->useTransactionManager()->MboSetRemote("matrectrans")
					->setValue("ponum", $row['ponum'])
					->setValue("rfsnum", $row['rfsnum'])
					->setValue("batchnum", $finalBatchNum) // Batchnum dari hasil issue
					->setValue("polinenum", $row['polinenum'])
					->setValue("linetype", "item")
					->setValue("itemnum", $itemnum)
					->setValue("description", $row['description'])
					->setValue("conditiontype", $row['conditiontype'])
					->setValue("quantity", -$returnQty)
					->setValue("discountpercentage", 0)
					->setValue("discountprice", 0)
					->setValue("unitcost", $unitCost)
					->setValue("linecost", $lineCost)
					->setValue("warehouse", $warehouse)
					->setValue("racknum", $row['racknum'])
					->setValue("gldebitacct", $row['gldebitacct'])
					->setValue("glcreditacct", $row['glcreditacct'])
					->setValue("actualdate", $row['returndate'])
					->setValue("enterdate", $enterDate)
					->setValue("financialperiod", $financialPeriod)
					->setValue("issuetype", $issuetype)
					->setValue("costdate", $finalCostDate) // Costdate dari hasil issue
					->setValue("expdate", $finalExpDate)   // Expdate dari hasil issue
					->setValue("enterby", $enterBy)
					->setValue("transtype", "RETURN")
					->setValue("remarks", $row['remarks'])
					->setValue("orgcode", $row['orgcode'])
					->setValue("sitecode", $row['sitecode'])
					->add();

				// 7. Tandai baris ini sudah diproses
				(new Set())->useTransactionManager()->MboSetRemote("rfsreturn")
					->where("rfsreturnid = ?", $row['rfsreturnid'])
					->setValue("isprocessed", 'Y')
					->change();
			}

			// 8. Update Status PO
			self::setPOLineReceiptStatus($ponum); 
			self::setPOReceiptStatus($ponum);

			$tm->commit();
			Set::throws(["msg" => "success"]);

		} catch (Exception $e) {
			$tm->rollback();
			error_log("CompleteReturn Error: " . $e->getMessage());
			Set::throws(["msg" => "Error: Failed to process return. " . $e->getMessage()]);
		}
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
			return null;
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
								Set::throws(["msg"=>'The issue type for item $itemnum is "Expired Date", but no date has been entered. Please input the appropriate expiration date.']);
								exit;
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
						if($recipient_username != null){
							$notificationData = [
								'username'    => $recipient_username,
								'message'     => "GOOD NEWS! {$quantity_needed} pc(s) of item {$itemnum} you were waiting for has been allocated.",
								'objectname'  => 'invreserve',
								'objectid'    => $invreserveid, // ID dari record invreserve yang baru di-update
								'objectnum'   => $invreservenum, // Nomor reservasi yang sekarang sudah RESERVED
								'processname' => 'RESERVATION_ALLOCATED' // Nama proses yang jelas
							];
							
							Super::sendNotification($notificationData);
						}
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
		//Super::sendJsonResponse("success", "Item(s) has been received and waiting-list users has been notified.");
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
		
		if(!empty($personid)){
			$set = new Set();
			$userSet = $set->MboSetRemote("mxsusers")->setWhere("personid='$personid'")->getMboSet();
			
			if($userSet->isEmpty()){
				return null;
			}
			$user = $userSet->getMbo();
			$username = $user->getString("username");
			return $username;
		}

		return null;
		
	}
	public static function CompleteReceipt()
	{
		$linetype = "item";
		$set = new Set();
		$rfsnum = Super::getParam("CompleteReceipt");
		
		// 1. Validasi dan Sanitasi
		$rfsnum = trim($rfsnum);
		if (empty($rfsnum)) {
			Set::throws(["msg" => "Error: GRI cannot be null"]);
			return;
		}
		$conn = DB::getMXConnection();
		//costdate
		
		$set = new Set();
		$rfsSet = $set->useTransactionManager()->MboSetRemote("rfsinsitem r")
		->setRelationship("rfs", "rfs.rfsnum = r.rfsnum")
		->setRelationship("po", "rfs.ponum = po.ponum") 
		->setRelationship("poline", "po.ponum = rfs.ponum AND poline.polinenum = r.rfsinslinenum AND poline.itemnum = r.itemnum")
		->setRelationship("itemcondition ic", "r.itemnum = ic.itemnum AND r.conditiontype = ic.conditiontype ")
		->where("r.rfsnum = ? AND r.inspectiontype = 'accepted' AND (r.isprocessed = 'N' or r.isprocessed is null)", $rfsnum)
		->MboRemote("r.rfsnum")->MboRemote("r.rfsinslinenum")->MboRemote("r.itemnum")->MboRemote("r.conditiontype")
		->MboRemote("r.description AS itemdesc")->MboRemote("r.inspectqty")->MboRemote("r.orderunit")->MboRemote("r.conversionfactor")
		->MboRemote("r.unitcost")->MboRemote("r.linecost")->MboRemote("r.taxcost")->MboRemote("r.gldebitacct")
		->MboRemote("r.glcreditacct")->MboRemote("po.estdatearrival")->MboRemote("po.orderdate")->MboRemote("r.inspcompdate")->MboRemote("r.inspectcomment")
		->MboRemote("r.warehouse")->MboRemote("r.racknum")->MboRemote("po.orgcode")->MboRemote("po.sitecode")
		->MboRemote("r.isprocessed")->MboRemote("ic.conditionrate")->MboRemote("r.inspectiontype")
		->MboRemote("r.rfsinsitemid")->MboRemote("po.vendor")->MboRemote("po.ponum")->MboRemote("poline.polinenum")
		->MboRemote("po.exchangerate") 
		->getMboSet("distinct");
		
		
		$counts = $rfsSet->counts();
		
		$ponum = null;
		// Mulai Transaksi Database
		mysqli_begin_transaction($conn);
		if($counts == 0){
			Super::sendJsonResponse("success", "No Data Found to receive.");
			return null;
		}

		$items_to_revalidate = []; // Array untuk menampung item yang stoknya bertambah

		try {
			for($i=0; $i<$counts; $i++){
				$row = $rfsSet->getMbo();
				//requested
				$rfsinsitemid = $row->getString('rfsinsitemid');
				$itemnum = $row->getString('itemnum');
				$conversionfactor = $row->getString('conversionfactor');
				$warehouse = $row->getString('warehouse');
				$racknum = $row->getString('racknum');
				$orgcode = $row->getString('orgcode');
				$sitecode = $row->getString('sitecode');
				$inspectqty = $row->getString('inspectqty');
				$ponum = $row->getString('ponum');
				$polinenum = $row->getString('polinenum');
				$unitcost = $row->getString('unitcost')/$conversionfactor;
				$exchangerate = $row->getString('exchangerate'); 
				$vendor = $row->getString('vendor');
				$conditiontype = $row->getString('conditiontype');
				$conditionrate = $row->getString('conditionrate');
				$inspcompdate = $row->getString('inspcompdate');

				//if inspcompdate empty
				if(empty($$inspcompdate)) $inspcompdate = Xbo::setEnterDate();

				$rfsnum = $row->getString('rfsnum');
				$rfsinslinenum = $row->getString('rfsinslinenum');
				$batchnum = Xbo::GetBatchNum();	 
				$enterby = Xbo::getLoginUser();
				$enterdate = date('Y-m-d H:i:s');
				$safetystock = 1;
				$inspectcomment = $row->getString('inspectcomment');
				// **Validasi conversionfactor**
				if (empty($conversionfactor) || !is_numeric($conversionfactor) || $conversionfactor == 0) {
					$conversionfactor = 1; // Set default ke 1 jika kosong, 0, atau bukan angka
				}
				
				// **Ambil Condition Type dan Rate**
				$set_ic = new Set();
				$sql_itemconditionSet = $set_ic->useTransactionManager()->MboSetRemote("itemcondition")
					->setWhere("itemnum = '$itemnum' AND conditionrate = '100'") // Filter Condition Rate 100
					->MboRemote("itemnum")
					->MboRemote("conditiontype")
					->MboRemote("conditionrate")
					->getMboSet();

				$itemconditionCounts = $sql_itemconditionSet->counts();

				if ($itemconditionCounts > 0) {
					$itemcondition = $sql_itemconditionSet->getMbo();
					$conditiontype = $itemcondition->getString('conditiontype');
					$conditionrate = $itemcondition->getString('conditionrate');
				} else {
					$conditiontype = NULL;
					$conditionrate = 100;
				}
				// **AKHIR  **
				
				// 3b. Insert ke invfifolifocost
				$quantity = $inspectqty * $conversionfactor; 
				$unitcost_base = $unitcost * $exchangerate; 
				$financialperiod = date('Ym', strtotime($inspcompdate));
				
				
				// Tambahkan offset 10 detik
				$inspcompdate_timestamp = strtotime($inspcompdate);
				$inspcompdate_timestamp += ($i * 10); 
				$inspcompdate_offset = date('Y-m-d H:i:s', $inspcompdate_timestamp);
				
				if(empty($inspcompdate_offset)) $inspcompdate_offset = $enterdate;
				
				$set3b = new Set();
				$set3b->useTransactionManager()->MboSetRemote("invfifolifocost")
				->setValue("itemnum", $itemnum)
				->setValue("conditiontype", $conditiontype)
				->setValue("costdate", $inspcompdate_offset)
				->setValue("batchnum", $batchnum)
				->setValue("ponum", $ponum)
				->setValue("polinenum", $polinenum)
				->setValue("vendorcode", $vendor)
				->setValue("financialperiod", $financialperiod)
				->setValue("unitcost", $unitcost_base)
				->setValue("quantity", $quantity)
				->setValue("physqty", $quantity)
				->setValue("conditionrate", $conditionrate)
				->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)
				->setValue("warehouse", $warehouse)
				->setValue("racknum", $racknum)
				->setValue("rfsnum", $rfsnum)
				->setValue("rfslinenum", $rfsinslinenum)
				->setValue("transtype", 'RECEIPT') 
				->add();
					
				// 3c. Insert/Update invvendor
				//$pr_requesteddate = $row->getString('pr_requesteddate');
				$prnum = (new Set())->useTransactionManager()->MboSetRemote("poline")->where("ponum=? AND polinenum=?", $ponum, $polinenum)->getMboValue("prnum");
				if(!empty($prnum)){
					$pr_requesteddate = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)->getMboValue("requesteddate");
				}else{
					$pr_requesteddate = (new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->getMboValue("orderdate");
				}

				$intleadtime = ($pr_requesteddate) ? (int)ActionSet::dateDiffInDays($pr_requesteddate, $inspcompdate) : 0;
				$extleadtime = ActionSet::dateDiffInDays($row->getString('orderdate'), $inspcompdate);
				$leadtime = $intleadtime + $extleadtime;
				
				$set3c = new Set();
				$sql_check_invvendorSet = $set3c->useTransactionManager()->MboSetRemote("invvendor")
				->where("itemnum = ? AND warehouse = ? AND orgcode = ? AND sitecode = ? AND vendorcode = ?",  $itemnum, $warehouse, $orgcode, $sitecode, $vendor)
				->getMboSet();

				$vendorCounts = $sql_check_invvendorSet->counts();
				
				if($vendorCounts>0){
					
					$set3c1 = new Set();
					$set3c1->useTransactionManager()->MboSetRemote("invvendor")
					->where("itemnum = ? AND warehouse = ? AND orgcode = ? AND sitecode = ? AND vendorcode = ?",  $itemnum, $warehouse, $orgcode, $sitecode, $vendor)
					->setValue("intleadtime", $intleadtime)
					->setValue("extleadtime", $extleadtime)
					->setValue("leadtime", $leadtime)
					->setValue("lastponum", $ponum)
					->setValue("lastorderdate", $row->getString('orderdate'))
					->setValue("lastprice", $unitcost) // Harga Vendor
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->change();
					
				}else{
					$set3c1 = new Set();
					$set3c1->useTransactionManager()->MboSetRemote("invvendor")
					->setValue("intleadtime", $intleadtime)
					->setValue("extleadtime", $extleadtime)
					->setValue("leadtime", $leadtime)
					->setValue("lastponum", $ponum)
					->setValue("lastorderdate", $row->getString('orderdate'))
					->setValue("lastprice", $unitcost) // Harga Vendor
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->setValue("itemnum", $itemnum)
					->setValue("warehouse", $warehouse)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("vendorcode", $vendor)
					->add();
				}

				//3d. Insert/Update inventory
				
				$set3d = new Set();
				$sql_check_inventorySet = $set3d->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum = '$itemnum' AND warehouse = '$warehouse' AND orgcode = '$orgcode' AND sitecode = '$sitecode'")->getMboSet();
				$inventoryCounts = $sql_check_inventorySet->counts();
				
				// Ambil Rata-Rata Leadtime (SUBQUERY!)
				$set_avg = new Set();
				$sql_avg_leadtimeSet = $set_avg->useTransactionManager()->MboSetRemote("invvendor")
					->setWhere("itemnum = '$itemnum' AND warehouse = '$warehouse' AND orgcode = '$orgcode' AND sitecode = '$sitecode'")
					->MboRemote("AVG(leadtime) AS avg_leadtime")
					->getMboSet();

				$avg_leadtimeMbo = $sql_avg_leadtimeSet->getMbo();

				$avg_leadtime = $avg_leadtimeMbo ? $avg_leadtimeMbo->getString('avg_leadtime') : 0;
				
				if ($inventoryCounts > 0) {
					
					$set3d1 = new Set();
					$set3d1->useTransactionManager()->MboSetRemote("inventory")
					->setWhere("itemnum = '$itemnum' AND warehouse = '$warehouse' AND orgcode = '$orgcode' AND sitecode = '$sitecode'")
					->setValue("leadtime", $avg_leadtime)
					->setValue("reorder", 'Y')
					->setValue("integration", 'NONE')
					->change();
					
				}else{
					
					$sinv = new Set();
					$sinv->useTransactionManager()->MboSetRemote("inventory")
					->setValue("leadtime", $avg_leadtime)
					->setValue("reorder", 'Y')
					->setValue("integration", 'NONE')
					->setValue("itemnum", $itemnum)
					->setValue("warehouse", $warehouse)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("reorderqty", 0)  
					->setValue("eoqqty", 0)	  
					->setValue("issuetype", 'FIFO')  
					->setValue("safetystock", $safetystock)				
					->add();
				}


				// matrectrans
				$linecost = $quantity * $unitcost_base;
				$actualdate = $inspcompdate; // Atau date('Y-m-d') jika menggunakan tanggal hari ini
				$enterdate = date('Y-m-d H:i:s');
				$financialperiod = date('Ym', strtotime($inspcompdate_offset));

				$set3m = new Set();
				$set3m->useTransactionManager()->MboSetRemote("matrectrans")
					->setValue("ponum", $ponum)
					->setValue("rfsnum", $rfsnum)
					->setValue("batchnum", $batchnum)
					->setValue("polinenum", $polinenum)
					->setValue("linetype", $linetype) 
					->setValue("itemnum", $itemnum)
					->setValue("description", $row->getString('itemdesc'))
					->setValue("conditiontype", $conditiontype)
					->setValue("quantity", $quantity)
					->setValue("unitcost", $unitcost_base)
					->setValue("linecost", $linecost)
					->setValue("warehouse", $warehouse)
					->setValue("racknum", $racknum)
					->setValue("gldebitacct", $row->getString('gldebitacct'))
					->setValue("glcreditacct", $row->getString('glcreditacct'))
					->setValue("actualdate", $actualdate)
					->setValue("enterdate", $enterdate)
					->setValue("financialperiod", $financialperiod)
					->setValue("issuetype", 'FIFO')
					->setValue("costdate", $inspcompdate_offset)
					->setValue("enterby", $enterby)
					->setValue("transtype", 'RECEIPT')
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("remarks", $inspectcomment)
					->add();
	
				// 3e. Update rfsinsitem.isprocessed = 'Y'
				$set3e = new Set();
				$sql_update_rfsinsitemSet = $set3e->useTransactionManager()->MboSetRemote("rfsinsitem")
				->setWhere("rfsinsitemid = '$rfsinsitemid'")
				->setValue("isprocessed", 'Y')	
				->change();		

				//tambahkan item ke "Daftar Social Reservation yang sudah di replenish"
				if (!in_array($itemnum, $items_to_revalidate)) {
                $items_to_revalidate[] = $itemnum;
            }

			}
			
			// Setelah semua pemrosesan BERHASIL, update status PO Line
            ActionSet::setPOLineReceiptStatus($ponum); 
			ActionSet::setPOReceiptStatus($ponum);
			// Commit Transaksi
			mysqli_commit($conn);

			if (!empty($items_to_revalidate)) {
				foreach ($items_to_revalidate as $item_to_process) {
					 ActionSet::revalidateall($item_to_process);
				}
			}
			
			Super::sendJsonResponse("success", "Item(s) has been received and waiting-list users has been notified.");

		} catch (Exception $e) {
			// Rollback Transaksi Jika Terjadi Kesalahan
			mysqli_rollback($conn);
			Super::sendJsonResponse("error", "Error: " . $e->getMessage());
		}
	}
	

	/**
	 * Menghitung dan memperbarui status penerimaan untuk setiap line item pada sebuah PO.
	 * Fungsi ini aman, cross-platform, dan berpartisipasi dalam transaksi aktif.
	 *
	 * @param string $ponum Nomor Purchase Order.
	 * @throws Exception Jika terjadi kesalahan database.
	 */
	public static function setPOLineReceiptStatus(string $ponum)
	{
		// Langkah 1: Jalankan kueri SQL cerdas untuk mendapatkan status per line.
		// Kita gunakan COALESCE agar kompatibel dengan Oracle/SQL Server.
		
		$set = new Set();
		$sql = "
			SELECT
				pl.ponum,
				pl.polinenum,
				CASE
					WHEN COALESCE(SUM(mt.quantity), 0) = 0 THEN 'NONE'
					WHEN COALESCE(SUM(mt.quantity), 0) < pl.orderqty THEN 'PARTIAL'
					WHEN COALESCE(SUM(mt.quantity), 0) >= pl.orderqty THEN 'COMPLETE'
					ELSE 'UNKNOWN'
				END AS receipt_status
			FROM
				poline pl
			LEFT JOIN
				matrectrans mt ON pl.ponum = mt.ponum AND pl.polinenum = mt.polinenum AND pl.itemnum = mt.itemnum AND mt.transtype = 'RECEIPT'
			WHERE pl.ponum = ?
			GROUP BY
				pl.ponum, pl.polinenum, pl.orderqty
		";

		// Eksekusi kueri dengan parameter binding yang aman
		$lineStatuses = $set::fetchAll($sql, [$ponum]);

		// Langkah 2: Loop hasil dan update status di tabel poline
		if (!empty($lineStatuses)) {
			foreach ($lineStatuses as $line) {
				// Lakukan UPDATE yang aman di dalam transaksi yang sedang berjalan
				(new Set())->useTransactionManager()
					->MboSetRemote("poline")
					->where("ponum = ? AND polinenum = ?", $line['ponum'], $line['polinenum'])
					->setValue("receiptstatus", $line['receipt_status'])
					->change();
			}
		}
	}

	public static function setPOReceiptStatus(string $ponum)
	{
	
		$sql = "
			SELECT
				CASE
					-- Jika jumlah baris dengan status 'NONE' sama dengan total baris, maka status header adalah 'NONE'.
					WHEN SUM(CASE WHEN pl.receiptstatus = 'NONE' THEN 1 ELSE 0 END) = COUNT(pl.polineid) THEN 'NONE'
					-- Jika jumlah baris dengan status 'COMPLETE' sama dengan total baris, maka status header adalah 'COMPLETE'.
					WHEN SUM(CASE WHEN pl.receiptstatus = 'COMPLETE' THEN 1 ELSE 0 END) = COUNT(pl.polineid) THEN 'COMPLETE'
					-- Jika tidak keduanya (berarti ada campuran status), maka statusnya adalah 'PARTIAL'.
					ELSE 'PARTIAL'
				END AS receipt_status
			FROM
				po
			INNER JOIN
				poline AS pl ON po.ponum = pl.ponum
			WHERE po.ponum = ?
			GROUP BY
				po.ponum
		";

		$set = new Set();
		$statusResult = $set::fetchAll($sql, [$ponum]);

		if (!empty($statusResult) && isset($statusResult[0]['receipt_status'])) {
			
			$receipt_status = $statusResult[0]['receipt_status'];

			(new Set())->useTransactionManager()
				->MboSetRemote("po")
				->where("ponum = ?", $ponum)
				->setValue("receipt", $receipt_status) // Asumsi nama kolom di tabel 'po' adalah 'receipt'.
				->change();
				
		} else {
			// Ini adalah kasus yang jarang terjadi, tapi penting untuk ditangani.
			// Bisa berarti PO tidak ada, atau PO ada tapi tidak punya line sama sekali.
			error_log("Could not determine aggregate receipt status for PO '$ponum'. It may not exist or have no lines.");
			
		}
	}

	public static function dateDiffInDays($date1, $date2) {

		if (empty($date1) || empty($date2)) {
			return 0;
		}
			
		$diff = strtotime($date2) - strtotime($date1);
		$value = abs(round($diff / 86400));
			
		return $value;
	}
	
	public static function CopyEmployeesToInspection(){
		$empids = Super::getParam("copyemptorfsins");
		$rfsnum = Super::getParam("rfsnum");
		$countrfsline = count($empids);
		$linenum = 0;
		$msg = null;
		for($i=0; $i<$countrfsline; $i++){
			$set = new Set();
			$empid = $empids[$i]["empid"];
			
			$empSet = $set->useTransactionManager()->MboSetRemote("employees")->setWhere("employeesid='$empid'")->MboRemote("personid")->getMboSet();
			$emp = $empSet->getMbo();
			$personid = $emp->getString("personid");
			$linenum = $linenum+10;
			
			$chk = new Set();
			$isExist = $chk->useTransactionManager()->MboSetRemote("rfsinsteam")->setWhere("rfsnum='$rfsnum' and personid='$personid'")->MboRemote("personid")->getMboSet();
			$counts = $isExist->counts();
			
			if($counts == 0){
				$set2 = new Set();
				$set2->useTransactionManager()->MboSetRemote("rfsinsteam")->setValue("rfsnum", $rfsnum)->setValue("rfsinslinenum", $linenum)->setValue("personid", $personid)->add();
				$msg = "ok";
			}else{
				continue;
			}
		}
		Set::throws(["msg"=>$msg]);
	}
	public static function CopyRFSLineToInspection(){
		$rfslineids = Super::getParam("copyrfslinetoinspection");
		$rfsnum = $_POST["rfsnum"];
		//inspectqty
		if(!isset($_POST["rfscourier"])){
			Set::throws(["msg"=>"notsetcourier"]);
		}else{
			$rfscourierid = $_POST["rfscourier"]["xxtableidvalue"];
			$set = new Set();
			$rfscourierlinenum = $set->useTransactionManager()->MboSetRemote("rfscourier")->setWhere("rfscourierid='$rfscourierid'")->MboRemote("rfscourierlinenum")->getMboSet()->getMbo()->getString("rfscourierlinenum");
			
			if(empty($rfscourierlinenum)){
				Set::throws(["msg"=>"notsetcourier"]);
			}else{
				$enterdate = Xbo::setEnterDate();
				$enterby = Xbo::getLoginUser();
			
				$set = new Set();
				$countrfsline = count($rfslineids);
				if($countrfsline > 0){
					for($i=0; $i<$countrfsline; $i++){
						$rfslineid = $rfslineids[$i]["rfslineid"];
						$inspectqty = $rfslineids[$i]["inspectqty"];
						
						if($inspectqty == 0) continue;
						$set = new Set();
						$rfslineSet = $set->useTransactionManager()->MboSetRemote("rfsline")->setWhere("rfslineid='$rfslineid'")->MboRemote("rfslinenum")->MboRemote("itemnum")->MboRemote("description")->MboRemote("unitcost")
						->MboRemote("conversionfactor")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("orderunit")->MboRemote("glcreditacct")->MboRemote("gldebitacct")
						->MboRemote("conditiontype")
						->getMboSet();
						
						$rfsline = $rfslineSet->getMbo();
						$rfslinenum = $rfsline->getString("rfslinenum");
						$itemnum = $rfsline->getString("itemnum");
						$description = $rfsline->getString("description");
						$conditiontype = $rfsline->getString("conditiontype");
						$unitcost = $rfsline->getString("unitcost");
						$linecost = floatval($inspectqty)*floatval($unitcost);
						$conversionfactor = $rfsline->getString("conversionfactor");
						$warehouse = $rfsline->getString("warehouse");
						$racknum = $rfsline->getString("racknum");
						$orderunit = $rfsline->getString("orderunit");
						$glcreditacct = $rfsline->getString("glcreditacct");
						$gldebitacct = $rfsline->getString("gldebitacct");
						$chkSet = new Set();
						$rfsinsitemSet = $chkSet->useTransactionManager()->MboSetRemote("rfsinsitem")->setWhere("rfsnum='$rfsnum' and rfscourierlinenum='$rfscourierlinenum' and rfsinslinenum='$rfslinenum'")->MboRemote("rfsinsitemid")->getMboSet();
						$countRfsIns = $rfsinsitemSet->counts();
						if($countRfsIns == 0){
							
							$set2 = new Set();
							$set2->useTransactionManager()->MboSetRemote("rfsinsitem")->setValue("rfsnum", $rfsnum)->setValue("rfscourierlinenum", $rfscourierlinenum)->setValue("rfsinslinenum", $rfslinenum)->setValue("itemnum", $itemnum)->setValue("description", $description)
							->setValue("inspectqty", $inspectqty)->setValue("orderunit", $orderunit)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("conversionfactor", $conversionfactor)
							->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)->setValue("inspectiontype", "inspect")->setValue("isapproved", "N")
							->setValue("conditiontype", $conditiontype)
							->add();
							
						}else{
							continue;
						}
					}
					Set::throws(["msg"=>"ok"]);
				}
			}
		}
	}
	public static function CopyPOLineToRFSLine()
	{
		
		$polineids = Super::getParam("copypolinetorfsline");
		$rfsnum = Super::getParam("rfsnum");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$set = new Set();
		$countpoline = count($polineids);
		for($i=0; $i<$countpoline; $i++){
			$polineid = $polineids[$i]["polineid"];
			$set = new Set();
			$polineSet = $set->useTransactionManager()->MboSetRemote("poline")->setWhere("polineid = $polineid")->MboRemote("polinenum")->MboRemote("itemnum")->MboRemote("description")
			->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("tax")->MboRemote("taxcost")->MboRemote("orderqty")->MboRemote("orderunit")->MboRemote("conversionfactor")->MboRemote("conditiontype")
			->MboRemote("partnum")->MboRemote("category")->MboRemote("catalog")->MboRemote("warehouse")->MboRemote("glcreditacct")->MboRemote("gldebitacct")->getMboSet();
			
			$poline = $polineSet->getMbo();
			$polinenum = $poline->getString("polinenum");
			$itemnum = $poline->getString("itemnum");
			$description = $poline->getString("description");
			$unitcost = $poline->getString("unitcost");
			$linecost = $poline->getString("linecost");
			$tax = $poline->getString("tax");
			$taxcost = $poline->getString("taxcost");
			$orderqty = $poline->getString("orderqty");
			$conditiontype = $poline->getString("conditiontype");
			$orderunit = $poline->getString("orderunit");
			$conversionfactor = $poline->getString("conversionfactor");
			$partnum = $poline->getString("partnum");
			$category = $poline->getString("category");
			$catalog = $poline->getString("catalog");
			$warehouse = $poline->getString("warehouse");
			$glcreditacct = $poline->getString("glcreditacct");
			$gldebitacct = $poline->getString("gldebitacct");
			
			$chekset = new Set();
			$isExist = $chekset->useTransactionManager()->MboSetRemote("rfsline")->setWhere("rfsnum = '$rfsnum' and rfslinenum='$polinenum'")->MboRemote("rfslinenum")->getMboSet();
			$countisExist = $isExist->counts();
			if($countisExist ==0){
				$set2 = new Set();
				$set2->useTransactionManager()->MboSetRemote("rfsline")->setValue("rfsnum", $rfsnum)->setValue("rfslinenum", $polinenum)->setValue("itemnum", $itemnum)->setValue("description", $description)
				->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("tax", $tax)->setValue("taxcost", $taxcost)->setValue("orderqty", $orderqty)
				->setValue("orderunit", $orderunit)->setValue("conversionfactor", $conversionfactor)->setValue("partnum", $partnum)->setValue("category", $category)
				->setValue("catalog", $catalog)->setValue("warehouse", $warehouse)->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
				->setValue("conditiontype", $conditiontype)
				->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->add();
			}else{
				continue;
			}
			
		}
		
		Set::throws(["msg"=>"ok"]);
	}
} 

Class ValidateTables {	
	public static function tbce(){
		
	}
	public static function tbcx(){
		$payload = Super::getParam("tbcx");
		$dataPackage = $payload["dataPackage"];

		$childTable = $dataPackage["datasetChild"];
		$deleteBy = $dataPackage["ownership"]["userid"];
		$parent = $dataPackage["ownership"]["xboset"];
		$parentid = $dataPackage["ownership"]["xbosetid"];
		$selectedRows =  $dataPackage["selectedRows"];

		$rfs = (new Set())->useTransactionManager()->MboSetRemote("rfs")->where("rfsid=?", $parentid)->getMboSet()->getMbo();
		$rfsnum = $rfs->getString("rfsnum");
		$ponum = $rfs->getString("ponum");	

		if($childTable == "rfscourier"){
			foreach($selectedRows as $row){
				$rfscourierid = $row["rfscourierid"];
				$rfscourier = (new Set())->useTransactionManager()->MboSetRemote("rfscourier")->where("rfscourierid =?", $rfscourierid)->getMboSet()->getMbo();
				$rfscourierlinenum = $rfscourier->getString("rfscourierlinenum");
				
				$rfsinsitemSet = (new Set())->useTransactionManager()->MboSetRemote("rfsinsitem")->where("rfsnum=? and rfscourierlinenum=?", $rfsnum, $rfscourierlinenum)->getMboSet();
				if($rfsinsitemSet->isEmpty()){
					//courier table can be deleted
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();		
				}else{
					$rfsinsitem = $rfsinsitemSet->getMbo();
					$inspectdate = $rfsinsitem->getString("inspectdate");
					$date_ctx = "on Date : $inspectdate";
					if(empty($inspectdate)) $date_ctx = "";

					$rfsinslinenum = $rfsinsitem->getString("rfsinslinenum");
					$line_ctx = "on Line : $rfsinslinenum";
					if(empty($rfsinslinenum)) $line_ctx = "";
					Super::sendJsonResponse("error", "Courier Table has related record with Inspection Item $line_ctx $date_ctx");	
				}
			}
			
		}elseif($childTable == "rfsinsitem"){
			foreach($selectedRows as $row){
				$rfsinsitemid = $row["rfsinsitemid"];
				$rfsinsitem = (new Set())->useTransactionManager()->MboSetRemote("rfsinsitem")->where("rfsinsitemid =  ? ", $rfsinsitemid)->getMboSet()->getMbo();
				$inspectStatus = $rfsinsitem->getString("inspectiontype");
				$rfsinslinenum = $rfsinsitem->getString("rfsinslinenum");
				if($inspectStatus == "accepted" || $inspectStatus == "intransit" || $inspectStatus == "reject"){
					$remarks = "";
					if ($inspectStatus == "accepted") {
						$remarks = ". The items have been checked and are ready for journal posting.";
					}
					if ($inspectStatus == "intransit") {
						$remarks = ". The items are being returned to the vendor for replacement.";
					}
					if ($inspectStatus == "reject") {
						$remarks = ". The items have been received in the warehouse for defect follow-up processing.";
					}
					Super::sendJsonResponse("error", "Cannot Delete on Line $rfsinslinenum. Inspection item cannot be deleted with status: $inspectStatus $remarks");

				}else{
					//rfs inspection item can be deleted
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();		
				}
			}
		}elseif($childTable == "rfsinsteam"){
			Super::sendJsonResponse("error", "Inspection Team Table record cannot be deleted for Auditing Information");
		}elseif($childTable == "rfsinsdoc"){
			Super::sendJsonResponse("error", "Inspection Document Tablerecord cannot be deleted for Auditing Information");
		}elseif($childTable == "rfsreturn" || $childTable == "matrectrans"){
			Super::sendJsonResponse("error", "Transaction Table cannot be deleted");
		}elseif($childTable == "rfsline"){
			foreach($selectedRows as $row){
				$rfslineid = $row["rfslineid"];
				$rfsline  = (new Set())->useTransactionManager()->MboSetRemote("rfsline")->where("rfslineid =  ? ", $rfslineid)->getMboSet()->getMbo();
				$rfslinenum = $rfsline->getString("rfslinenum");
				//Check RFSINSLINE
				$rfsinslineSet = (new Set())->useTransactionManager()->MboSetRemote("rfsinsitem")->where("rfsinslinenum=? AND rfsnum=?", $rfslinenum, $rfsnum)->getMboSet();
				if($rfsinslineSet->isEmpty()){
					//Check Matrectrans
					$matrectransSet = (new Set())->useTransactionManager()->MboSetRemote("matrectrans")->where("ponum=? AND polinenum=?", $ponum, $rfslinenum)->getMboSet();
					if($matrectransSet->isEmpty()){
						//RFS Line can be deleted
						$_POST["delChildPackage"] = $dataPackage;
						Super::deleteChildren();		
					}else{
						Super::sendJsonResponse("error", "Cannot delete GRI line. There are related records linked to a material receipt transaction.");
					}
				}else{
					$rfsinsitem = $rfsinslineSet->getMbo();
					$rfsinslinenum = $rfsinsitem->getString("rfsinslinenum");
					$inspectStatus = $rfsinsitem->getString("inspectiontype");
					$remarks = "";
					if ($inspectStatus == "accepted") {
						$remarks = ". The items have been checked and are ready for journal posting.";
					}
					if ($inspectStatus == "intransit") {
						$remarks = ". The items are being returned to the vendor for replacement.";
					}
					if ($inspectStatus == "reject") {
						$remarks = ". The items have been received in the warehouse for defect follow-up processing.";
					}
					Super::sendJsonResponse("error", "Cannot delete GRI line. The item is in the inspection process on Line: $rfsinslinenum, with Status: $inspectStatus $remarks");
				}

			}
			
		}
		

	}
}
	

?>