<?php
require_once ("../../../eat.php");


Class ActionSet {
	public static function createpr(){
		$srinum = Super::getParam("createpr");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		
		$set = new Set();
		$scopeSet = $set->MboSetRemote("sriscopechange")->setWhere("srinum='$srinum'")->getMboSet();
		if($scopeSet->isEmpty()){
			$msg = "noscope";
			Set::throws(["msg"=>$msg]);
			return;
		}
		
		$setpo = new Set();
		$poSet = $setpo->MboSetRemote("sri")->setWhere("srinum='$srinum'")->setRelationship("po", "sri.ponum = po.ponum")
		->MboRemote("sri.ponum")->MboRemote("po.vendor")->MboRemote("po.description as podesc")->MboRemote("po.orgcode")->MboRemote("po.sitecode")
		->MboRemote("po.budgetnum")
		->getMboSet();
		$po = $poSet->getMbo();
		
		$vendor = $po->getString("vendor");
		$orgcode = $po->getString("orgcode");
		$sitecode = $po->getString("sitecode");
		$podesc = $po->getString("podesc");
		$budgetnum = $po->getString("budgetnum");
		$glcreditacct = Xbo::getVendorAcct($vendor, $orgcode, $sitecode);		
		$linetype = "services";
		$conversionfactor = 1;
		$receiptstatus = "NONE";
		
		//getBaseCurrency
		$set = new Set();
		$basecurrency = $set->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("basecurrency")->getMboSet()->getMbo()->getString("basecurrency");

		//check PR if exist
		$setpr = new Set();
		$prSet = $setpr->MboSetRemote("pr")->setWhere("srinum='$srinum'")->MboRemote("prnum")->getMboSet();
		
		if($prSet->isEmpty()){
			$getAutoNum = new Super();
			$prnum = $getAutoNum->getAutoNumber("pr", "update_next_value")->autonumber;
			$priority = "normal";
			$status = "new";
			$set = new Set();
			$set->MboSetRemote("pr")
			->setValue("prnum", $prnum)->setValue("description", "Scope Change for PO: $ponum - $podesc")
			->setValue("priority", $priority)->setValue("status", "new")->setValue("statusdate", $enterdate)
			->setValue("budgetnum", $budgetnum)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->setValue("srinum", $srinum)->setValue("exchangerate", 1)->setValue("currencycode", $basecurrency)
			->setValue("vendor", $vendor)
			->add();
		}else{
			$pr = $prSet->getMbo();
			$prnum = $pr->getString("prnum");
		}
		
		
		$set = new Set();
		$sriscopeSet = $set->MboSetRemote("sriscopechange sc")
		->setRelationship("sriinspection si", "sc.srinum = si.srinum and sc.sriinslinenum = si.sriinslinenum")
		->setWhere("sc.srinum='$srinum' and (sc.isprocessed='N' or sc.isprocessed is null)")
		->MboRemote("sc.srinum")
		->MboRemote("sc.sriinslinenum")
		->MboRemote("sc.scopetitle")
		->MboRemote("sc.scopedescription")
		->MboRemote("sc.approvedby")
		->MboRemote("sc.approvaldate")
		->MboRemote("sc.orderqty")
		->MboRemote("sc.orderunit")
		->MboRemote("si.wonum")
		->getMboSet();
		
		$counts = $sriscopeSet->counts();
		$requestedby = null;
		$requireddate = null;
		for($i=0; $i<$counts; $i++){
			$sriscope = $sriscopeSet->getMbo();
			$srinum = $sriscope->getString("srinum");
			$sriinslinenum = $sriscope->getString("sriinslinenum");
			$scopetitle = $sriscope->getString("scopetitle");
			$scopedescription = $sriscope->getString("scopedescription");
			$approvedby = $sriscope->getString("approvedby");
			$approvaldate = $sriscope->getString("approvaldate");
			$requireddate = $approvaldate;
			$requestedby = $approvedby;
			$orderqty = $sriscope->getString("orderqty");
			$orderunit = $sriscope->getString("orderunit");
			$wonum = $sriscope->getString("wonum");
			$prlinenum = Xbo::getLineNumber("prline", "prlinenum", "prnum='$prnum'");
			$setprl = new Set();
			$prlineSet = $setprl->MboSetRemote("prline prl")->setWhere("srinum='$srinum' and sriinslinenum='$sriinslinenum'")->getMboSet();
			$gldebitacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode);
			if($prlineSet->isEmpty()){
				$set2 = new Set();
				$set2->MboSetRemote("prline")
				->setValue("prnum", $prnum)
				->setValue("prlinenum", $prlinenum)
				->setValue("linetype", $linetype)
				->setValue("description", $scopetitle)
				->setValue("orderqty", $orderqty)
				->setValue("orderunit", $orderunit)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)
				->setValue("srinum", $srinum)
				->setValue("sriinslinenum", $sriinslinenum)
				->setValue("receiptstatus", $receiptstatus)
				->setValue("conversionfactor", $conversionfactor)
				->setValue("requireddate", $requireddate)
				->setValue("wonum", $wonum)
				->add();
				
				$set3 = new Set();
				$set3->MboSetRemote("sriscopechange")->setWhere("srinum='$srinum' and sriinslinenum='$sriinslinenum'")->setValue("isprocessed", "Y")->setValue("prnum", $prnum)->setValue("prlinenum", $prlinenum)->change();
				
			}else{
				continue;
			}
			
		}
		
		//update pr.requestedby & pr.requireddate
		$set = new Set();
		$set->MboSetRemote("pr")->setWhere("prnum='$prnum'")
		->setValue("requestedby", $requestedby)
		->setValue("requireddate", $requireddate)
		->change();
		
		$msg = "success";
		Set::throws(["msg"=>$msg, "prnum"=>$prnum]);
		
	}
	
	public static function setServicePOLineReceiptStatus(string $ponum)
	{
		
		$sql = "
			SELECT
				pl.ponum,
				pl.polinenum,
				CASE
					WHEN COALESCE(SUM(srt.quantity), 0) = 0 THEN 'NONE'
					WHEN COALESCE(SUM(srt.quantity), 0) < pl.orderqty THEN 'PARTIAL'
					WHEN COALESCE(SUM(srt.quantity), 0) >= pl.orderqty THEN 'COMPLETE'
					ELSE 'UNKNOWN'  
				END AS receipt_status
			FROM
				poline pl
			LEFT JOIN
				servrectrans srt ON pl.ponum = srt.ponum AND pl.polinenum = srt.polinenum
			WHERE pl.ponum = ? AND pl.linetype = 'services'
			GROUP BY
				pl.ponum, pl.polinenum, pl.orderqty
		";
		$lineStatuses = Set::fetchAll($sql, [$ponum]);

		// Langkah 2: Loop di PHP (super cepat, tanpa kueri) dan lakukan UPDATE
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
					-- Jika semua baris punya status 'NONE', maka header 'NONE'
					WHEN SUM(CASE WHEN pl.receiptstatus = 'NONE' THEN 1 ELSE 0 END) = COUNT(pl.polineid) THEN 'NONE'
					-- Jika semua baris punya status 'COMPLETE', maka header 'COMPLETE'
					WHEN SUM(CASE WHEN pl.receiptstatus = 'COMPLETE' THEN 1 ELSE 0 END) = COUNT(pl.polineid) THEN 'COMPLETE'
					-- Jika ada setidaknya satu 'PARTIAL' atau 'NONE' (dan tidak semuanya sama), maka 'PARTIAL'
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

	
		$statusResult = Set::fetchAll($sql, [$ponum]);

		if (!empty($statusResult) && isset($statusResult[0]['receipt_status'])) {
			
			$receipt_status = $statusResult[0]['receipt_status'];

			// Lakukan UPDATE di dalam transaksi yang sedang berjalan
			(new Set())->useTransactionManager()
				->MboSetRemote("po")
				->where("ponum = ?", $ponum)
				->setValue("receipt", $receipt_status) // Asumsi nama kolomnya 'receipt'
				->change();
				
		} else {
			
			throw new Exception("Could not determine aggregate receipt status for PO '$ponum'.");
			error_log("Could not determine aggregate receipt status for PO '$ponum'. It may not exist or have no lines.");
		}
	}
		
	public static function CompleteServiceInspection()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$srinum = Super::getParam("CompleteServiceInspection");
			if (empty($srinum)) {
				throw new InvalidArgumentException("SRI Number is not given in the payload.");
			}
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			$financialperiod = date('Ym', strtotime($enterdate));

			$sriSet = (new Set())->useTransactionManager()->MboSetRemote("sri")->where("srinum=?", $srinum)->MboRemote("ponum, sridate, status")->getMboSet();

			if($sriSet->isEmpty()){
				Super::sendJsonResponse("error", "Service Receipt Inspection Not Found!");
			}

			$sri = $sriSet->getMbo();
			$ponum = $sri->getString("ponum");
			$sridate = $sri->getString("sridate");
			$current_status = strtolower(trim($sri->getString("status")));

			
			$errormsg = [];

			$postatus = (new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->getMboValue("status");
			$postatus = strtolower($postatus);
			if($postatus != "sent"){
				$errormsg[] = "PO Status must be changed to 'sent' first before receiving the service work.";
			}

			if (empty($ponum)) {
				$errormsg[] = "PO number is required and missing from this SRI record.";
			}

			if (empty($sridate)) {
				$errormsg[] = "SRI Date is required.";
			}

			if (empty($current_status)) {
				$errormsg[] = "SRI Status is missing/invalid.";
			} else {
				if ($current_status !== 'approved') {
					if (in_array($current_status, ['draft', 'inprogress', 'new'])) {
						$errormsg[] = "SRI status must be 'APPROVED' before receiving vendor services. Current status: " . strtoupper($current_status);
					} elseif (in_array($current_status, ['rejected', 'cancelled', 'canceled', 'closed'])) {
						$errormsg[] = "Cannot process SRI because it has been " . strtoupper($current_status) . ".";
					} else {
						// Status lain yang tidak dikenal
						$errormsg[] = "Invalid SRI status for processing: " . strtoupper($current_status);
					}
				}
			}

			if (!empty($errormsg)) {
				$finalErrorMessage = implode("<br>", $errormsg);
				Super::sendJsonResponse("error", $finalErrorMessage);
			}


			//Validasi SRI Inspection
			$inspectionLines = (new Set())->useTransactionManager()
				->MboSetRemote("sriinspection")
				->where("srinum = ?", $srinum)
				->getMboSet()
				->fetchData();

			if (empty($inspectionLines)) {
				$errormsg[] = "This SRI has no inspection lines to process.";
			} else {
				// Langkah 5: Loop di PHP untuk memvalidasi setiap baris
				foreach ($inspectionLines as $line) {
					$lineNum = $line['sriinslinenum']; // Untuk pesan error yang lebih jelas

					// Validasi #1: Kolom Rating tidak boleh kosong
					if (empty($line['qualityrating'])) {
						$errormsg[] = "Line $lineNum: Quality Rating is required.";
					}
					if (empty($line['communicationrating'])) {
						$errormsg[] = "Line $lineNum: Communication Rating is required.";
					}
					if (empty($line['responsivenesrating'])) { // Perhatikan typo, sesuaikan dengan kolom DB
						$errormsg[] = "Line $lineNum: Responsiveness Rating is required.";
					}

					// Validasi #2: Kolom Tanggal
					$actualStartDate = $line['actualstartdate'] ? new DateTime($line['actualstartdate']) : null;
					$actualFinishDate = $line['actualfinishdate'] ? new DateTime($line['actualfinishdate']) : null;

					if (!$actualStartDate) {
						$errormsg[] = "Line $lineNum: Actual Start Date is required.";
					}
					if (!$actualFinishDate) {
						$errormsg[] = "Line $lineNum: Actual Finish Date is required.";
					}

					// Validasi #3: Logika antar Tanggal (hanya jika kedua tanggal valid)
					if ($actualStartDate && $actualFinishDate) {
						if ($actualFinishDate < $actualStartDate) {
							$errormsg[] = "Line $lineNum: Actual Finish Date cannot be earlier than Actual Start Date.";
						}
					}

					// Validasi #4: Progress harus 100
					// Gunakan isset() karena progress bisa jadi '0' yang valid tapi empty() anggap kosong.
					if (!isset($line['progress']) || $line['progress'] === '' || $line['progress'] === null) {
						$errormsg[] = "Line $lineNum: Progress is required.";
					} 
				}
			}

			// Langkah 6: Gabungkan semua error (dari header DAN lines) dan kirim jika ada
			if (!empty($errormsg)) {
				$finalErrorMessage = implode("<br>", $errormsg);
				Super::sendJsonResponse("error", $finalErrorMessage);
			
			}

			$sriInspectionLines = (new Set())->useTransactionManager()
				->MboSetRemote("sriinspection sri")
				->where("sri.srinum = ? AND sri.status = 'approved' AND (sri.isprocessed = 'N' OR sri.isprocessed IS NULL)", $srinum)
				->setRelationship("po", "sri.ponum = po.ponum")
				->setRelationship("poline pl", "sri.ponum = pl.ponum AND sri.polinenum = pl.polinenum")
				->MboRemote("sri.srinum, sri.sriinslinenum, sri.ponum, sri.polinenum, sri.description, sri.orderqty, sri.orderunit, sri.wonum, sri.tax, sri.taxcost, sri.progress, sri.actualfinishdate, sri.qualitynotes, po.vendor, po.orgcode, po.sitecode")
				->MboRemote("pl.linecost AS poline_linecost,  pl.orderqty AS poline_orderqty, pl.serviceitemnum")
				->getMboSet()
				->fetchData();

			if (empty($sriInspectionLines)) {
				$tm->commit(); 
				Super::sendJsonResponse("error", "No approved and unprocessed inspection lines found for SRI $srinum.");
				return;
			}
			
			$ponum = $sriInspectionLines[0]['ponum'];


			/* Validasi Jumlah Service Receipt VS jumlah POLINE.ORDERQTY */

			$poLineNums = array_unique(array_column($sriInspectionLines, 'polinenum'));
			$existingReceiptsSet = (new Set())->useTransactionManager()
				->MboSetRemote("servrectrans")
				->where("ponum = ?", $ponum)
				->whereIn("polinenum", $poLineNums)
				->MboRemote("polinenum, SUM(quantity) as total_received_qty")
				->group("polinenum")
				->getMboSet();
			
			// Buat Map
			$existingReceiptsMap = $existingReceiptsSet->fetchDataAssoc('polinenum');
			
		
			// SIMULASIKAN total penerimaan BARU
			foreach ($sriInspectionLines as $line) {
				$polinenum = $line['polinenum'];
				$orderQtyOnPo = (float)($line['poline_orderqty'] ?? 0);
				$alreadyReceivedQty = (float)($existingReceiptsMap[$polinenum]['total_received_qty'] ?? 0);
				$thisReceiptQty = (float)($line['orderqty'] ?? 0); // Kuantitas dari inspeksi ini

				// Hitung total akhir jika inspeksi ini diproses
				$finalTotalQty = $alreadyReceivedQty + $thisReceiptQty;

				// VALIDASI!
				if ($finalTotalQty > $orderQtyOnPo) {
					throw new Exception(
						"Validation failed for PO Line $ponum-$polinenum: " .
						"Cannot receive quantity of {$thisReceiptQty}. " .
						"Already received {$alreadyReceivedQty} out of {$orderQtyOnPo} ordered. " .
						"This transaction would result in a total of {$finalTotalQty}."
					);
				}
			}


			/* END VALIDATION */


			$servRecTransData = [];
			$processedLineIds = [];

			foreach ($sriInspectionLines as $line) {
				$progressPercentage = (float)($line['progress'] ?? 0) / 100;
				if ($progressPercentage <= 0) {
					continue;
				}
				$baseLineCost = (float)($line['poline_linecost'] ?? 0);
				
				$calculatedLineCost = $baseLineCost * $progressPercentage;
				$calculatedUnitCost = ($line['orderqty'] > 0) ? $calculatedLineCost / $line['orderqty'] : 0;
				$calculatedTaxCost = (float)($line['taxcost'] ?? 0) * $progressPercentage;
				
				$gldebitacct = Xbo::getWOAccount($line['wonum'], $line['orgcode'], $line['sitecode']);
				$glcreditacct = Xbo::getVendorAcct($line['vendor'], $line['orgcode'], $line['sitecode']);

				$servRecTransData[] = [
					"srinum" => $line['srinum'],
					"sriinslinenum" => $line['sriinslinenum'],
					"ponum" => $line['ponum'],
					"serviceitemnum" => $line['serviceitemnum'],
					"polinenum" => $line['polinenum'],
					"linetype" => "services",
					"description" => $line['description'],
					"remarks" => $line['qualitynotes'],
					"quantity" => $line['orderqty'],
					"orderunit" => $line['orderunit'],
					"unitcost" => $calculatedUnitCost,
					"linecost" => $calculatedLineCost,
					"taxcost" => $calculatedTaxCost,
					"actualdate" => $line['actualfinishdate'],
					"wonum" => $line['wonum'],
					"gldebitacct" => $gldebitacct,
					"glcreditacct" => $glcreditacct,
					"enterby" => $enterby,
					"enterdate" => $enterdate,
					"financialperiod" => $financialperiod,
					"orgcode" => $line['orgcode'],
					"sitecode" => $line['sitecode'],
					"transtype" => "RECEIPT"
				];
				$processedLineIds[] = $line['sriinslinenum'];
			}

			if (!empty($servRecTransData)) {
				//Insert to Servrectrans
				(new Set())->useTransactionManager()->MboSetRemote("servrectrans")->addStream($servRecTransData);
				//Update Processed = Y for Service Inspection
				(new Set())->useTransactionManager()
					->MboSetRemote("sriinspection")
					->where("srinum = ?", $srinum)
					->whereIn("sriinslinenum", $processedLineIds)
					->setValue("isprocessed", 'Y')
					->change();
			}

			self::setServicePOLineReceiptStatus($ponum);
			self::setPOReceiptStatus($ponum);

			$tm->commit();
			Super::sendJsonResponse("success", "Service has been received");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error",  $e->getMessage());
		}
	}

	public static function getMaxReceivableQty()
	{
		try {
			$srinum      = Super::getParam("getMaxReceivableQty");
			$ponum       = Super::getParam("ponum");
			$polinenum   = Super::getParam("polinenum");
			$changed_progress        = floatval(Super::getParam("newprogress"));
			$changed_sriinspectionid = Super::getParam("sriinspectionid");

			if (empty($ponum) || empty($polinenum) || empty($srinum)) {
				throw new Exception("SRI Number, PO Number, and PO Line Number are required.");
			}

			// Validasi Progress:
			// Jumlahkan progress dari baris lain dalam srinum + polinenum yang SAMA,
			// kecuali baris yang sedang diedit (identified by PK: sriinspectionid)
			$sql = "SELECT SUM(progress) as total_used_progress 
					FROM sriinspection                
					WHERE ponum = ? 
					AND polinenum = ? 
					AND sriinspectionid <> ?"; 

			$result      = Set::fetchAll($sql, [$ponum, $polinenum, $changed_sriinspectionid]);
			$usedProgress = floatval($result[0]['total_used_progress'] ?? 0);

			$maxAllowedProgress = 100.0 - $usedProgress;

			if (round($changed_progress, 2) > round($maxAllowedProgress, 2)) {
				Set::throws([
					"msg"               => "New progress exceeds maximum allowed. Maximum allowed is: " . $maxAllowedProgress . "%",
					"maxAllowedProgress" => $maxAllowedProgress
				]);
				return;
			}

			// Ambil data poline untuk kalkulasi
			$polineSet = (new Set())->useTransactionManager()
				->MboSetRemote("poline")
				->where("ponum = ? AND polinenum = ?", $ponum, $polinenum)
				->MboRemote("unitcost, taxcost, orderqty")
				->getMboSet();

			$poline = $polineSet->fetch();
			if (!$poline) {
				throw new Exception("PO Line not found.");
			}

			$unitcost = floatval($poline["unitcost"] ?? 0);
			$taxcost  = floatval($poline["taxcost"]  ?? 0);
			$orderqty = floatval($poline["orderqty"] ?? 0);

			$new_linecost = ($changed_progress / 100) * ($orderqty * $unitcost);
			$new_taxcost  = ($changed_progress / 100) * $taxcost;
			$new_orderqty = ($changed_progress / 100) * $orderqty;

			Set::throws([
				"msg"          => "valid",
				"new_linecost" => round($new_linecost, 2),
				"new_taxcost"  => round($new_taxcost,  2),
				"new_orderqty" => round($new_orderqty, 2)
			]);

		} catch (Exception $e) {
			Set::throws(["msg" => "error", "content" => $e->getMessage()]);
		}
	}


	public static function CopySrilineToSriinspection()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// =====================================================================
			// 1. VALIDASI INPUT
			// =====================================================================
			$srinum   = Super::getParam("copysrilinetosriinspection");
			$srilines = Super::getParam("srilines");

			if (empty($srinum)) {
				throw new InvalidArgumentException("SRI Number is required.");
			}
			if (!isset($srilines) || !is_array($srilines) || empty($srilines)) {
				$tm->commit();
				Super::sendJsonResponse("error", "No SRI Lines selected to copy.");
				return;
			}

			$enterdate  = Xbo::setEnterDate();
			$enterby    = Xbo::getLoginUser();
			$srilineIds = array_column($srilines, "srilineid");

			// =====================================================================
			// 2. BATCH FETCH: Data sri_summary (remaining_qty sudah exclude SRI aktif)
			//    VIEW sri_summary menggunakan sl.srinum sebagai konteks,
			//    sehingga inspected_in_other_sri otomatis exclude srinum saat ini.
			// =====================================================================
			$summaryDataMap = (new Set())->useTransactionManager()
				->MboSetRemote("sri_summary ss")
				->setRelationship(
					"sriline sl",
					"sl.srilineid = ss.sri_summaryid"
				)
				->whereIn("ss.sri_summaryid", $srilineIds)
				->MboRemote("
					ss.sri_summaryid,
					ss.srinum,
					ss.srilinenum,
					ss.ponum,
					ss.polinenum,
					ss.description,
					ss.unitcost,
					ss.orderqty,
					ss.orderunit,
					ss.srivendorlinenum,
					ss.inspected_in_other_sri,
					ss.remaining_qty,
					sl.tax,
					sl.taxcost,
					sl.wonum
				")
				->getMboSet()
				->fetchDataAssoc('sri_summaryid');

			if (empty($summaryDataMap)) {
				throw new Exception("Could not find matching SRI Summary data.");
			}

			// =====================================================================
			// 3. BATCH FETCH: Total progress yang sudah ada per polinenum (semua SRI)
			//    Digunakan untuk validasi cap 100% global.
			// =====================================================================
			$ponums    = array_unique(array_column(array_values($summaryDataMap), 'ponum'));
			$polinums  = array_unique(array_column(array_values($summaryDataMap), 'polinenum'));

			$usedProgressMap = [];
			if (!empty($ponums) && !empty($polinums)) {
				$progressRows = Set::fetchAll(
					"SELECT ponum, polinenum, COALESCE(SUM(progress), 0) as total_used
					FROM sriinspection
					WHERE ponum IN (" . implode(',', array_fill(0, count($ponums), '?')) . ")
					AND polinenum IN (" . implode(',', array_fill(0, count($polinums), '?')) . ")
					GROUP BY ponum, polinenum",
					array_merge(array_values($ponums), array_values($polinums))
				);
				foreach ($progressRows as $pr) {
					$key = $pr['ponum'] . '_' . $pr['polinenum'];
					$usedProgressMap[$key] = floatval($pr['total_used']);
				}
			}

			// =====================================================================
			// 4. OPTIMASI: Ambil max sriinslinenum sekali saja
			// =====================================================================
			$currentMaxLineNum = (int)(new Set())->useTransactionManager()
				->MboSetRemote("sriinspection")
				->where("srinum = ?", $srinum)
				->MboRemote("MAX(sriinslinenum) as max_line")
				->getMboValue("max_line", 0);

			// =====================================================================
			// 5. PROSES DI MEMORI
			// =====================================================================
			$inspectionDataToInsert = [];
			$skippedLines           = 0;

			foreach ($srilineIds as $srilineid) {
				$row = $summaryDataMap[$srilineid] ?? null;
				if (!$row) continue;

				$orderqty         = floatval($row['orderqty']);
				$remaining_qty    = floatval($row['remaining_qty']);

				// Skip jika VIEW sudah menunjukkan tidak ada sisa
				if ($remaining_qty <= 0) {
					$skippedLines++;
					continue;
				}

				// --- Validasi Cap Global 100% ---
				$mapKey           = $row['ponum'] . '_' . $row['polinenum'];
				$usedProgress     = $usedProgressMap[$mapKey] ?? 0.0;
				$maxAllowed       = round(100.0 - $usedProgress, 2);

				if ($maxAllowed <= 0) {
					// Sudah 100% ter-inspect dari semua SRI, skip
					$skippedLines++;
					continue;
				}

				// Hitung percentage dari remaining_qty
				$percentage = ($orderqty > 0)
					? round(($remaining_qty / $orderqty) * 100, 2)
					: 0.0;

				// Cap jika melebihi maxAllowed
				if ($percentage > $maxAllowed) {
					$percentage    = $maxAllowed;
					$remaining_qty = round(($maxAllowed / 100) * $orderqty, 6);
				}

				// Kalkulasi cost
				$unitcost = floatval($row['unitcost']);
				$taxcost  = floatval($row['taxcost']);
				$linecost = round($unitcost * $remaining_qty, 2);

				// Taxcost proporsional terhadap percentage yang di-cap
				$new_taxcost = round(($percentage / 100) * $taxcost, 2);

				$currentMaxLineNum += 10;

				$inspectionDataToInsert[] = [
					"srinum"           => $srinum,
					"srilinenum"       => $row['srilinenum'],
					"sriinslinenum"    => $currentMaxLineNum,
					"srivendorlinenum" => $row['srivendorlinenum'],
					"ponum"            => $row['ponum'],
					"polinenum"        => $row['polinenum'],
					"description"      => $row['description'],
					"unitcost"         => $unitcost,
					"orderqty"         => $remaining_qty,
					"orderunit"        => $row['orderunit'],
					"linecost"         => $linecost,
					"tax"              => $row['tax'],
					"taxcost"          => $new_taxcost,
					"wonum"            => $row['wonum'],
					"progress"         => number_format($percentage, 2),
					"status"           => "inprogress",
					"isprocessed"      => "N",
					"enterby"          => $enterby,
					"enterdate"        => $enterdate,
				];
			}

			// =====================================================================
			// 6. BATCH INSERT
			// =====================================================================
			if (empty($inspectionDataToInsert)) {
				throw new Exception(
					"No valid lines to copy. All quantities have been fully inspected."
				);
			}

			(new Set())->useTransactionManager()
				->MboSetRemote("sriinspection")
				->addStream($inspectionDataToInsert);

			$tm->commit();

			$msg = "Successfully copied " . count($inspectionDataToInsert) . " line(s).";
			if ($skippedLines > 0) {
				$msg .= " {$skippedLines} line(s) skipped (already fully inspected).";
			}

			Super::sendJsonResponse("success", $msg, ["srinum" => $srinum]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to copy lines: " . $e->getMessage());
		}
	}

	public static function CopyPolineToSriline(){
		$srinum = Super::getParam("copypolinetosriline");
		$polines = Super::getParam("polines");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$count = count($polines);
		for($i=0; $i<$count; $i++){
			$polineid = $polines[$i]["polineid"];
			
			$set = new Set();
			$polineSet = $set->MboSetRemote("poline")->setWhere("polineid='$polineid'")
			->MboRemote("ponum")
			->MboRemote("polinenum")
			->MboRemote("description")
			->MboRemote("unitcost")
			->MboRemote("linecost")
			->MboRemote("orderqty")
			->MboRemote("orderunit")
			->MboRemote("tax")
			->MboRemote("taxcost")
			->MboRemote("wonum")
			->getMboSet();
			
			$poline = $polineSet->getMbo();
			
			$ponum = $poline->getString("ponum");
			$polinenum = $poline->getString("polinenum");
			$description = $poline->getString("description");
			$unitcost = $poline->getString("unitcost");
			$linecost = $poline->getString("linecost");
			$orderqty = $poline->getString("orderqty");
			$orderunit = $poline->getString("orderunit");
			$tax = $poline->getString("tax");
			$taxcost = $poline->getString("taxcost");
			$wonum = $poline->getString("wonum");
			
			$check = new Set();
			$check->MboSetRemote("sriline")->setWhere("srinum='$srinum' and ponum='$ponum' and polinenum='$polinenum'")->getMboSet();
			if($check->isEmpty()){
				$srilinenum =  Xbo::getLineNumber("sriline", "srilinenum", "srinum='$srinum'");
				$set = new Set();
				$set->MboSetRemote("sriline")
				->setValue("srinum", $srinum)
				->setValue("srilinenum", $srilinenum)
				->setValue("ponum", $ponum)
				->setValue("polinenum", $polinenum)
				->setValue("description", $description)
				->setValue("unitcost", $unitcost)
				->setValue("orderqty", $orderqty)
				->setValue("orderunit", $orderunit)
				->setValue("linecost", $linecost)
				->setValue("tax", $tax)
				->setValue("taxcost", $taxcost)
				->setValue("wonum", $wonum)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->add();
			}else{
				continue;
			}
		}
		Set::throws(["srinum"=>$srinum, "status"=>"success", "msg"=>"ok"]);
	}
}
	

?>