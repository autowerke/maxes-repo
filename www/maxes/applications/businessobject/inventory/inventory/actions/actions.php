<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");



Class ActionSet {
	public static function invflCostAdj(){
		$payload = Super::getParam("invflCostAdj");
		if(empty($payload)){
			Super::sendJsonResponse("error", "Select Row to Physical Count Adjustment");
		}
		$invtransdata = [];
		$enterby = Xbo::getLoginUser();
		$transdate = Xbo::setEnterDate();

		foreach($payload as $row){
			$invfifolifocostid = $row["invfifolifocostid"];
			$newCost = $row["newcurrentcost"];
			
			$newcostadjustdate = $row["newcostadjustdate"];

			if($newCost == null || empty($newcostadjustdate)) {
				Super::sendJsonResponse("error", "New Cost and Adjustment Date is required.");
			}

			if($newCost < 0){
				Super::sendJsonResponse("error", "New Cost cannot be under 0");
			}
			$transtype = "CURBALADJ";

			$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost ifl")->where("ifl.invfifolifocostid=?", $invfifolifocostid)
			->setRelationship("v_invbalance vib", "ifl.itemnum = vib.itemnum AND ifl.warehouse = vib.warehouse")
			->MboRemote("ifl.itemnum, ifl.physqty, ifl.physcountdate, ifl.quantity, ifl.warehouse, ifl.unitcost, ifl.conditiontype, ifl.racknum, ifl.financialperiod, 
			ifl.invfifolifocostid, ifl.orgcode, ifl.sitecode, ifl.quantity, ifl.batchnum, ifl.costdate, ifl.expdate, vib.avgcost")->getMboSet();
			
			if($invfifolifocostSet->isEmpty()){
				Super::sendJsonResponse("error", "No Record Found!");
			}

			$invfl = $invfifolifocostSet->fetch();
			
			$orgcode = $invfl["orgcode"];
			$sitecode = $invfl["sitecode"];
			$unitcost = $invfl["unitcost"];
			$quantity = $invfl["quantity"];
			$batchnum  = $invfl["batchnum"];
			$costdate  = $invfl["costdate"];
			$expdate  = $invfl["expdate"];
			$avgcost  = $invfl["avgcost"];

			
			$cost = $newCost;
			$oldcost = $unitcost;
			
			$variancecost = $cost - $oldcost;
			$varianceqty = 0;
			$warehouse = $invfl["warehouse"];
			
			// GL accounts
			$controlacc   = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
			$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
			$transtype    = 'COSTADJ';
			
			if ($newCost == $unitcost) {
				continue; // no variance -> no transaction
			}
				
			if ($cost > $oldcost) {
				$gldebitacct = $controlacc;
				$glcreditacct = $shrinkageacc;
			} else {
				$gldebitacct = $shrinkageacc;
				$glcreditacct = $controlacc;
			}

			$invtransdata [] = [
				"itemnum" => $invfl["itemnum"],
				"transtype" => $transtype,
				"warehouse" => $warehouse,
				"racknum" => $invfl["racknum"],
				"conditiontype" => $invfl["conditiontype"],
				"quantity" => $quantity, 
				"oldquantity" => $quantity,
				"cost" => $cost,
				"oldcost" => $oldcost,
				"varianceqty" => $varianceqty,
				"variancecost" => $variancecost,
				"stdcost" => $cost,
				"avgcost" => $avgcost,
				"gldebitacct" => $gldebitacct,
				"glcreditacct" => $glcreditacct,
				"orgcode" => $orgcode,
				"sitecode" => $sitecode,
				"enterby" => $enterby,
				"enterdate" => $transdate,
				"batchnum" => $batchnum,
				"costdate" => $costdate,
				"expdate" => $expdate,
				"financialperiod" => Xbo::getFinancialPeriod($transdate)
			];

			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=?", $invfifolifocostid)
			->setValue("unitcost", $newCost)
			->setValue("transtype", $transtype)
			->setValue("transdate", $transdate)
			->setValue("financialperiod", Xbo::getFinancialPeriod($newcostadjustdate))
			->change();
		}

		if(empty($invtransdata)){
			Super::sendJsonResponse("error", "No cost adjustment is needed. The variance cost is the same.");
		}
		(new Set())->MboSetRemote("invtrans")->addStream($invtransdata);

		Super::sendJsonResponse("success", "Item adjusted based on current balance adjustment.");


	}	
	public static function invflcurbalAdj(){
		$payload = Super::getParam("invflcurbalAdj");
		if(empty($payload)){
			Super::sendJsonResponse("error", "Select Row to Physical Count Adjustment");
		}
		$invtransdata = [];
		
		$enterby = Xbo::getLoginUser();
		$transdate = Xbo::setEnterDate();

		foreach($payload as $row){
			$invfifolifocostid = $row["invfifolifocostid"];
			$newcurrentqty = $row["newcurrentqty"];
			$newstockadjustdate = $row["newstockadjustdate"];
		
			if($newcurrentqty ==  null || empty($newstockadjustdate)) {
				Super::sendJsonResponse("error", "New Current Qty and Adjustment Date is required.");
			}

			if($newcurrentqty < 0){
				Super::sendJsonResponse("error", "Current Qty cannot be under 0");
			}
			$transtype = "CURBALADJ";

			$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost ifl")->where("ifl.invfifolifocostid=?", $invfifolifocostid)
			->setRelationship("v_invbalance vib", "ifl.itemnum = vib.itemnum AND ifl.warehouse = vib.warehouse")
			->MboRemote("ifl.itemnum, ifl.physqty, ifl.physcountdate, ifl.quantity, ifl.warehouse, ifl.unitcost, ifl.conditiontype, ifl.racknum, ifl.financialperiod, 
			ifl.invfifolifocostid, ifl.orgcode, ifl.sitecode, ifl.quantity, ifl.batchnum, ifl.costdate, ifl.expdate, vib.avgcost")->getMboSet();
			
			if($invfifolifocostSet->isEmpty()){
				Super::sendJsonResponse("error", "No Record Found!");
			}

			$invfl = $invfifolifocostSet->fetch();
			$orgcode = $invfl["orgcode"];
			$sitecode = $invfl["sitecode"];
			$unitcost = $invfl["unitcost"];
			$newCurbal = $newcurrentqty;
			$oldCurbal =  $invfl["quantity"];
			$cost = $unitcost*$newCurbal;
			$oldcost = $unitcost*$oldCurbal;
			$varianceqty = $newCurbal - $oldCurbal;
			$variancecost = $varianceqty * $cost;
			$batchnum  = $invfl["batchnum"];
			$costdate  = $invfl["costdate"];
			$expdate  = $invfl["expdate"];
			$avgcost  = $invfl["avgcost"];
			$warehouse = $invfl["warehouse"];

			// GL accounts
			$controlacc   = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
			$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
			$transtype    = 'CURBALADJ';

			if ($newCurbal == $oldCurbal) {
				continue; // no variance -> no transaction
			}

			if ($newCurbal > $oldCurbal) {
				$gldebitacct = $controlacc;
				$glcreditacct = $shrinkageacc;
			} else {
				$gldebitacct = $shrinkageacc;
				$glcreditacct = $controlacc;
			}

			$invtransdata [] = [
				"itemnum" => $invfl["itemnum"],
				"transtype" => $transtype,
				"warehouse" => $warehouse,
				"racknum" => $invfl["racknum"],
				"conditiontype" => $invfl["conditiontype"],
				"quantity" => $newCurbal, 
				"oldquantity" => $oldCurbal,
				"cost" => $cost,
				"oldcost" => $oldcost,
				"varianceqty" => $varianceqty,
				"variancecost" => $variancecost,
				"stdcost" => $cost,
				"avgcost" => $avgcost,
				"gldebitacct" => $gldebitacct,
				"glcreditacct" => $glcreditacct,
				"orgcode" => $orgcode,
				"sitecode" => $sitecode,
				"enterby" => $enterby,
				"enterdate" => $transdate,
				"batchnum" => $batchnum,
				"costdate" => $costdate,
				"expdate" => $expdate,
				"financialperiod" => Xbo::getFinancialPeriod($transdate)
			];

			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=?", $invfifolifocostid)
			->setValue("quantity", $newcurrentqty)
			->setValue("transtype", $transtype)
			->setValue("transdate", $transdate)
			->setValue("financialperiod", Xbo::getFinancialPeriod($newstockadjustdate))
			->change();
		}

		if(empty($invtransdata)){
			Super::sendJsonResponse("error", "No Current Balance adjustment is needed. The variance Quantity is the same.");
		}

		(new Set())->MboSetRemote("invtrans")->addStream($invtransdata);

		Super::sendJsonResponse("success", "Item adjusted based on current balance adjustment.");

	}
	public static function invflphyscountAdj(){
		$payload = Super::getParam("invflphyscountAdj");
		if(empty($payload)){
			Super::sendJsonResponse("error", "Select Row to Physical Count Adjustment");
		}

		foreach($payload as $row){
			$invfifolifocostid = $row["invfifolifocostid"];
			$newphysqty = $row["newphysqty"];
			$newphyscountdate = $row["newphyscountdate"];

			if($newphysqty == null || empty($newphyscountdate)) {
				Super::sendJsonResponse("error", "New Physical Count and Physical Count Date is required.");
			}

			if($newphysqty < 0){
				Super::sendJsonResponse("error", "Physical Count Qty cannot be under 0");
			}

			$transtype = "PHYSCOUNT";
			$transdate = Xbo::setEnterDate();

			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=?", $invfifolifocostid)
			->setValue("physqty", $newphysqty)
			->setValue("physcountdate", $newphyscountdate)
			->setValue("transtype", $transtype)
			->setValue("transdate", $transdate)
			->setValue("financialperiod", Xbo::getFinancialPeriod($newphyscountdate))
			->change();

		}
		Super::sendJsonResponse("success", "Item adjusted based on physical count.");

	}
	public static function abc_stl(){
		$payload = Super::getParam("abc_stl");
		$jsonPayload = json_encode($payload);
		$endpoint = "/generate/sentinel_abc";
		$llm = Super::sendToML($endpoint, $jsonPayload);
		Super::sendJsonResponse("success", $llm);

	}
	public static function getInvCart(){
		try {
			$invSet = (new Set())->useTransactionManager()
								->MboSetRemote("invcart")
								->MboRemote("itemnum, quantity, linecost, remarks, replenishfrom, enterby")
								->getMboSet(); 
			$allInvCartData = $invSet->fetchData();

			$data = new stdClass;
			$data->cart_data = $allInvCartData;
			
			
			$endpoint = "/inventory/audit-cart";
			$payload = json_encode($data);
			
			$llm = Super::sendToML($endpoint, $payload, True); //True => return all response datas
			

			$response_data = json_decode($llm, true);
			
			if (isset($response_data['status'])) {
				$status = "success";
				$data = $response_data["alerts"];
				$message = "Audit Complete";
				
			} else {
				$status = "error";
				$message = "Audit Error";
				$data = [];
				
			}
			//echo json_encode($data);
			Super::sendJsonResponse($status, $message, $data);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", "Failed to retrieve cart: " . $e->getMessage());
		}
	}
	
	public static function executeAssetConversion()
	{
		$tm = TransactionManager::getInstance();
		try {
			$tm->beginTransaction();

			$payload = Super::getParam("executeAssetConversion")["data"];
			if (empty($payload) || !is_array($payload)) {
				http_response_code(400);
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}

			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			$orgSiteInfo = Xbo::getOrgcode($enterby);
			$orgcode = $orgSiteInfo->orgcode;
			$sitecode = $orgSiteInfo->sitecode;

			$newAssetsData = [];

			$invFifoLifoCostIds = array_column($payload, 'invfifolifocostid');
			$itemNums = array_unique(array_column($payload, 'itemnum'));
			
			$itemDetailsSet = (new Set())->useTransactionManager()->MboSetRemote("item")
										->whereIn("itemnum", $itemNums)
										->getMboSet();
			$itemDetailsMap = [];
			while($item = $itemDetailsSet->fetch()){
				$itemDetailsMap[$item['itemnum']] = $item;
			}

		

			foreach ($payload as $conversionData) {
					// --- PERBAIKAN DI SINI ---
					$serialnum = $conversionData['serialnumber'];
					$assetnum = $conversionData['assetnum'];
					$itemnum = $conversionData['itemnum'];

					if (empty($serialnum) || empty($assetnum)) {
						throw new Exception("Asset Number and Serial Number are required for all new assets.");
					}

					$itemMaster = $itemDetailsMap[$itemnum] ?? null;
					if (!$itemMaster) {
						throw new Exception("Master data for item '$itemnum' not found.");
					}
					
					// Siapkan data untuk INSERT ke tabel 'assets'
					$newAssetsData[] = [
						'assetnum' => $assetnum,
						'description' => $conversionData['description'],
						'serialnum' => $serialnum,
						'brand' => $itemMaster['brand'],
						'manufacturer' => $itemMaster['manufacturer'],
						'assettype' => 'OPERATION',
						'locnum' => $conversionData['locnum'],
						'installdate' => $enterdate,
						'status' => 'OPERATING',
						'statusdate' => $enterdate,
						'isdown' => 'N',
						'healthrate' => 100.00,
						'lasthealthupdatedate' => $enterdate,
						'classificationcode' => $itemMaster['classificationcode'],
						'orgcode' => $orgcode,
						'sitecode' => $sitecode,
						'enterby' => $enterby,
						'enterdate' => $enterdate,
						'changeby' => $enterby,
						'changedate' => $enterdate,
						'itemnum' => $itemnum 
					];
				}

			// PROSES PENGURANGAN STOK & PENAMBAHAN BIAYA (DENGAN PERBAIKAN)
	
			$quantityToReduce = array_count_values($invFifoLifoCostIds);
			
			foreach ($quantityToReduce as $invfifolifocostid => $qty) {
					$invFifoLifoCostMbo = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
													->where("invfifolifocostid=?", $invfifolifocostid)->getMboSet()->getMbo();
					
					if (!$invFifoLifoCostMbo) {
						throw new Exception("Inventory ID '$invfifolifocostid' not found.");
					}

					$itemnum = $invFifoLifoCostMbo->getString("itemnum");
					$warehouse = $invFifoLifoCostMbo->getString("warehouse");
					$currentQuantity = (float) $invFifoLifoCostMbo->getString("quantity");
					$unitcost = (float) $invFifoLifoCostMbo->getString("unitcost");

					if ($currentQuantity < $qty) {
						throw new Exception("Insufficient stock for itemnum '$itemnum' in warehouse '$warehouse'. Required: $qty, Available: $currentQuantity.");
					}

					$newQuantity = $currentQuantity - $qty;

					// Update kuantitas di invfifolifocost
					(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
							->where("invfifolifocostid=?", $invfifolifocostid)
							->setValue("quantity", $newQuantity)
							->change();
							
					// Tambahkan 'unitcost' ke data ASET yang akan dibuat
					foreach($newAssetsData as $key => $assetData){
						$payloadAsset = $payload[$key];
						if($payloadAsset['invfifolifocostid'] == $invfifolifocostid){
							$newAssetsData[$key]['purchaseprice'] = $unitcost; // Asumsi kolom di tabel assets adalah 'purchaseprice'
						}
					}
				}
			
		
			if (empty($newAssetsData)) {
				throw new Exception("No asset data was prepared for creation.");
			}

			(new Set())->useTransactionManager()
						->MboSetRemote("assets") // Target tabel diubah menjadi 'assets'
						->addStream($newAssetsData);


			$tm->commit();
			self::sendJsonResponse("success", count($newAssetsData) . " items have been successfully converted to assets.");

			} catch (Exception $e) {
				$tm->rollback();
				http_response_code(400);
				self::sendJsonResponse("error", $e->getMessage());
			}
		}

	
		public static function executeToolConversion()
		{
			$tm = TransactionManager::getInstance();
			try {
				
				$tm->beginTransaction();

				$payload = Super::getParam("executeToolConversion")["data"];
				if (empty($payload) || !is_array($payload)) {
					http_response_code(400);
					self::sendJsonResponse("error", "Invalid or empty payload.");
					return;
				}

				$enterby = Xbo::getLoginUser();
				$enterdate = Xbo::setEnterDate();
				$newToolsData = []; // Array untuk menampung data tool baru untuk bulk insert

				// Kumpulkan semua invfifolifocostid untuk mengurangi query ke database
				$invFifoLifoCostIds = array_column($payload, 'invfifolifocostid');
				$uniqueInvFifoLifoCostIds = array_unique($invFifoLifoCostIds);
				
				// Ambil detail master item dalam satu query untuk efisiensi
				$itemNums = array_unique(array_column($payload, 'itemnum'));
				$itemDetailsSet = (new Set())->useTransactionManager()->MboSetRemote("item")
											->whereIn("itemnum", $itemNums)
											->getMboSet();
				$itemDetailsMap = [];
				while($item = $itemDetailsSet->fetch()){
					$itemDetailsMap[$item['itemnum']] = $item;
				}

				// LOOP UNTUK VALIDASI JUMLAH ITEM
				foreach ($payload as $conversionData) {
					// Validasi data per item
					if (empty($conversionData['serialnumber'])) {
						throw new Exception("Serial number is required for all new tools.");
					}

					$itemnum = $conversionData['itemnum'];
					
					// Ambil data dari master item yang sudah di query sebelumnya
					$itemMaster = $itemDetailsMap[$itemnum] ?? null;
					if (!$itemMaster) {
						throw new Exception("Master data for item '$itemnum' not found.");
					}

					// Siapkan data untuk INSERT ke tabel 'tools'
					$newToolsData[] = [
						'toolnum' => $conversionData['toolnum'],
						'name' => $conversionData['name'],
						'description' => $conversionData['description'],
						'serialnumber' => $conversionData['serialnumber'],
						'tooltype' => $itemMaster['itemnum'], 
						'category' => $itemMaster['itemgroup'], 
						'brand' => $itemMaster['brand'], 
						'status' => 'AVAILABLE',  
						'locnum' => $conversionData['locnum'],
						'isactive' => 'Y',
						'enterby' => $enterby,
						'enterdate' => $enterdate,
						'changeby' => $enterby,
						'changedate' => $enterdate
					];
				}

				// PROSES PENGURANGAN STOK & PENAMBAHAN BIAYA
				// Hitung berapa banyak unit yang diambil dari record id
				$quantityToReduce = array_count_values($invFifoLifoCostIds);
				
				foreach ($quantityToReduce as $invfifolifocostid => $qty) {
					$invFifoLifoCostMbo = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
													->where("invfifolifocostid=?", $invfifolifocostid)->getMboSet()->getMbo();

					$itemnum = $invFifoLifoCostMbo->getString("itemnum");
					$warehouse = $invFifoLifoCostMbo->getString("warehouse");
					
					if (!$invFifoLifoCostMbo) {
						throw new Exception("Inventory ID '$invfifolifocostid' not found.");
					}
					
					$currentQuantity = (float) $invFifoLifoCostMbo->getString("quantity");
					$unitcost = (float) $invFifoLifoCostMbo->getString("unitcost");
					
					if ($currentQuantity < $qty) {
						throw new Exception("Insufficient stock for itemnum '$itemnum' and warehouse '$warehouse', ID '$invfifolifocostid'. Required: $qty, Available: $currentQuantity.");
					}

					$newQuantity = $currentQuantity - $qty;

					// Update kuantitas di invfifolifocost
					(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
							->where("invfifolifocostid=?", $invfifolifocostid)
							->setValue("quantity", $newQuantity)
							->change();
							
					// Tambahkan 'unitcost' ke data tool yang akan dibuat, berdasarkan batch asalnya
					foreach($newToolsData as $key => $toolData){
						// Cari semua tool baru yang berasal dari batch ini
						$payloadTool = $payload[$key]; // Dapatkan payload asli yang sesuai
						if($payloadTool['invfifolifocostid'] == $invfifolifocostid){
							$newToolsData[$key]['unitcost'] = $unitcost;
						}
					}
				}
				
				if (empty($newToolsData)) {
					throw new Exception("No tool data was prepared for creation.");
				}

				(new Set())->useTransactionManager()
						->MboSetRemote("tools")
						->addStream($newToolsData);

				// FINALISASI
			
				$tm->commit();
				self::sendJsonResponse("success", count($newToolsData) . " items have been successfully converted to tools.");

			} catch (Exception $e) {
				$tm->rollback();
				self::sendJsonResponse("error", $e->getMessage());
			}
		}

		public static function getUnitCostConversion(){
			$payload = Super::getParam("getUnitCostConversion");
			if (empty($payload) || !is_array($payload)) {
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}

			$invfifolifocostSet = (new Set())->MboSetRemote("invfifolifocost")->whereIn("invfifolifocostid", $payload)
			->MboRemote("invfifolifocostid, unitcost")->getMboSet()->fetchData();

			if (empty($invfifolifocostSet)) {
				throw new Exception("Could not find Unit Cost data for the provided IDs.");
			}

			Super::sendJsonResponse("success", "Unit Cost Retrieved", $invfifolifocostSet);
		}
		public static function AssetConversion(){
			$payload = Super::getParam("assetConversion");
			if(empty($payload) || !is_array($payload)){
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}
			

		}

		public static function ToolConversion(){
			$payload = Super::getParam("toolConversion");
			if(empty($payload) || !is_array($payload)){
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}
			

		}
		public static function applyStockingParamChange() {
			// 1. Validasi Payload 
			$payload = Super::getParam("applyStockingParamChange");
			if (empty($payload) || !is_array($payload)) {
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}

			$itemnum = $payload['itemnum'] ?? null;
			$warehouse = $payload['warehouse'] ?? null;
			$new_rop = $payload['recom_rop'] ?? null;
			$new_ss = $payload['recom_ss'] ?? null;

			if (empty($itemnum) || empty($warehouse)) {
				self::sendJsonResponse("error", "Required parameter 'itemnum' or 'warehouse' is missing.");
				return;
			}
			if (is_null($new_rop) && is_null($new_ss)) {
				self::sendJsonResponse("error", "No recommendation value (recom_rop or recom_ss) was provided.");
				return;
			}

			// 3. Validasi Record
			$mainSet = new Set();
			$whereClause = "itemnum='$itemnum' AND warehouse='$warehouse'";
			$inventorySet = $mainSet->MboSetRemote("inventory")->setWhere($whereClause)->getMboSet();

			if ($inventorySet->isEmpty()) {
				self::sendJsonResponse("error", "Inventory record not found for item {$itemnum} in warehouse {$warehouse}.");
				return;
			}

			// 4. Eksekusi 
			$changeby = Xbo::getLoginUser();
			$changedate = Xbo::setEnterDate();
			
			// rantai query
			$mainSet = new Set();
			$updateQuery = $mainSet->MboSetRemote("inventory")->setWhere($whereClause);

			$changedParams = []; 

			// Tambahkan setValue() kondisional
			if (!is_null($new_rop)) {
				// Gunakan nama kolom yang konsisten: 'reorderpoint'
				$updateQuery->setValue("reorderqty", floatval($new_rop));
				$changedParams[] = "ROP";
			}
			if (!is_null($new_ss)) {
				$updateQuery->setValue("safetystock", floatval($new_ss));
				$changedParams[] = "Safety Stock";
			}

			$updateQuery->setValue("changeby", $changeby)
						->setValue("changedate", $changedate)
						->change();

			// 5. Kirim Response Sukses
			$successMessage = implode(' and ', $changedParams) . " for item {$itemnum} has been updated successfully.";
			self::sendJsonResponse("success", $successMessage);
		}

		public static function IssueSellingItem($data){
			$orgcode = $data->orgcode;
			$itemnum = $data->itemnum;
			$warehouse = $data->warehouse;
			$conditiontype = $data->conditiontype;
			$pricecode = $data->pricecode;
			$issuettpe = $data->issuetype;
			
			$bo = new Xbo();
			$pricing = $bo->getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype, $pricecode);
			$pricing_msg = $pricing->msg;
			if($pricing_msg == "cantfounddefaultprice") {
				self::sendJsonResponse("error", "Cannot find default price");
				
			}
			$primarydiscount = floatval($pricing->primarydiscount);
			$secondarydiscount = floatval($pricing->secondarydiscount);
			$discountprice = floatval($pricing->discountprice);
			
			$unitprice = floatval($pricing->unitprice);
			$unitpricediscount = floatval($pricing->unitpricediscount);
			
			$lineprice = $unitprice*$quantity;
			$linepricediscount = $unitpricediscount*$quantity;
			
			$unitprofit = $unitprice-$avgcost;
			$unitprofitdiscount = $unitpricediscount-$avgcost;
			
			$lineprofit = $unitprofit*$quantity;
			$lineprofitdiscount = $unitprofitdiscount*$quantity;
					
			$unitcommission = Xbo::GetUnitCommission($itemnum, $warehouse, $pricecode, $conditiontype);
			$linecommission = $unitcommission*$quantity;
			
			
			return null;
		}
		public static function sendJsonResponse($status, $message) {			
			echo json_encode(['status' => $status, 'message' => $message]);
			return;
			exit();
		}
		
		
		public static function quickIssueCurrentItem(){
			
			$params = [];
			
			$params['itemnum'] = $_POST["issuecuritem"];
			$params['warehouse'] =  $_POST["warehouse"];
			$params['racknum'] = $_POST["racknum"];
			$params['conditiontype'] = $_POST["conditiontype"];
			$params['orgcode'] = $_POST["orgcode"];
			$params['sitecode'] = $_POST["sitecode"];
			$params['wotranstype'] = $_POST["wotranstype"];
			$params['issuedate'] = $_POST["issuedate"];
			$params['wonum'] = $_POST["wonum"];
			
			$params['assetnum'] = $_POST["assetnum"];
			$params['location'] = $_POST["location"];
			$params['gldebitacct'] = $_POST["gldebitacct"];
			$params['glcreditacct'] = $_POST["glcreditacct"];
			$params['issuedqty']= $_POST["quantity"];
			$params['requestedby'] = $_POST["requestedby"];
			
			$params['enterdate']  = Xbo::setEnterDate();
			$params['enterby']  = Xbo::getLoginUser();
			
			$params['conditionrate'] = Xbo::getConditionRate($params['conditiontype'], "itemcondition", $params['itemnum'], $params['warehouse'] );
			$params['financialperiod']  = Xbo::getFinancialPeriod(); 


			$params['remarks'] = $_POST["remarks"];

			
			
			self::IssueItemsWithoutReservation($params);
		}
		
		public static function IssueItemsWithoutReservation($params = null) {
			
			$params_array [] = $params;
			$payload = $params_array;
			
			if (empty($payload) || !is_array($payload)) {
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}
			
			
			$processedCount = 0;
			$errorMessages = [];
			

			// 2. Loop untuk setiap item yang akan di-issue dari UI
			
			foreach ($payload as $itemToIssue) {
				
				try {
					// --- Langkah A: Ekstrak data ---
					$id = $itemToIssue["id"] ?? null;
					$wonum = $itemToIssue["wonum"] ?? null;
					$itemnum = $itemToIssue["itemnum"] ?? null;
					$warehouse = $itemToIssue["warehouse"] ?? null;
					$racknum = $itemToIssue["racknum"] ?? null;
					$conditiontype = $itemToIssue["conditiontype"] ?? null;
					$issuedqty = floatval($itemToIssue["issuedqty"] ?? 0);
					$issuedate = $itemToIssue["issuedate"] ?? Xbo::setEnterDate();
					$remarks = $itemToIssue["remarks"] ?? null;
					$requestedby = $itemToIssue["requestedby"] ?? null;

					$woStatus = (new Set())->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("status")->getMboSet()->getMbo()->getString("wonum");
					$woStatus = strtolower($woStatus);
					
					$setInv = new Set();
					$invMboSet = $setInv->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' AND warehouse='$warehouse'")->getMboSet();
					
					// FIX #1: Validasi NULL sebelum memanggil getString()
					if ($invMboSet->isEmpty()) {
						$errorMessages[] = "Inventory record not found for item {$itemnum} in warehouse {$warehouse}.";
						continue;
					}
					$invMbo = $invMboSet->getMbo();
					$issuetype = $invMbo->getString("issuetype");
					if (empty($issuetype)) {
						$errorMessages[] = "Issuetype not defined for item {$itemnum}.";
						continue;
					}
					
					// --- Langkah C: Siapkan Paket Data $context yang Lengkap ---
					$setWO = new Set();
					$woMbo = $setWO->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->getMboSet()->getMbo();
					$context = [
						'itemnum' => $itemnum,
						'issuetype' => $issuetype,
						'wonum' => $wonum,
						'mrnum' => $itemToIssue["mrnum"] ?? null,
						'enterby' => Xbo::getLoginUser(),
						'enterdate' => Xbo::setEnterDate(),
						'actualdate' => $issuedate,
						'assetnum' => $woMbo ? $woMbo->getString('assetnum') : null,
						'location' => $woMbo ? $woMbo->getString('location') : null,
						'gldebitacct' => $woMbo ? Xbo::getWOAccount($wonum, $woMbo->getString('orgcode'), $woMbo->getString('sitecode')) : null,
						//'requestedby' => $woMbo ? $woMbo->getString("owner") : null,
						'requestedby' => $requestedby ?? null,
						'consignment' => $invMbo->getString("consignment"),
						'orgcode' => $invMbo->getString("orgcode"),
						'sitecode' => $invMbo->getString("sitecode"),
						'remarks'  => $remarks
					];
					
					
					if ($issuetype === "FIFO" || $issuetype === "LIFO" || $issuetype === "EXPDATE") {
						$issueResult = self::executeFifoLifoIssuing($itemnum, $issuedqty, $issuetype, $context, $warehouse, $racknum, $conditiontype);
					} elseif ($issuetype === "AVG") {
						$issueResult = self::executeAvgIssuing($itemnum, $issuedqty, $warehouse, $racknum, $conditiontype, $context);
					} else {
						$issueResult = ['status' => 'error', 'message' => "Unsupported issuetype: {$issuetype}"];
					}
					
					// --- Langkah E: Closing - Update/Hapus invreserve ---
					if ($issueResult && $issueResult['status'] === 'success') {
						$processedCount++;
					} else {
						$errorMessages[] = "For item {$itemnum}: " . ($issueResult['message'] ?? 'Unknown error.');
					}
					
					
					Xbo::calculateWOCost($wonum);
					
				} catch (Exception $e) {
					$errorMessages[] = "A critical system error occurred for item {$itemnum}: " . $e->getMessage();
				}
			} // End of foreach loop
			// --- Langkah F: Kirim Response Akhir ---
			if (empty($errorMessages)) {
				self::sendJsonResponse("success", "Successfully issued {$processedCount} item(s).");
			} else {
				$finalMessage = "Process completed. <br>Success: {$processedCount} record(s). <br>Failures: " . count($errorMessages) . " record(s). Details: <hr>" . implode(' <br> ', $errorMessages);
				self::sendJsonResponse("error", $finalMessage, 409); 
			}
		}
		/**
		 * Helper untuk mengeksekusi issuing HANYA berdasarkan rencana yang SUDAH ADA di invallocations.
		 * Fungsi ini tidak lagi peduli dengan FIFO/LIFO karena itu sudah di-handle sebelumnya.
		
		*/
		private static function executeFromAllocations($invreserveid, $allocationsSet, $context) {
			// 1. Dapatkan informasi item sekali saja dari konteks
			$itemnum = $context['itemnum']; // perlu menambahkan ini ke $context

			// 2. Loop untuk SETIAP BARIS RENCANA ALOKASI
			for ($i = 0; $i < $allocationsSet->counts(); $i++) {
				$allocationMbo = $allocationsSet->getMbo($i);
				$fifocost_id = $allocationMbo->getString('invfifolifocostid');
				$allocated_qty = floatval($allocationMbo->getString('allocatedqty'));

				// 3. VALIDASI JUST-IN-TIME (Anti-Race Condition)
				// Cek langsung ke batch spesifik yang ditunjuk oleh rencana.
				$setFifo = new Set();
				$fifoMboSet = $setFifo->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$fifocost_id'")->getMboSet();			
				$fifoMbo = $fifoMboSet->getMbo();
				
				if ( $fifoMboSet->isEmpty() || floatval($fifoMbo->getString('quantity')) < $allocated_qty) {
					// Rencana sudah tidak valid karena stok fisiknya sudah berubah!
					return ['status' => 'error', 'message' => "Stock mismatch for allocated batch ID {$fifocost_id}. Please revalidate the main reservation."];
				}
				$from_warehouse = $fifoMbo->getString('warehouse');
				$from_racknum = $fifoMbo->getString('racknum');
				$expireddate = $fifoMbo->getString('expdate');
				$batchnum =  $fifoMbo->getString('expdate');
				$conditiontype =  $fifoMbo->getString('conditiontype');
				$costdate =  $fifoMbo->getString('costdate');
				$glcreditacct = Xbo::getwhgl($from_warehouse, "noprint");
				
				// 4. EKSEKUSI PENGURANGAN & PENCATATAN (Jika Validasi Lolos)
				$old_qty = floatval($fifoMbo->getString('quantity'));
				$new_qty = $old_qty - $allocated_qty;

				// --- AKSI A: Kurangi Stok Fisik dari BATCH SPESIFIK ---
				$setUpdate = new Set();
				$setUpdate->MboSetRemote("invfifolifocost")
						->setWhere("invfifolifocostid='$fifocost_id'")
						->setValue("quantity", $new_qty)
						->change();
				
				// --- AKSI B: Catat Transaksi di matusetrans ---
				$unitcost = floatval($fifoMbo->getString("unitcost"));
				$linecost = $unitcost * $allocated_qty;
				$setMatusetrans = new Set();			
				$setMatusetrans->MboSetRemote("matusetrans")
					->setValue("wonum", $context['wonum'] ?? null)
					->setValue("requestedby", $context["requestedby"])
					->setValue("assetnum", $context["assetnum"])	
					->setValue("location", $context["location"])
					->setValue("itemnum", $itemnum)
					->setValue("expireddate", $expireddate)
					->setValue("batchnum", $batchnum)
					->setValue("conditiontype", $conditiontype)				
					->setValue("warehouse", $from_warehouse)
					->setValue("racknum", $from_racknum)
					->setValue("quantity", $allocated_qty * -1)
					->setValue("oldcurbal", $old_qty)
					->setValue("newcurbal", $new_qty)
					->setValue("unitcost", $unitcost) 
					->setValue("linecost", $linecost * -1)
					->setValue("issuetype", $context["issuetype"]) 
					->setValue("costdate", $costdate)
					->setValue("transtype", "ISSUE")
					->setValue("gldebitacct", $context["gldebitacct"])
					->setValue("glcreditacct", $glcreditacct)
					->setValue("isconsignment", $context["consignment"])
					->setValue("orgcode", $context["orgcode"])
					->setValue("sitecode", $context["sitecode"])
					->setValue("actualdate", $context["actualdate"])
					->setValue("mrnum", $context['mrnum'] ?? null)
					->add();

				//Delete Reservation Allocation from Inventory Allocation.
				(new Set())->useTransactionManager()->MboSetRemote("invallocations")->where("invfifolifocostid=? AND invreserveid=?", $fifocost_id, $invreserveid)->remove();
			}
			
			
			return ['status' => 'success', 'message' => 'Successfully issued from detailed allocation plan.'];
		}
		
		
		/**
		 * Menangani proses issuing untuk item dengan tipe FIFO, LIFO, atau EXPDATE.
		 * Fungsi ini melakukan validasi real-time, mengurangi stok dari batch yang benar,
		 * dan mencatat transaksi di matusetrans.
		
		*/
		private static function executeFifoLifoIssuing($itemnum, $quantity_needed, $issuetype, $context, $warehouse, $racknum = null, $conditiontype = null) {

		// 1. Tentukan Aturan Pengurutan (Sorter)
			$sorter = "costdate ASC";
			if ($issuetype === "LIFO") $sorter = "costdate DESC";
			elseif ($issuetype === "EXPDATE") $sorter = "expdate ASC";

			// 2. Bangun WHERE Clause yang Lebih Spesifik dan Aman
			$whereClause = "itemnum='$itemnum' AND warehouse='$warehouse' AND quantity > 0";
			
			// Ini adalah setting jika FIFO LIFO dibatasi oleh Rak tertentu.
			/*
			if (!empty($racknum)) {
				// KASUS 1: User MENSPESIFIKASIKAN rak.
				$safe_racknum = addslashes($racknum); // Contoh sederhana escaping
				$whereClause .= " AND racknum = '{$safe_racknum}'";
			} else {
				// KASUS 2: User TIDAK menspesifikasikan rak.
				// Cari stok yang racknum-nya NULL ATAU string kosong.
				$whereClause .= " AND (racknum IS NULL OR racknum = '')";
			}
			*/
			// tangani conditiontype secara dinamis
			if (!empty($conditiontype)) {
				// KASUS 1: User MENSPESIFIKASIKAN Condition Type.
				$safe_conditiontype = addslashes($conditiontype); // Contoh sederhana escaping
				$whereClause .= " AND conditiontype = '{$safe_conditiontype}'";
			} else {
				// KASUS 2: User TIDAK menspesifikasikan Condition Type.
				// Cari stok yang conditiontype-nya NULL ATAU string kosong.
				$whereClause .= " AND (conditiontype IS NULL OR conditiontype = '')";
			}
			
			// 2. Ambil semua batch fisik yang tersedia, sudah diurutkan sesuai aturan.
			$setFifo = new Set();
			$fifoStockSet = $setFifo->useTransactionManager()->MboSetRemote("invfifolifocost")
									->setWhere($whereClause)
									->order($sorter)
									->getMboSet();

			// 3. VALIDASI STOK REAL-TIME (Anti-Race Condition)
			// Hitung total stok yang benar-benar ada PADA SAAT INI.
			$total_available_realtime = 0;
			if (!$fifoStockSet->isEmpty()) {
				$countRecords = $fifoStockSet->counts();
				for ($k = 0; $k < $countRecords; $k++) {
					$fifostock = $fifoStockSet->getMbo();
					$total_available_realtime += floatval($fifostock->getString('quantity'));
					
				}
			}
			
			if ($total_available_realtime < $quantity_needed) {
				$errorMessage = "Issuing failed for item {$itemnum}. Real-time stock is insufficient. Needed: {$quantity_needed}, Available: {$total_available_realtime}.";
				return ['status' => 'error', 'message' => $errorMessage];
			}
			
			// 4. PROSES "PEMANENAN" (Jika Stok Terbukti Cukup)
			$remaining_needed = $quantity_needed;
			$setFifo = new Set();
			$fifoStockSet = $setFifo->useTransactionManager()->MboSetRemote("invfifolifocost")
									->setWhere($whereClause)
									->order($sorter)
									->getMboSet();
			$countRecords = $fifoStockSet->counts();
			for ($k = 0; $k < $countRecords; $k++) {
				// Hentikan loop jika kebutuhan sudah terpenuhi
				if ($remaining_needed <= 0) {
					break;
				}

				$fifoMbo = $fifoStockSet->getMbo($k); 
				$expireddate = $fifoMbo->getString("expdate");
				// PENYEMPURNAAN: Tambahkan validasi untuk EXPDATE
				if ($issuetype === "EXPDATE" && empty($expireddate)) {
					$batch_info = $fifoMbo->getString('batchnum') ?? $fifoMbo->getString('invfifolifocostid');
					return ['status' => 'error', 'message' => "Issuing failed. Item {$itemnum} requires an expiration date, but batch {$batch_info} does not have one."];
				}
				
				$fifo_id = $fifoMbo->getString('invfifolifocostid');
				
				$old_qty = floatval($fifoMbo->getString('quantity'));
				
				// Tentukan berapa banyak yang akan diambil dari batch ini
				$take_from_this_batch = min($old_qty, $remaining_needed);
				
				// --- AKSI A: Kurangi Stok Fisik di invfifolifocost ---
				$new_qty = $old_qty - $take_from_this_batch;
				//echo "take_from_this_batch: $take_from_this_batch, old_qty:$old_qty, new_qty: $new_qty <br>";
				$from_warehouse = $fifoMbo->getString("warehouse");
				$from_racknum = $fifoMbo->getString("racknum");
				
				$batchnum = $fifoMbo->getString("batchnum");
				$conditiontype = $fifoMbo->getString("conditiontype");
				$costdate =  $fifoMbo->getString("costdate");
				$glcreditacct = Xbo::getwhgl($from_warehouse, "noprint");
				$setUpdate = new Set();
				
				$setUpdate->useTransactionManager()->MboSetRemote("invfifolifocost")
						->setWhere("invfifolifocostid='$fifo_id'")
						->setValue("quantity", $new_qty)
						->change();
				
				// --- AKSI B: Catat Transaksi di matusetrans ---
				$unitcost = floatval($fifoMbo->getString("unitcost"));
				$linecost = $unitcost * $take_from_this_batch;
				
				$setMatusetrans = new Set();			
				$setMatusetrans->useTransactionManager()->MboSetRemote("matusetrans")
					->setValue("wonum", $context['wonum'] ?? null)
					->setValue("requestedby", $context["requestedby"])
					->setValue("assetnum", $context["assetnum"])	
					->setValue("location", $context["location"])
					->setValue("itemnum", $itemnum)
					->setValue("expireddate", $expireddate)
					->setValue("batchnum", $batchnum)
					->setValue("conditiontype", $conditiontype)				
					->setValue("warehouse", $from_warehouse)
					->setValue("racknum", $from_racknum)
					->setValue("quantity", $take_from_this_batch * -1)
					->setValue("oldcurbal", $old_qty)
					->setValue("newcurbal", $new_qty)
					->setValue("unitcost", $unitcost) 
					->setValue("linecost", $linecost * -1)
					->setValue("issuetype", $issuetype) 
					->setValue("costdate", $costdate)
					->setValue("transtype", "ISSUE")
					->setValue("gldebitacct", $context["gldebitacct"])
					->setValue("glcreditacct", $glcreditacct)
					->setValue("isconsignment", $context["consignment"])
					->setValue("orgcode", $context["orgcode"])
					->setValue("sitecode", $context["sitecode"])
					->setValue("actualdate", $context["actualdate"])
					->setValue("remarks", $context["remarks"])
					->setValue("enterdate", Xbo::setEnterDate())
					->setValue("enterby", Xbo::getLoginUser())
					->setValue("mrnum", $context['mrnum'])
					->add(); 
				// Kurangi sisa kebutuhan
				$remaining_needed -= $take_from_this_batch;
			}
			
			$measureunit = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
			
			return ['status' => 'success', 'message' => "Successfully issued {$quantity_needed} {$measureunit} of {$itemnum}."];
		}


		/**
		 * Menangani proses issuing untuk item dengan tipe AVG.
		 * Proses ini dikendalikan oleh input user (lokasi), namun tetap
		 * memanen secara FIFO di dalam lokasi yang ditentukan tersebut.
		
		*/
		
		
		private static function executeAvgIssuing($itemnum, $quantity_needed, $warehouse, $racknum, $conditiontype = null, $context) {
			
			$whereClause = "itemnum='$itemnum' AND warehouse='$warehouse' AND quantity > 0";

			if (!empty($racknum)) {
				// KASUS 1: User MENSPESIFIKASIKAN rak.
				$safe_racknum = addslashes($racknum); // Contoh sederhana escaping
				$whereClause .= " AND racknum = '{$safe_racknum}'";
			} else {
				// KASUS 2: User TIDAK menspesifikasikan rak.
				$whereClause .= " AND (racknum IS NULL OR racknum = '')";
			}
			
			// Where Clause conditiontype
			if (!empty($conditiontype)) {
				// KASUS 1: User MENSPESIFIKASIKAN Condition Type.
				$safe_conditiontype = addslashes($conditiontype); // Contoh sederhana escaping
				$whereClause .= " AND conditiontype = '{$safe_conditiontype}'";
			} else {
				// KASUS 2: User TIDAK menspesifikasikan Condition Type.
				$whereClause .= " AND (conditiontype IS NULL OR conditiontype = '')";
			}
			// 2. Ambil semua batch dari lokasi yang ditentukan, diurutkan FIFO sebagai default.
			//    Ini untuk memastikan konsistensi jika ada beberapa batch di satu rak.
			$setBatches = new Set();
			$availableBatchesSet = $setBatches->MboSetRemote("invfifolifocost")
											->setWhere($whereClause)
											->order("costdate ASC") // FIFO di dalam lokasi terpilih
											->getMboSet();
			
			// 3. VALIDASI STOK REAL-TIME (Anti-Race Condition)
			// Hitung total stok yang ada HANYA di lokasi yang diminta.
			$total_available_in_location = 0;
			if (!$availableBatchesSet->isEmpty()) {
				for ($k = 0; $k < $availableBatchesSet->counts(); $k++) {
					$availableBatches = $availableBatchesSet->getMbo();
					$total_available_in_location += floatval($availableBatches->getString('quantity'));
				}
			}
			
			if ($total_available_in_location < $quantity_needed) {
				$location_info = $warehouse . (!empty($racknum) ? "/{$racknum}" : "");
				$errorMessage = "Issuing failed for item {$itemnum}. Not enough stock at the specified location ({$location_info}). Needed: {$quantity_needed}, Available: {$total_available_in_location}.";
				return ['status' => 'error', 'message' => $errorMessage];
			}

			// 4. Ambil Biaya Rata-Rata (Average Cost) untuk item ini
			// Biaya ini bersifat global untuk item, tidak per-batch.
			$setAvgCost = new Set();
			$inventoryMboSet = $setAvgCost->MboSetRemote("v_invbalance")
										->setWhere("itemnum='$itemnum' AND warehouse='$warehouse'")
										->getMboSet();
			
			if($inventoryMboSet->isEmpty()){
				$errorMessage = "No Record Found for $itemnum,  on location $warehouse. Needed: {$quantity_needed}, Available: {$total_available_in_location}.";
				return ['status' => 'error', 'message' => $errorMessage];
			}
			$inventoryMbo = $inventoryMboSet->getMbo();
			$avgcost = floatval($inventoryMbo->getString('avgcost'));
			
			// 5. PROSES "PEMANENAN" dari LOKASI yang DITENTUKAN
			$setBatches = new Set();
			$availableBatchesSet = $setBatches->MboSetRemote("invfifolifocost")
											->setWhere($whereClause)
											->order("costdate ASC") // FIFO di dalam lokasi terpilih
											->getMboSet();
											
			$remaining_needed = $quantity_needed;
			for ($k = 0; $k < $availableBatchesSet->counts(); $k++) {
				if ($remaining_needed <= 0) break;

				$batchMbo = $availableBatchesSet->getMbo();
				$batch_id = $batchMbo->getString('invfifolifocostid');
				$old_qty = floatval($batchMbo->getString('quantity'));
				$expireddate = $batchMbo->getString('expdate');
				$batchnum = $batchMbo->getString('batchnum');
				$conditiontype =  $batchMbo->getString('conditiontype');
				$from_warehouse = $batchMbo->getString('warehouse');
				$from_racknum = $batchMbo->getString('racknum');
				$take_from_this_batch = min($old_qty, $remaining_needed);
				$costdate = $batchMbo->getString('costdate');
				// --- AKSI A: Kurangi Stok Fisik di invfifolifocost ---
				$new_qty = $old_qty - $take_from_this_batch;
				$setUpdate = new Set();
				$setUpdate->MboSetRemote("invfifolifocost")
						->setWhere("invfifolifocostid='$batch_id'")
						->setValue("quantity", $new_qty)
						->change();
				
				$glcreditacct = Xbo::getwhgl($from_warehouse, "noprint");
				// --- AKSI B: Catat Transaksi di matusetrans ---
				// PENTING: Untuk AVG, linecost dihitung dari avgcost.
				$linecost = $avgcost * $take_from_this_batch;

				$setMatusetrans = new Set();			
				$setMatusetrans->MboSetRemote("matusetrans")
					->setValue("wonum", $context['wonum'] ?? null)
					->setValue("requestedby", $context["requestedby"])
					->setValue("assetnum", $context["assetnum"])	
					->setValue("location", $context["location"])
					->setValue("itemnum", $itemnum)
					->setValue("expireddate", $expireddate)
					->setValue("batchnum", $batchnum)
					->setValue("conditiontype", $conditiontype)				
					->setValue("warehouse", $from_warehouse)
					->setValue("racknum", $from_racknum)
					->setValue("quantity", $take_from_this_batch * -1)
					->setValue("oldcurbal", $old_qty)
					->setValue("newcurbal", $new_qty)
					->setValue("unitcost", $avgcost)
					->setValue("linecost", $linecost * -1)
					->setValue("issuetype", "AVG") 
					->setValue("costdate", $costdate)
					->setValue("transtype", "ISSUE")
					->setValue("gldebitacct", $context["gldebitacct"])
					->setValue("glcreditacct", $glcreditacct)
					->setValue("isconsignment", $context["consignment"])
					->setValue("orgcode", $context["orgcode"])
					->setValue("sitecode", $context["sitecode"])
					->setValue("actualdate", $context["actualdate"])
					->setValue("enterdate", Xbo::setEnterDate())
					->setValue("enterby", Xbo::getLoginUser())
					->setValue("mrnum", $context['mrnum'])
					->setValue("remarks", $context["remarks"])
					->add();

				$remaining_needed -= $take_from_this_batch;
			}
			
			// 6. Kembalikan Hasil Sukses
			return ['status' => 'success', 'message' => "Successfully issued {$quantity_needed} of {$itemnum} (AVG Cost)."];
		}

		
		public static function issueReservedItemsForWorkOrder() {
			
			
			$payload = $_POST["issuereserverditems"] ?? null;
			if (empty($payload) || !is_array($payload)) {
				self::sendJsonResponse("error", "Invalid or empty payload.");
				return;
			}
			
			
			$processedCount = 0;
			$errorMessages = [];
			

			// 2. Loop untuk setiap item yang akan di-issue dari UI
			
			foreach ($payload as $itemToIssue) {
				
				try {
					// --- Langkah A: Ekstrak data ---
					$id = $itemToIssue["id"] ?? null;
					$wonum = $itemToIssue["wonum"] ?? null;
					$itemnum = $itemToIssue["itemnum"] ?? null;
					$warehouse = $itemToIssue["warehouse"] ?? null;
					$racknum = $itemToIssue["racknum"] ?? null;
					$conditiontype = $itemToIssue["conditiontype"] ?? null;
					$issuedqty = floatval($itemToIssue["issuedqty"] ?? 0);
					$issuedate = $itemToIssue["issuedate"] ?? Xbo::setEnterDate();
					
					if (empty($id) || empty($wonum) || empty($itemnum) || empty($warehouse) || $issuedqty <= 0) {
						$errorMessages[] = "Skipping item with ID {$id}: Incomplete data provided.";
						continue;
					}
					$mainSet = new Set(); 	
					// --- Langkah B: Validasi Awal & Dapatkan Aturan Main ---
					$invreserveMbo = $mainSet->MboSetRemote("invreserve")->setWhere("invreserveid='$id'")->getMboSet()->getMbo();
					$invreservenum = $invreserveMbo->getString("invreservenum");

					if (!$invreserveMbo || $invreserveMbo->getString("status") !== "RESERVED") {
						$errorMessages[] = "Cannot process item <strong>{$itemnum}</strong> (Reservation #: <strong>$invreservenum</strong>, ID: <strong>{$id}</strong>): Reservation status is not <strong>RESERVED</strong>.";
						continue;
					}

					$requestedqty = floatval($invreserveMbo->getString("quantity"));

					//  Issue quantity melebihi permintaan
					if ($issuedqty > $requestedqty) {
						$errorMessages[] = "Invalid issue quantity for item <strong>{$itemnum}</strong> (Reservation #: <strong>$invreservenum</strong>, ID: <strong>{$id}</strong>): Requested only <strong>{$requestedqty}</strong>, but attempted to issue <strong>{$issuedqty}</strong>.";
						continue;
					}

					//  Issue quantity tidak boleh nol atau negatif
					if ($issuedqty <= 0) {
						$errorMessages[] = "Invalid issue quantity for item <strong>{$itemnum}</strong> (Reservation #: <strong>$invreservenum</strong>, ID: <strong>{$id}</strong>): Quantity must be greater than zero.";
						continue;
					}

					// 🔍 Validasi ketersediaan inventory
					$setInv = new Set();
					$invMboSet = $setInv->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' AND warehouse='$warehouse'")->getMboSet();

					if ($invMboSet->isEmpty()) {
						$errorMessages[] = "No inventory record found for item <strong>{$itemnum}</strong> in warehouse <strong>{$warehouse}</strong>. Please verify the item and warehouse.";
						continue;
					}
					$invMbo = $invMboSet->getMbo();
					$issuetype = $invMbo->getString("issuetype");
					if (empty($issuetype)) {
						$errorMessages[] = "Issuetype not defined for item {$itemnum}.";
						continue;
					}
					
					// Siapkan Paket Data $context yang Lengkap ---
					$setWO = new Set();
					$woMbo = $setWO->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->getMboSet()->getMbo();
					$context = [
						'itemnum' => $itemnum,
						'issuetype' => $issuetype,
						'wonum' => $wonum,
						'mrnum' => $itemToIssue["mrnum"] ?? null,
						'enterby' => Xbo::getLoginUser(),
						'enterdate' => Xbo::setEnterDate(),
						'actualdate' => $issuedate,
						'assetnum' => $woMbo ? $woMbo->getString('assetnum') : null,
						'location' => $woMbo ? $woMbo->getString('location') : null,
						'gldebitacct' => $woMbo ? Xbo::getWOAccount($wonum, $woMbo->getString('orgcode'), $woMbo->getString('sitecode')) : null,
						'requestedby' => $woMbo ? $woMbo->getString("owner") : null,
						'consignment' => $invMbo->getString("consignment"),
						'orgcode' => $invMbo->getString("orgcode"),
						'sitecode' => $invMbo->getString("sitecode")
					];
					
					
					// --- THE SMART DISPATCHER ---
					$issueResult = null;
					$allocationsSet = $mainSet->MboSetRemote("invallocations")->setWhere("invreserveid='$id'")->getMboSet();
					
					if (!$allocationsSet->isEmpty()) {
						// KASUS A: ADA ALOKASI DETAIL - EKSEKUSI RENCANA
						$issueResult = self::executeFromAllocations($id, $allocationsSet, $context);
					
					}else{
						if ($issuetype === "FIFO" || $issuetype === "LIFO" || $issuetype === "EXPDATE") {
							$issueResult = self::executeFifoLifoIssuing($itemnum, $issuedqty, $issuetype, $context, $warehouse, $racknum, $conditiontype);
						} elseif ($issuetype === "AVG") {
							$issueResult = self::executeAvgIssuing($itemnum, $issuedqty, $warehouse, $racknum, $conditiontype, $context);
						} else {
							$issueResult = ['status' => 'error', 'message' => "Unsupported issuetype: {$issuetype}"];
						}
					}
					// --- Langkah E: Closing - Update/Hapus invreserve ---
					if ($issueResult && $issueResult['status'] === 'success') {
						
						$restqty = $requestedqty - $issuedqty;
						if ($restqty <= 0) {
							$setdel = new Set();
							$setdel->MboSetRemote("invreserve")->setWhere("invreserveid=$id")->remove();
						} else {
							$setresv = new Set();
							$setresv->MboSetRemote("invreserve")->setWhere("invreserveid='$id'")->setValue("quantity", $restqty)->change();
						}
						$processedCount++;
					} else {
						$errorMessages[] = "For item {$itemnum}: " . ($issueResult['message'] ?? 'Unknown error.');
					}
					
					Xbo::calculateWOCost($wonum);
					
				} catch (Exception $e) {
					$errorMessages[] = "A critical system error occurred for item {$itemnum}: " . $e->getMessage();
				}
			} // End of foreach loop
			
				
			// --- Langkah F: Kirim Response Akhir ---
			if (empty($errorMessages)) {
				self::sendJsonResponse("success", "Successfully issued {$processedCount} item(s).");
			} else {
				$finalMessage = "Process completed. <br>Failures: " . count($errorMessages) . " record(s). <br>Success: {$processedCount} record(s). <br> Details: <hr>" . implode(' <br> ', $errorMessages);
				self::sendJsonResponse("error", $finalMessage); 
			}
		}
		
		public static function sumWOCost($wonum){
			
			$WOCost = XboSet::SumWOCost($wonum, true);
			$actualcost = $WOCost["actualcost"] ?? null;
			$plannedcost = $WOCost["plannedcost"] ?? null;
			$variancecost = $WOCost["variancecost"] ?? null;
			$progress = $WOCost["progress"] ?? null;
			
			$set = new Set();
			$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")
			->setValue("actualcost", $actualcost)
			->setValue("plannedcost", $plannedcost)
			->setValue("variancecost", $variancecost)
			->setValue("progress", $progress)				
			->change();
		}
	public static function createprabc() {
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$prnum = Super::getParam("createprabc");
			$itemListPayload = Super::getParam("itemList");
			$actualItemList = $itemListPayload['invcartid'] ?? [];
			$itemListIds = !empty($actualItemList) ? array_column($actualItemList, 'invcartid') : [];

			
			if (empty($prnum) || empty($itemListIds)) {
				throw new InvalidArgumentException("Item List and PR Number are required.");
			}

			$prdesc = "Inventory Replenishment ".Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();
			$orgSite = Xbo::getOrgcode($enterby);
			$orgcode = $orgSite->orgcode;
			$sitecode = $orgSite->sitecode;
			$status = "new";
			if(empty($orgcode) || empty($sitecode)){
				Set::throws(["status"=>"error", "message"=>"The username does not have default Organization and Site"]);
				return;
				exit();
			}

			$description = $prdesc;
			$requestedby = $enterby;
			$priority = "normal";
			$remarks = "Required Items for Inventory Replenishment based on 3 Dimensional ABC Analysis";
			$statusdate = Xbo::setEnterDate();
			$enterdate = $statusdate;
			$requesteddate = $statusdate;
			$requireddate = $requesteddate;
					
			//get Default Currency
			$setc = new Set();
			$currencycode = (new Set())->useTransactionManager()->MboSetRemote("organization")->getMboValue("basecurrency");
			$exchangerate = 1;	

			
			$setpr = new Set();
			$setpr->MboSetRemote("pr")
			->setValue("prnum", $prnum)
			->setValue("description", $description)
			->setValue("requestedby", $requestedby)
			->setValue("priority", $priority)
			->setValue("remarks", $remarks)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("requesteddate", $requesteddate)
			->setValue("requireddate", $requireddate)
			->setValue("currencycode", $currencycode)
			->setValue("exchangerate", $exchangerate)
			->setValue("enterdate", $enterdate)
			->setValue("enterby", $enterby)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->add();
			// Create PR Lines
			$invcartItems = (new Set())->useTransactionManager()
            ->MboSetRemote("invcart")
            ->whereIn("invcartid", $itemListIds)
            ->getMboSet()
            ->fetchDataAssoc('invcartid');

			$prlines = [];
			$prlinenum = 0;
			
			foreach ($itemListIds as $invcartid) {
				$item = $invcartItems[$invcartid] ?? null;
				if (!$item) continue; 
				if (empty($item['warehouse'])) {
					throw new Exception("Warehouse is required for item {$item['itemnum']} for '{$item['remarks']}'");
				}
				$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $item['itemnum'])
				->MboRemote("description, manufacturer")->getMboset();
				$itemMbo = $itemSet->getmbo();
				$description = $itemMbo->getString("description");
				$manufacturer = $itemMbo->getString("manufacturer");

				$prlinenum += 10;
				$prlines[] = [
					"prnum" => $prnum,
					"itemnum" => $item['itemnum'],
					"description" => $description,
					"manufacturer" => $manufacturer,
					"linetype" => "item",
					"prlinenum" => $prlinenum,
					"conditiontype" => $item['conditiontype'],
					"orderqty" => $item['quantity'],
					"unitcost" => $item['unitcost'],
					"linecost" => $item['linecost'],
					"orderunit" => $item['orderunit'],
					"warehouse" => $item['warehouse'],
					"gldebitacct" => $item['gldebitacct'],
					"glcreditacct" => $item['glcreditacct'],
					"requestedby" => $item['enterby']
				];
			}

			if (empty($prlines)) {
				throw new Exception("No valid items found from the provided list to create PR lines.");
			}
			
			
			(new Set())->useTransactionManager()->MboSetRemote("prline")->addStream($prlines);

			//Calculate PR pretax, tax, Total Cost, total basecost
			 $prTotals = (new Set())->useTransactionManager()
            ->MboSetRemote("prline")
            ->where("prnum = ?", $prnum)
            ->MboRemote("SUM(linecost) as pretaxtotal, SUM(COALESCE(taxcost, 0)) as totaltax")
            ->getMboSet()
            ->fetch();

			 $pretaxtotal = (float)($prTotals['pretaxtotal'] ?? 0);
       		 $totaltax = (float)($prTotals['totaltax'] ?? 0);
       		 $totalbasecost = $pretaxtotal + $totaltax;

			
			//Update PR Header Cost, use default exchange rate.
			(new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)
			->setValue("pretaxtotal",$pretaxtotal )
			->setValue("totaltax",$totaltax )
			->setValue("totalcost",$totalbasecost )
			->setValue("totalbasecost",$totalbasecost )
			->change();
				
			//create pr status date history
			(new Set())->useTransactionManager()->MboSetRemote("prstatus")
            ->setValue("prnum", $prnum)
            ->setValue("status", "new")
            ->setValue("changeby", $enterby)
            ->setValue("changedate", $statusdate) // Menggunakan $statusdate yang sudah ada
            ->setValue("remarks", "PR for Inventory Replenishment.")
            ->add();
			

			//Delete invcartid; 
			foreach($itemListPayload as $items){
				foreach($items as $id){
					$invcartid = $id["invcartid"];
					(new Set())->useTransactionManager()->MboSetRemote("invcart")->where("invcartid=?", $invcartid)->remove();
				}
				
			}
			$tm->commit();
        	Super::sendJsonResponse("success", "PR has been created successfully.");

		} catch (Exception $e) {
			// Jika ada error di manapun, semua akan di-rollback
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
			
		public static function generatePRNum(){
			$getAutoNum = new Super();
			$prnum = $getAutoNum->getAutoNumber("pr", "update_next_value")->autonumber;
			Set::throws(["new_prnum"=>$prnum]);
		}
		
		public static function addToCartRecomorder(){
			$payload = Super::getParam("addToCartRecomorder");
			$itemnum = $payload["itemnum"];
			$quantity = $payload["recomendedOrderQty"];
			
			$whereClause = "itemnum='$itemnum'";
			$set = new Set();
			$conditiontype = $set->MboSetRemote("itemcondition")->setWhere("$whereClause and conditionrate='100'")->MboRemote("conditiontype")->getMboSet()->getMbo()->getString("conditiontype");
			$set = new Set();
			$unitcost = $set->MboSetRemote("invfifolifocost")->setWhere($whereClause)->MboRemote("max(unitcost) as unitcost")->getMboSet()->getMbo()->getString("unitcost");
			$linecost = floatval($quantity)*floatval($unitcost);
			$set = new Set();
			$orderunit = $set->MboSetRemote("item")->setWhere($whereClause)->MboRemote("orderunit")->getMboSet()->getMbo()->getString("orderunit");
			
			$remarks = "Recommended Order from Maintenance Forecasting";
			
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			
			
			$set = new Set();
			$set->MboSetRemote("invcart")
			->setValue("itemnum", $itemnum)
			->setValue("remarks", $remarks)
			->setValue("quantity", $quantity)
			->setValue("conditiontype", $conditiontype)
			->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)
			->setValue("orderunit", $orderunit)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("replenishfrom", "RECOM-ORDER")
			->add();
			
			Set::throws(["status"=>"success", "message"=>"Item has been added to cart"]);
			
		}
		public static function addtoCart() {
			$payload = Super::getParam("addtoCart");
			$itemnum = $payload["itemnum"] ?? null;
			$warehouse = $payload["warehouse"] ?? null; 
			$remarks = $payload["remarks"] ?? null; 
			$quantity = floatval($payload["orderqty"]) ?? 0; 

			if(empty($itemnum)) Super::sendJsonResponse("error", "Item Number is required.");

			$whereClause = "itemnum='$itemnum'";
			
			$itemCondMbo = (new Set())->MboSetRemote("itemcondition")->setWhere("$whereClause AND conditionrate='100'")->getMboSet()->getMbo();
			$conditiontype = $itemCondMbo ? $itemCondMbo->getString("conditiontype") : null;
			
			$unitCostMbo = (new Set())->MboSetRemote("invfifolifocost")->setWhere($whereClause)->MboRemote("MAX(unitcost) as unitcost")->getMboSet()->getMbo();
			$unitcost = $unitCostMbo ? floatval($unitCostMbo->getString("unitcost")) : 0;
			
			$itemMbo = (new Set())->MboSetRemote("item")->setWhere($whereClause)->getMboSet()->getMbo();
			$orderunit = $itemMbo ? $itemMbo->getString("orderunit") : null;
			
			$linecost = $quantity * $unitcost;
			$gldebitacct = $warehouse ? Xbo::getwhgl($warehouse, "noprint") : null; // Pastikan warehouse ada
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			
			$setCart = new Set();
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
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("replenishfrom", "ABC_ANALYSIS") 
				->add();
			
			Set::throws(["status"=>"success", "message"=>"Item {$itemnum} with quantity {$quantity} has been added to the cart."]);
		}
		public static function disassembleItem() {
			$itemnum = Super::getParam("disassembleItem");
			$towarehouse = Super::getParam("towarehouse");
			$toracknum = Super::getParam("toracknum");
			$dissqty = Super::getParam("dissqty");

			$itemnum = htmlspecialchars($itemnum);
			//Assemble To Warehouse , To Racknum
			$towarehouse = htmlspecialchars($towarehouse);
			$toracknum = htmlspecialchars($toracknum);
		
			$msg = [];
			if (is_array($dissqty)) {
				// Which Item to Disassemble
				foreach ($dissqty as $item) {
					$itemnum = $item["itemnum"] ?? null;  // Tambahkan operator null coalescing
					//$dissasemble_qty = $item["quantity"] ?? null;
					$dissasemble_qty = $item["dissassmbleqty"] ?? null;
					$from_warehouse = $item["warehouse"] ?? null;
					$from_racknum = $item["racknum"] ?? null;
					$invfifolifocostid = $item["invfifolifocostid"] ?? null;
					$msg = self::disassembleItem2($itemnum, $towarehouse, $toracknum, $dissasemble_qty, $from_warehouse, $from_racknum, $invfifolifocostid); 

				}
				
			} else {
				$msg [] = [
					"status" => "error",
					"message" => "No disassemble quantities found."
				];
			}

	
			Super::sendJsonResponse($msg[0]["status"], $msg[0]["message"]);
		}
		public static function disassembleItem2($itemnum, $towarehouse, $toracknum, $dissasemble_qty, $from_warehouse, $from_racknum, $invfifolifocostid){ //Tambahkan invfifolifocostid

			$wc = "itemnum='$itemnum'";
			$subitemnums =  [];
			$financialperiod = Xbo::getFinancialPeriod();

			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			$transtype = "DISASSEMBLE";

			$msg = [];
			
			$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
			if ($itemSet->isEmpty()) {
				$msg [] = [
					"status" => "error",
					"message" => "Item Master not found."
				];
				return $msg;
			}
			$item = $itemSet->getMbo();
			$orgcode = $item->getString("orgcode");
			$sitecode = $item->getString("sitecode");

			//get Condition Type and Condition Rate
			
			$itemconditionSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->where("itemnum=? and conditionrate=?", $itemnum, 100)
			->MboRemote('conditiontype')->MboRemote('conditionrate')->getMboSet();
			if($itemconditionSet->isEmpty()){
				$conditiontype = null;
				$conditionrate = null;
			}else{
				$itemcondition = $itemconditionSet->getMbo();
				$conditiontype = $itemcondition->getString("conditiontype");
				$conditionrate = $itemcondition->getString("conditionrate");
			}
			
			//Disassemble Item Structure
			$itemStruct = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->where("itemnum=?", $itemnum)->MboRemote("subitemnum")->MboRemote("quantity")->MboRemote("unitcost")->getMboSet();
			$counts = $itemStruct->counts();
			for($i=0; $i<$counts; $i++){
				$item = $itemStruct->getMbo();
				$subitemnum = $item->getString("subitemnum"); 
				$quantity = floatval($item->getString("quantity"));
				$unitcost = floatval($item->getString("unitcost"));
				$total_dissQty = $quantity * $dissasemble_qty;

				//1. check sub item Inventory
			
				$ivSet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->where("itemnum=? AND warehouse=?", $subitemnum, $towarehouse)->getMboSet(); 
				if($ivSet->isEmpty()){
					(new Set())->useTransactionManager()->MboSetRemote("inventory")
						->setValue("itemnum", $subitemnum) // Perbaikan: Gunakan subitemnum
						->setValue("warehouse", $towarehouse)
						->setValue("consignment",'N')
						->setValue("racknum",$toracknum)
						->setValue("reorderqty", "1")
						->setValue("leadtime",'1')
						->setValue("eoqqty",'1')
						->setValue("issuetype",'FIFO')
						->setValue("safetystock",'1')
						->setValue("reorder",'Y')
						->setValue("integration",'NONE')
						->setValue("orgcode",$orgcode)
						->setValue("sitecode",$sitecode)
						->add();
				}else{
					
				}

				//store it into invfifolifocost
		
				//cek data invfifolifocost nya.
				if(empty($racknum)){
					$rackval = "and racknum is null";
				}else{
					$rackval = "and racknum='$toracknum'";
				}
				$wc_subitemcomponent = "itemnum = '$subitemnum' and warehouse = '$towarehouse' $rackval";
				
				$invfifolifocostCheckSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
				->setWhere($wc_subitemcomponent)
				->MboRemote("quantity")->getMboSet();

				//Jika data kosong. maka lakukan inisiasi
				if($invfifolifocostCheckSet->isEmpty()){
					$batchnum = Xbo::GetBatchNum();
					(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
						->setValue("itemnum", $subitemnum) // Perbaikan: Gunakan subitemnum
						->setValue("warehouse", $towarehouse)
						->setValue("racknum", $toracknum)
						->setValue("conditiontype", $conditiontype)
						->setValue("conditionrate", $conditionrate)
						->setValue("quantity", $total_dissQty)
						->setValue("unitcost", $unitcost)
						->setValue("costdate", $enterdate)
						->setValue("transtype", $transtype)
						->setValue("transdate", $enterdate)
						->setValue("orgcode", $orgcode)
						->setValue("sitecode", $sitecode)
						->setValue("batchnum", $batchnum)
						->setValue("financialperiod", $financialperiod)
						->add();
				}else{
					$ifl = $invfifolifocostCheckSet->getMbo();
					$old_quantity = floatval($ifl->getString("quantity"));
					$new_quantity = $old_quantity+$total_dissQty;
					(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wc_subitemcomponent)->setValue("quantity", $new_quantity)->change();
				}

			}

			//Kurangi quantity item induk
			$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=? ", $invfifolifocostid)->MboRemote("quantity")->getMboSet();
			
			//Cek apakah data ada
			if(!$invfifolifocostSet->isEmpty()){

				$invfifolifocost= $invfifolifocostSet->getMbo();
				$old_quantity = $invfifolifocost->getString("quantity");

				// pastikan, qty disassemble tidak melebihi quantity item induk
				if($dissasemble_qty > $old_quantity){
				
					$msg [] = [
						"status" => "error",
						"message" => "The disassembly quantity exceeds the stock quantity."
					];

					return $msg;
				}
				$new_quantity = $old_quantity-$dissasemble_qty;

				(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=? ", $invfifolifocostid)->setValue("quantity", $new_quantity)->change();
			}
			else{
				$msg [] = [
					"status" => "error",
					"message" => "Item not found"
				];

				return $msg;
			}
			
			$msg [] = [
				"status" => "success",
				"message" => "Item has been disassembled."
			];
			return $msg;
		}
		
		public static function assembleItems(){
			
			$assembleItems = Super::getParam("assembleItems");
			$itemnum = Super::getParam("itemnum");
			$to_warehouse = Super::getParam("warehouse"); // tempat dimana hasil rakitan akan disimpan
			$gldebitacct = Xbo::getwhgl($to_warehouse, "noprint");
			$transtype = "ASSEMBLE";
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			$financialperiod = Xbo::getFinancialPeriod();

			$itemCatalog = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->getMboSet()->getMbo();
			$main_orgcode = $itemCatalog->getString("orgcode");
			$main_sitecode = $itemCatalog->getString("sitecode");

			if(empty($main_orgcode) || empty($main_sitecode)){
				$orginfo = Xbo::getOrgcode($enterby);
				$main_orgcode = $orginfo->orgcode;
				$main_sitecode = $orginfo->sitecode;
			} 

			if ($assembleItems === null && json_last_error() !== JSON_ERROR_NONE) {
				$msg = 'Error decoding JSON: ' . json_last_error_msg();
				Super::sendJsonResponse("error", $msg);
			}

			
			// Loop melalui array assembleItems dan akses nilai-nilainya
			$subitemnum_totalcost = 0; 
			foreach ($assembleItems as $item) {
				$subitemnum = $item['itemnum'];
				$requiredQty = $item['requiredQty'];
				$totalAssembledQty = $item['totalAssembledQty'];

				//echo "Item: $itemnum, Required Qty: $requiredQty, Total Assembled Qty: $totalAssembledQty<br>";

				// Loop melalui array takenFrom
				//hitung total cost dari assembled item (itemnum) 
				
				
				foreach ($item['takenFrom'] as $takenFrom) {
					$warehouse = $takenFrom['warehouse'];
					$racknum = $takenFrom['racknum'];
					$takenQty = $takenFrom['takenQty'];
					$takenQty = floatval($takenQty);
					$invfifolifocostid = $takenFrom['invfifolifocostid'];
					
					$whereClause = "invfifolifocostid='$invfifolifocostid'";
				
					//get value of item structure from invfifolifocost
					$iflSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
					->where("invfifolifocostid=?", $invfifolifocostid)
					->MboRemote("quantity")->MboRemote("unitcost")->MboRemote("conditiontype")
					->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("wonum")->MboRemote("ponum")
					->MboRemote("expdate")->MboRemote("costdate")->MboRemote("batchnum")->MboRemote("warehouse")
					->getMboSet();
					
					if($iflSet->isEmpty()){
						//item structure does not exist
						Super::sendJsonResponse("error", "Item Structure does not exist");
					}else{
						$ifl = $iflSet->getMbo(); 
						$unitcost = floatval($ifl->getString("unitcost"));
						$subitemnum_totalcost = $subitemnum_totalcost+$unitcost;
						
						$conditiontype = $ifl->getString("conditiontype");
						$orgcode = $ifl->getString("orgcode");
						$sitecode = $ifl->getString("sitecode");
						$wonum = $ifl->getString("wonum");
						$ponum = $ifl->getString("ponum");
						$expdate = $ifl->getString("expdate");
						$costdate = $ifl->getString("costdate");
						$batchnum = $ifl->getString("batchnum");
						$from_warehouse =  $ifl->getString("warehouse"); //lokasi dimana item structure di simpan.
						$glcreditacct = Xbo::getwhgl($from_warehouse, "noprint");
						//kurangi stock saat ini
						$old_quantity = floatval($ifl->getString("quantity"));
						$requiredQty = floatval($requiredQty);
						$new_quantity = $old_quantity-$takenQty;
						$takenCost = $takenQty*$unitcost;
						
						(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=?", $invfifolifocostid)->setValue("quantity", $new_quantity)->change();
						
						//Masukkan ke dalam history
						
						$newCost = $new_quantity*$unitcost;
						$oldcost = $old_quantity*$unitcost;
						$variancecost = $oldcost-$takenCost;
						
						(new Set())->useTransactionManager()->MboSetRemote("invtrans")
						->setValue("itemnum", $subitemnum)
						->setValue("transtype", $transtype)
						->setValue("warehouse", $warehouse) 
						->setValue("racknum", $racknum)
						->setValue("conditiontype", $conditiontype)
						->setValue("quantity", $new_quantity)
						->setValue("oldquantity", $old_quantity)
						->setValue("cost", $newCost)
						->setValue("oldcost", $oldcost) 
						->setValue("orgcode", $orgcode) 
						->setValue("sitecode", $sitecode) 
						->setValue("enterby", $enterby) 
						->setValue("enterdate", $enterdate) 
						->setValue("financialperiod", $financialperiod)
						->setValue("ponum", $ponum) 
						->setValue("wonum", $wonum) 
						->setValue("costdate", $costdate) 
						->setValue("expdate", $expdate) 
						->setValue("gldebitacct", $gldebitacct) 
						->add();
					}
				}
			}
			
			//buat item yang sudah dirakit (item assemble)
			
			//1. Check availability in Inventory
		
			$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")
			->where("itemnum=? and warehouse=?", $itemnum, $to_warehouse)
			->MboRemote("racknum")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
			if($inventorySet->isEmpty()){
			
				(new Set())->useTransactionManager()->MboSetRemote("inventory")
				->setValue("leadtime", 0)
				->setValue("reorder", 'Y')
				->setValue("integration", 'NONE')
				->setValue("itemnum", $itemnum)
				->setValue("warehouse", $to_warehouse)
				->setValue("orgcode", $main_orgcode)
				->setValue("sitecode", $main_sitecode)
				->setValue("reorderqty", 0)  
				->setValue("eoqqty", 0)	  
				->setValue("issuetype", 'FIFO')  
				->setValue("safetystock", 0)				
				->add();


			}
			//RELOAD			
			$inventorySet = (new Set())->MboSetRemote("inventory")
			->where("itemnum=? and warehouse=?", $itemnum, $to_warehouse)
			->MboRemote("racknum")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
			
			$inventory = $inventorySet->getMbo();
			$racknum = $inventory->getString("racknum");
			$orgcode = $inventory->getString("orgcode");
			$sitecode = $inventory->getString("sitecode");

			//2. Check if Item has condition code;
			
			$itemconditionSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->where("itemnum=? and conditionrate=?", $itemnum, 100)
			->MboRemote('conditiontype')->MboRemote('conditionrate')->getMboSet();
			
			if($itemconditionSet->isEmpty()){
				$itemcondition = $itemconditionSet->getMbo();
				$conditiontype = null;
				$conditionrate = null;
			}else{
				$itemcondition = $itemconditionSet->getMbo();
				$conditiontype = $itemcondition->getString("conditiontype");
				$conditionrate = $itemcondition->getString("conditionrate");
			}
			//3. Add Assembled Item to inventory fifo lifo cost.

			
			$assembledQty = 1;
			$batchnum = Xbo::GetBatchNum();
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
				->setValue("itemnum", $itemnum)
				->setValue("warehouse", $to_warehouse)
				->setValue("racknum", $racknum)
				->setValue("conditiontype", $conditiontype)
				->setValue("conditionrate", $conditionrate)
				->setValue("quantity", $assembledQty)
				->setValue("unitcost", $subitemnum_totalcost)
				->setValue("costdate", $enterdate)
				->setValue("transtype", $transtype)
				->setValue("transdate", $enterdate)
				->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)
				->setValue("batchnum", $batchnum)
				->setValue("financialperiod", $financialperiod)
				->add();
			
			Super::sendJsonResponse("success", "Item $itemnum has been assembled and stored in $to_warehouse.");

		}
		
		
		public static function getMaxAssembleQty(){
		$itemnum = Super::getParam("getMaxAssembleQty");

		// 1. Dapatkan daftar struktur item (sub-item dan kuantitas yang dibutuhkan)
		$set = new Set();
		$itemStructSet = $set->MboSetRemote("itemstruct")
						->setWhere("itemnum='$itemnum'")
						->MboRemote("subitemnum")
						->MboRemote("quantity")
						->getMboSet();
		$counts = $itemStructSet->counts();

		$itemStructure = [];
		$subitemnums = [];
		for($i=0; $i<$counts; $i++){
			$is = $itemStructSet->getMbo();
			$subitemnum = $is->getString("subitemnum");
			$quantity = (float)$is->getString("quantity"); // Penting: Konversi ke float agar perhitungan benar
			$itemStructure[$subitemnum] = $quantity; // Gunakan associative array untuk memudahkan pencarian
			$subitemnums[] = $subitemnum;
		}

		// 2. Dapatkan ketersediaan sub-item di gudang (dari invfifolifocost)
		if (!empty($subitemnums)) {
			$whereClause = "invfifolifocost.itemnum='" . implode("' OR invfifolifocost.itemnum='", $subitemnums) . "'";
		} else {
			// Jika tidak ada sub-item, tidak ada yang bisa dirakit.
			Set::throws(["maxAssembleQty"=>0]); // Kembalikan 0 dengan Set::throws
			return;
		}

		$set = new Set();
		$invfifolifoSet = $set->MboSetRemote("invfifolifocost")
						->setWhere($whereClause)
						->MboRemote("itemnum")
						->MboRemote("quantity")
						->getMboSet();
		$counts = $invfifolifoSet->counts();

		$availableItemStructure = [];
		for($i=0; $i<$counts; $i++){
			$iv = $invfifolifoSet->getMbo();
			$itemnum = $iv->getString("itemnum");
			$quantity = (float)$iv->getString("quantity"); // Penting: Konversi ke float
			// Jika itemnum sudah ada, tambahkan kuantitasnya, jika tidak, buat baru
			if (isset($availableItemStructure[$itemnum])) {
				$availableItemStructure[$itemnum] += $quantity;
			} else {
				$availableItemStructure[$itemnum] = $quantity;
			}
		}

		// 3. Hitung jumlah maksimum yang dapat dirakit
		$maxAssembleQty = PHP_INT_MAX; // Inisialisasi dengan nilai maksimum

		foreach ($itemStructure as $subitemnum => $quantityNeeded) {
			$quantityAvailable = isset($availableItemStructure[$subitemnum]) ? $availableItemStructure[$subitemnum] : 0; // Handle jika sub-item tidak ada di gudang

			if ($quantityNeeded == 0) {
				// Handle kasus jika quantityNeeded = 0, untuk menghindari division by zero error
				$ratio = PHP_INT_MAX; // Atau nilai besar lainnya, karena tidak ada batasan
			} else {
				$ratio = $quantityAvailable / $quantityNeeded;
			}

			$maxAssembleQty = min($maxAssembleQty, $ratio);
		}

		// 4. Bulatkan ke bawah dan kembalikan hasilnya
		Set::throws(["maxAssembleQty"=>floor($maxAssembleQty)]); // Kembalikan dengan Set::throws
	}
		
	
	
	public static function listItemStruct(){
		$itemnum = Super::getParam("listItemStruct");
		$warehouse = Super::getParam("warehouse");
		
		$set = new Set();
		$itemStructSet = $set->MboSetRemote("itemstruct ist")
		->setRelationship("item i", "ist.subitemnum = i.itemnum")
		->setWhere("ist.itemnum='$itemnum'")->MboRemote("ist.subitemnum")->MboRemote("ist.quantity")->MboRemote("i.description")->getMboSet();
		$counts = $itemStructSet->counts();

		$subitemnums = [];
		for($i=0; $i<$counts; $i++){
			$is = $itemStructSet->getMbo();
			$subitemnum = $is->getString("subitemnum");
			$quantity = $is->getString("quantity");
			$description = $is->getString("description");

			$subitemnums[] = ["subitemnum"=>$subitemnum, "quantity"=>$quantity, "description"=>$description];
		}

		if (!empty($subitemnums)) {
			$itemnums = []; 
			foreach ($subitemnums as $item) {
				$itemnums[] = $item["subitemnum"]; 
			}
			$whereClause = "invfifolifocost.itemnum='" . implode("' OR invfifolifocost.itemnum='", $itemnums) . "'";
		} else {
			$whereClause = "1=0";
		}
		
		//get max_disassemble_qty
		$set = new Set();
		$iflSet = $set->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("sum(quantity) as max_disassemble_qty")->getMboSet();
		$ifl = $iflSet->getMbo();
		$max_disassemble_qty = $ifl->getString("max_disassemble_qty");
		
		//warehouse list
		$warehouselist = [];
		$set2 = new Set();
		$whSet = $set2->MboSetRemote("warehouse")->MboRemote("warehouse")->getMboSet();
		$countWh = $whSet->counts();
		if($countWh>0){
			for($i=0; $i<$countWh; $i++){
				$wh = $whSet->getMbo();
				$warehouse = $wh->getString("warehouse");
				$warehouselist[] = $warehouse;
				
			}
		}
		$response = [
			"whereclause" => $whereClause,
			"subitemnums" => $subitemnums,
			"warehouselist" => $warehouselist,
			"max_disassemble_qty" => $max_disassemble_qty
		];

		header('Content-Type: application/json'); // Penting untuk memberi tahu browser bahwa ini adalah JSON
		echo json_encode($response);
		exit(); // Penting untuk menghentikan eksekusi PHP setelah mengirimkan JSON
	}
	public static function requestitemnumber(){
    $requestitemnumber = Super::getParam("requestitemnumber");

    if (is_array($requestitemnumber)) {
        foreach ($requestitemnumber as $item) {
            $object = $item['object'];
            $objectkey = $item['objectkey'];
            $itemnum = $item['itemnum'];
            $rowindex = $item['rowindex'];

			$set = new Set();
			$objectSet = $set->MboSetRemote("itemrequest")->setWhere("object='$object' and objectkey='$objectkey'")->setValue("itemnum", $itemnum)->change();
			
			$set = new Set();
			$set->MboSetRemote($object)->setWhere($object."id = '$objectkey'")->setValue("itemnum", $itemnum)->change();
        }
			$msg = "success";
			$remarks="";
		} else {
			$msg="error";
			$remarks= "Request Item is not an array.";
		}
	
		Set::throws(["msg"=>$msg, "remarks"=>$remarks]);
	}
	public static function recomorder()
	{
		$startdate = $_POST["recomorder"];
		$monthsahead = $_POST["monthsahead"];
		$monthsahead = intval($monthsahead);
		$endDate = date('Y-m-d', strtotime($startdate . ' + ' . $monthsahead . ' months'));

		$set = new Set();
		$reservationSet = $set->MboSetRemote("WOPLANMATERIAL wop")
			->setRelationship("WORKORDER wo", "wo.WONUM = wop.WONUM")
			->setRelationship("ITEM i", "i.ITEMNUM = wop.ITEMNUM")
			->where("wo.TARGSTARTDATE BETWEEN ? AND ?", $startdate, $endDate) 
			->MboRemote(
				"wop.itemnum, " .
				"i.description AS itemdesc, " .
				"SUM(wop.QUANTITY) AS reservationQty, " .
				"i.issueunit, " .
				"wo.wonum, " .
				"wo.description AS wodesc, " .
				"wo.targstartdate"
			)
			->group("wop.itemnum, i.description, i.issueunit, wo.wonum, wo.description")
			->order("wop.itemnum")
			->getMboSet();

		// 3. Logika pemrosesan data di PHP tetap SAMA PERSIS, hanya sumber datanya yang beda
		$reservations = [];
		while ($row = $reservationSet->fetch()) {
			$itemnum = $row['itemnum']; 

			if (!isset($reservations[$itemnum])) {
				$reservations[$itemnum] = [
					'itemnum' => $itemnum,
					'itemdesc' => $row['itemdesc'],
					'quantity' => 0,
					'orderunit' => $row['issueunit'],
					'workorders' => [],
					'stockOnTheWay' => [
						'quantity' => 0,
						'po' => []
					],
					'currentbalance' => [
						'curbal' => 0,
						'avgcost' => 0
					]
				];
			}

			$reservations[$itemnum]['quantity'] += (int)$row['reservationQty'];
			
			$reservations[$itemnum]['workorders'][] = [
				'wonum' => $row['wonum'],
				'wodesc' => $row['wodesc'],
				'targstartdate' => $row['targstartdate'],
				'reservedqty' => round($row['reservationQty'], 2),
				'late' => 'Unknown',
				'lateDays' => 'N/A'
			];
		}

	
		// Bagian 2: Ambil Data PO (STOCKONTHEWAY) 


		$set = new Set();
		$poSet = $set->MboSetRemote("poline pl")
			->setRelationship("po", "pl.ponum = po.ponum")
			->setRelationship(
				"(SELECT ponum, polinenum, SUM(quantity) as receivedqty 
				  FROM matrectrans 
				  GROUP BY ponum, polinenum) as received", 
				"pl.ponum = received.ponum AND pl.polinenum = received.polinenum"
			)
			->where("po.STATUS = ? AND po.ORDERDATE BETWEEN ? AND ?", 'SENT', $startdate, $endDate) 
			->MboRemote(
				"pl.ponum, pl.polinenum, pl.itemnum, pl.orderqty, " .
				"po.orderdate, po.estdatearrival, " .
				"IFNULL(received.receivedqty, 0) as receivedqty" // Ambil hasil agregat
			)
			->getMboSet();

		// 3. Loop HANYA SEKALI, tanpa ada query lagi di dalamnya
		while ($row_po = $poSet->fetch()) {
			$itemnum_po = $row_po['itemnum'];
			$orderqty = (int)$row_po['orderqty'];
			$receivedqty = (int)$row_po['receivedqty']; 
			
			// Kalkulasi sisa kuantitas
			$remainingqty = $orderqty - $receivedqty;

			// Lanjutkan hanya jika ada sisa kuantitas
			if ($remainingqty <= 0) {
				continue;
			}

			// Jika item ini ada di daftar reservasi, update datanya
			if (isset($reservations[$itemnum_po])) {
				// Tambahkan total kuantitas yang sedang dalam perjalanan
				$reservations[$itemnum_po]['stockOnTheWay']['quantity'] += $remainingqty;
				
				// Tambahkan detail PO-nya
				$reservations[$itemnum_po]['stockOnTheWay']['po'][] = [
					'ponum' => $row_po['ponum'],
					'orderqty' => $orderqty,
					'remainingqty' => $remainingqty,
					'orderdate' => $row_po['orderdate'],
					'estdatearrival' => $row_po['estdatearrival']
				];
			}
		}
	
		// Bagian 3: Ambil Data Saldo (CURRENTBALANCE) 
	

		$itemnumsToFetch = array_keys($reservations);

		// Hanya jalankan query jika ada item yang perlu dicari
		if (!empty($itemnumsToFetch)) {

			// 2. Buat string yang aman untuk klausa IN
			$itemnumList = "'" . implode("', '", $itemnumsToFetch) . "'";

			$set = new Set();
			$balanceSet = $set->MboSetRemote("v_invbalance")
				->where("itemnum IN ($itemnumList)")
				->MboRemote("itemnum, curbal, avgcost")
				->getMboSet();
				
			while ($row_balance = $balanceSet->fetch()) {
				$itemnum_balance = $row_balance['itemnum'];
				
				// query ini PASTI hanya mengembalikan item yang ada di $reservations.
				$reservations[$itemnum_balance]['currentbalance']['curbal'] = (float)$row_balance['curbal'];
				$reservations[$itemnum_balance]['currentbalance']['avgcost'] = (float)$row_balance['avgcost'];
			}
		}

		
		// Bagian 4: Hitung Rekomendasi
	

		foreach ($reservations as &$res) {
			$requiredQty = $res['quantity'];
			$onTheWay = $res['stockOnTheWay']['quantity'];
			$curBalance = $res['currentbalance']['curbal'];

			$gap = $requiredQty - ($curBalance + $onTheWay);
			$res['recomendedOrderQty'] = $gap > 0 ? $gap : 0;

			if ($res['recomendedOrderQty'] > 0) {
				$res['recomendedJustification'] = "Forecasted demand exceeds available stock and incoming purchase orders.";
			} else {
				$res['recomendedJustification'] = "Available stock and incoming orders are sufficient to fulfill forecasted demand.";
			}
		}
		unset($res);

   
		// Bagian 4: Finalisasi dan Output - Evaluasi Keterlambatan 
		
		foreach ($reservations as &$res) {
			
		
			// LANGKAH 1: HITUNG CLOSEST ARRIVAL SEKALI SAJA UNTUK ITEM INI
		
			$closestArrival = null; // Inisialisasi untuk item ini
			
			if (isset($res['stockOnTheWay']['po']) && is_array($res['stockOnTheWay']['po'])) {
				foreach ($res['stockOnTheWay']['po'] as $po_item) {
					if (isset($po_item['estdatearrival']) && !empty(trim($po_item['estdatearrival']))) {
						try {
							// Ambil hanya bagian tanggal YYYY-MM-DD
							$arrivalDateString = substr(trim($po_item['estdatearrival']), 0, 10);
							if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $arrivalDateString)) {
								$arrivalDate = new DateTime($arrivalDateString);
								$arrivalDate->setTime(0, 0, 0); // Normalisasi waktu

								// Jika ini tanggal pertama, atau lebih awal dari yang sudah ada
								if ($closestArrival === null || $arrivalDate < $closestArrival) {
									$closestArrival = $arrivalDate;
								}
							}
						} catch (Exception $e) {
							// Abaikan tanggal yang formatnya salah, log error jika perlu
							error_log("Invalid estdatearrival format for PO {$po_item['ponum']}: " . $e->getMessage());
						}
					}
				}
			}

			// LANGKAH 2: SEKARANG LOOP WORK ORDER 
			
			foreach ($res['workorders'] as &$wo) {
				$wo['late'] = "Unknown"; // Default
				$wo['lateDays'] = "N/A"; // Default
				
				$targDate = null;
				if (isset($wo['targstartdate']) && !empty(trim($wo['targstartdate']))) {
					try {
						$targDateString = substr(trim($wo['targstartdate']), 0, 10);
						 if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $targDateString)) {
							$targDate = new DateTime($targDateString);
							$targDate->setTime(0, 0, 0); // Normalisasi waktu
						}
					} catch (Exception $e) {
						error_log("Invalid targstartdate format for WO {$wo['wonum']}: " . $e->getMessage());
						$targDate = null;
					}
				}
				
				// Cek apakah targDate dan closestArrival (yang sudah dihitung) valid
				if ($targDate && $closestArrival) {
					// Jika tanggal target LEBIH AWAL dari kedatangan -> TELAT
					if ($targDate < $closestArrival) {
						$interval = $closestArrival->diff($targDate);
						$wo['late'] = "Yes";
						$wo['lateDays'] = $interval->days;
					} else { // Jika tanggal target SAMA DENGAN atau LEBIH LAMBAT dari kedatangan -> TIDAK TELAT
						$wo['late'] = "No";
						$wo['lateDays'] = 0;
					}
				}
				// Jika salah satu tanggal tidak valid, biarkan nilainya "Unknown"
			}
			unset($wo); 
		}
		unset($res); // Good practice
		$reservations = array_values($reservations);
		header('Content-Type: application/json');
		echo json_encode($reservations);

	}

	
	public static function ChangeConditionType(){
		$itemnum = $_POST["itemnum"]; 
		$changerecs = $_POST["changeconditiontype"]; // informasi condition type yang akan dirubah
		$countRecords = count($_POST["changeconditiontype"]); // berapa record item yang akan dirubah
		$warehouse = null;
		$enterdate = Xbo::setEnterDate();

		for($i=0; $i<$countRecords; $i++){
		
			$invfifolifocostid = $changerecs[$i]["id"]; //id dari record pada tabel invfifolifocost 
			$toconditiontype = $changerecs[$i]["toconditiontype"]; //berubah ke condition type apa?
			$changeqty = $changerecs[$i]["changeqty"]; //berapa jumlah qty yang harus berubah condition type nya
			
		
			$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")
			->MboRemote("quantity")->MboRemote("warehouse")->MboRemote("batchnum")->MboRemote("expdate")->MboRemote("costdate")
			->MboRemote("racknum")->MboRemote("conditiontype")->MboRemote("conditionrate")
			->MboRemote("ponum")->MboRemote("polinenum")->MboRemote("wonum")->MboRemote("vendorcode")->MboRemote("financialperiod")
			->MboRemote("unitcost")->MboRemote("physqty")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
			$invfifolifocost = $invfifolifocostSet->getMbo();
			$currentqty = $invfifolifocost->getString("quantity");
			$warehouse =  $invfifolifocost->getString("warehouse");
			$toconditionrate = Xbo::getConditionRate($toconditiontype, "itemcondition", $itemnum, $warehouse);
			$racknum =  $invfifolifocost->getString("racknum");
			$expdate = $invfifolifocost->getString("expdate");
			$costdate = $invfifolifocost->getString("costdate");
			$batchnum = $invfifolifocost->getString("batchnum");
			$ponum =  $invfifolifocost->getString("ponum");
			$polinenum =  $invfifolifocost->getString("polinenum");
			$wonum =  $invfifolifocost->getString("wonum");
			$vendorcode =  $invfifolifocost->getString("vendorcode");
			$financialperiod =  $invfifolifocost->getString("financialperiod");
			$unitcost =  $invfifolifocost->getString("unitcost");
			$physqty =  $invfifolifocost->getString("physqty");
			$orgcode =  $invfifolifocost->getString("orgcode");
			$sitecode =  $invfifolifocost->getString("sitecode");
			$rackval = Xbo::RackWhereClause($racknum);
			$conditiontype = $invfifolifocost->getString("conditiontype");

			
			if($conditiontype == $toconditiontype){
				//same conditiontype
			}else{
				
				$newunitcost = $unitcost*$toconditionrate/100; //conversi nilai unit cost item senilai % condition rate 
				
				if($currentqty > 0){ // check current quantity dari item 
					if($currentqty > $changeqty){ //check apakah lebih besar dari jumlah qty yang akan diubah
						$newqty = $currentqty - $changeqty; //jika lebih besar, maka kurangi current qty dengan change qty
						(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")->setValue("quantity", $newqty)->change(); //ubah ke jumlah yang sudah dikurangi
						
						$setc = new Set();
						$wccondtype = "itemnum='$itemnum' and warehouse = '$warehouse' and conditiontype='$toconditiontype' $rackval";
						$invfifolifocostSet2 = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wccondtype)->MboRemote("quantity")->getMboSet();
						$cnts = $invfifolifocostSet2->counts();
						if($cnts == 0){
							$transtype = "SPLITCOND";
							(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wccondtype)
							->setValue("itemnum", $itemnum)
							->setValue("warehouse", $warehouse)
							->setValue("conditiontype", $toconditiontype)
							->setValue("conditionrate", $toconditionrate)
							->setValue("racknum", $racknum)
							->setValue("quantity", $changeqty)
							->setValue("batchnum", $batchnum)
							->setValue("costdate", $costdate)
							->setValue("expdate", $expdate)
							->setValue("ponum", $ponum)
							->setValue("polinenum", $polinenum)
							->setValue("wonum", $wonum)
							->setValue("vendorcode", $vendorcode)
							->setValue("financialperiod", $financialperiod)
							->setValue("unitcost", $newunitcost)
							->setValue("physqty", $physqty)
							->setValue("orgcode", $orgcode)
							->setValue("sitecode", $sitecode)
							->setValue("transtype", $transtype)
							->setValue("transdate", $enterdate)
							->setValue("enterdate", $enterdate)
							->add();
						}else{
							$invfifolifocost2 = $invfifolifocostSet2->getMbo();
							$currentqty = $invfifolifocost2->getString("quantity");
							$newqty = $currentqty + $changeqty;
							(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
							->setWhere($wccondtype)
							->setValue("quantity", $newqty)
							->change();
						
						}
					}
				}
			}
		}
		
		$ss = new stdClass();
		$ss->countRecords = $countRecords;
		$ss->itemnum = $itemnum;
		
		Set::throws($ss);
	}
	
	public static function NewBatchNum(){
		
		$itemnum = $_POST["itemnum"];
		$newbatchnum = $_POST["newbatchnum"];
		$id = $_POST["id"];
		
		$set = new Set();
		$oldbacthnum = $set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$id'")->MboRemote("batchnum")->getMboSet()->getMbo()->getString("batchnum");
		$tables = ["custbalance", "custinvtrans", "invfifolifocost", "matrectrans", "matsaletrans", "matusetrans"];
		
		for($i=0; $i<count($tables); $i++){
			$set = new Set();
			if($tables[$i]=="invfifolifocost") {
				$set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$id'")->setValue("batchnum", $newbatchnum)->change();
			}else{
				$set->MboSetRemote($tables[$i])->setWhere("itemnum='$itemnum' and batchnum='$oldbacthnum'")->setValue("batchnum", $newbatchnum)->change();
			}
		}
		
	}
	public static function QRCodeInfo(){
		$itemnum = $_POST["qrcodeinfo"];
		
		$s = new Set();
		$itemSet = $s->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->MboRemote("manufacturer")->MboRemote("spectext")->getMboSet();
		$item = $itemSet->getMbo();
		
		$itemdesc = $item->getString("description");
		$manufacturer = $item->getString("manufacturer");
		$spectext = $item->getString("spectext");
		
		$ss = new Set();
		$invVendorSet = $ss->MboSetRemote("invvendor")->setRelationship("vendor", "invvendor.vendorcode = vendor.vendorcode")
		->setWhere("invvendor.itemnum='$itemnum'")
		->MboRemote("invvendor.vendorcode")->MboRemote("vendor.name")
		->getMboSet();
		
		$cnt = $invVendorSet->counts();
		
		$rr =[];
		for($i=0; $i<$cnt; $i++){
			$invVendor = $invVendorSet->getMbo();
			$vendorcode = $invVendor->getString("vendorcode");
			$name = $invVendor->getString("name");
			$ssr = new stdClass();
			$ssr->vendorcode = $vendorcode;
			$ssr->name = $name;
			array_push($rr, $ssr);
		}
		
		
		Set::throws(["itemnum"=>$itemnum, "itemdesc"=>$itemdesc, "vendors"=>$rr, "manufacturer"=>$manufacturer, "spectext"=>$spectext]);
	}
	public static function SynFifoLifoAdditem(){
		$itemnum = $_POST["synfifolifoadditem"];
		$warehouse = $_POST["warehouse"];
		$conditiontype = $_POST["conditiontype"];
		$expdate = $_POST["expdate"];
		$condval = Xbo::CondTypeWhereClause($conditiontype);
		$racknum = $_POST["racknum"];
		$rackval = Xbo::RackWhereClause($racknum);
		$newcurbal = $_POST["curbal"];
		$costdate = Xbo::setEnterDate();
		$financialperiod = Xbo::getFinancialPeriod();
		$enterby = Xbo::getLoginUser();
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		
		$unitcost = Xbo::getAvgCost($itemnum, $warehouse, $conditiontype);
		$conditionrate = Xbo::getConditionRate($conditiontype, "itemcondition", $itemnum, $warehouse);
	
		
		$set = new Set();
		$ibSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("sum(curbal) as sumcurbal")->getMboSet();
		$ib = $ibSet->getMbo();
		$sumcurbal = $ib->getString("sumcurbal");		
		if(empty($sumcurbal)) $sumcurbal =0;
		$nextqty = $sumcurbal;
		$set = new Set();
		$iflSet = $set->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("sum(quantity) as sumqty")->getMboSet();
		
		$ifl = $iflSet->getMbo();
		$flsumqty = $ifl->getString("sumqty");
		
		$batchnum = rand(10, 99)."-".rand(100, 999)."-".rand(1000, 2000);
		$varianceqty = $nextqty-$flsumqty;
		
		
		if($nextqty >= $flsumqty){
			if($varianceqty == 0){
				$msg = "noadd";
			}
			else{
				$set = new Set();
				$set->MboSetRemote("invfifolifocost")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)->setValue("racknum", $racknum)
				->setValue("quantity", $varianceqty)->setValue("expdate", $expdate)->setValue("batchnum", $batchnum)->setValue("financialperiod", $financialperiod)
				->setValue("unitcost", $unitcost)->setValue("conditionrate", $conditionrate)->setValue("costdate", $costdate)->setValue("enterdate", $costdate)->setValue("transdate", $costdate)
				->setValue("transtype", 'ADDITEM')->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
			}
			$msg = "ok";
		}else{
			if($nextqty < $flsumqty){
				$msg = 'needadjustment';
			}
		}
		
		Set::throws(["msg"=>$msg]);
	}
	public static function MatuseReturnFIFOLIFO(){
		$postvalue = $_POST["matusereturnfifolifo"];
		$tbname = $_POST["tablename"];
		$cnt = count($postvalue);
		$msg = null;
		for($i=0; $i<$cnt; $i++){
			$fo = new stdClass();
			$fo->tablename = $tbname;
			$fo->datarecord = $postvalue[$i];
			$get = ActionSet::ProcessReturnFIFOLIFO($fo);
			$msg = $get->msg;
			if($msg != "returned"){
				break;
			}
		}
		if($msg == "returned"){
			Set::throws(["msg"=>$msg]);
		}else{
			Set::throws(["msg"=>$msg]); 
		}
	}
	public static function InsertOrUpdateINVENTORY($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		
		$set = new Set();
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$invSet = $set->MboSetRemote("inventory")->setWhere($wcinv)->MboRemote("inventoryid")->getMboSet();
	
		$invcnt = $invSet->counts();
		
		if($invcnt > 0){
		}else{
			$set = new Set();
			$set->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)
			->setValue("reorderqty", 1)->setValue("leadtime", 0)->setValue("eoqqty", 1)->setValue("safetystock", 0)->setValue("reorder", "Y")->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
		}
	}
	
	public static function InsertOrUpdateINVCOST($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$conditiontype = $ob->conditiontype;
		$conditionrate = $ob->conditionrate;
		$unitcost = $ob->unitcost;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		
		$glaccount = Xbo::getGLAccount($itemnum);
		$invcostadjacc = Xbo::getInvCostAdjAcct($towarehouse, $orgcode, $sitecode);
		$shrinkageacc = Xbo::getShrinkageAcct($towarehouse, $orgcode, $sitecode);
		
		$set = new Set();
		$invCostSet = $set->MboSetRemote("invcost")->setWhere("itemnum = '$itemnum' and warehouse = '$towarehouse' and conditiontype='$conditiontype'")->getMboSet();
		$cnt = $invCostSet->counts();
		if($cnt > 0){
		}else{
			$setic = new Set();
			$setic->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)
			->setValue("conditiontype", $conditiontype)->setValue("conditionrate", $conditionrate)
			->setValue("stdcost", $unitcost)->setValue("avgcost", $unitcost)->setValue("oldcost", $unitcost)
			->setValue("lastcost", $unitcost)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)
			->setValue("invcostadjacc", $invcostadjacc)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
		}
	}
	
	public static function InsertOrUpdateINVBALANCE($ob){ // obsoelete
		
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$conditiontype = $ob->conditiontype;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		$retqty = $ob->retqty;
		$costdate = $ob->costdate;
		$expdate = $ob->expdate;
		$batchnum = $ob->batchnum;
		$wonum = $ob->wonum;
		$financialperiod = $ob->financialperiod;
		$conditionrate = $ob->conditionrate;
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
			
		if(empty($toracknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$toracknum'";
					
		$set = new Set();
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$wcinvbal = $wcinv." $condval $rackval";
		
		$set =  new Set();
		
		if(!empty($costdate)){
			$whereclause = $wcinvbal. " and costdate='$costdate'";
			$set = new Set();
			$invbalSet = $set->MboSetRemote("invfifolifocost")->setWhere($whereclause)->MboRemote("quantity")->getMboSet();
			if($invbalSet->isEmpty()){
				$set = new Set();
				$invbalSet = $set->MboSetRemote("invfifolifocost")->setWhere($wcinvbal)->MboRemote("quantity")->getMboSet();
				$whereclause = $wcinvbal;
			}
		}
		
		
		
		$invbalcnt = $invbalSet->counts();
		if($invbalcnt >0){
			$invbal = $invbalSet->getMbo();
			$oldcurbal = floatval($invbal->getString("quantity"));
			$newcurbal = $oldcurbal+$retqty;
			$set3 = new Set();
			$set3->MboSetRemote("invfifolifocost")->setWhere($whereclause)->setValue("quantity", $newcurbal)->setValue("oldcurbal", $oldcurbal)->change();
		}else{
			$oldcurbal = 0;
			$newcurbal = $oldcurbal+$retqty;
			$set3 = new Set();
			$set->MboSetRemote("invfifolifocost")
			->setValue("itemnum", $itemnum)
			->setValue("conditiontype", $conditiontype)
			->setValue("costdate", $costdate)
			->setValue("expdate", $expdate)
			->setValue("batchnum", $batchnum)
			->setValue("wonum", $wonum)
			->setValue("financialperiod", $financialperiod)
			->setValue("unitcost", $unitcost)
			->setValue("quantity", $newcurbal)
			->setValue("physqty", $newcurbal)
			->setValue("conditionrate", $conditionrate)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("warehouse", $towarehouse)
			->setValue("racknum", $toracknum)
			->setValue("transtype", 'RETURN')
			->setValue("transdate", $transdate)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->add();
		}
	}
	
	public static function InsertOrUpdateINVFIFOLIFOCOST($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$conditiontype = $ob->conditiontype;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		$quantity = floatval($ob->quantity);
		$costdate = $ob->costdate;
		$financialperiod = $ob->financialperiod;
		$conditionrate = $ob->conditionrate;
		$unitcost = floatval($ob->unitcost);
		$batchnum = $ob->batchnum;
		$expdate = $ob->expdate;
		$retqty = floatval($ob->retqty);
		$refwo = $ob->wonum;

		if(isset($ob->fromwarehouse)){
			$fromwarehouse = $ob->fromwarehouse;
		}else{
			$fromwarehouse = $ob->warehouse;
		}
		
		if(isset($ob->fromracknum)){
			$fromracknum = $ob->fromracknum;
		}else{
			$fromracknum = $ob->racknum;
		}
		
		if(isset($ob->issuedate)){
			$costdate = $ob->issuedate;
		}else{
			$costdate = $ob->costdate;
		}
		
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
			
		if(empty($toracknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$toracknum'";
		
		if(empty($fromracknum)) $fromrackval = " and (racknum is null or racknum='')"; 
		else $fromrackval = " and racknum='$fromracknum'";
		

		
		
		$wccostdate = Super::whereDate("costdate", $costdate);
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$wcinv2 = "itemnum='$itemnum' and warehouse='$fromwarehouse'";
		$wcfifolifo = "$wcinv $condval $wccostdate $rackval";
		$wcfifolifopovendor = "$wcinv2 $condval $wccostdate ";
		
		
		if(empty($expdate)){
			$wcfifolifo = "$wcinv $condval $wccostdate $rackval ";
			$wcfifolifopovendor = "$wcinv2 $condval $wccostdate $fromrackval";
		}else{
			$wcexpdate = Super::whereDate("expdate", $expdate);
			$wcfifolifo = "$wcinv $condval $wccostdate $rackval $wcexpdate";
			$wcfifolifopovendor = "$wcinv2 $condval $wccostdate $fromrackval";
		}

		$iflSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifo)->MboRemote("quantity")->getMboSet();
		
		$iflcnt = $iflSet->counts();
		if($iflcnt > 0){
			$ifl = $iflSet->getMbo();
			$oldflqty = floatval($ifl->getString("quantity"));
			$newflqty = $oldflqty+$retqty;
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifo)->setValue("quantity", $newflqty)->change();
		}else{
			
			$povendorSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifopovendor)->MboRemote("vendorcode")->MboRemote("ponum")->MboRemote("wonum")->getMboSet();
			$countpoVendor = $povendorSet->counts();
			if($countpoVendor == 0){
				
				$wcfifolifopovendor = "$wcinv2 and batchnum='$batchnum'";
				$povendorSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifopovendor)->MboRemote("vendorcode")->MboRemote("ponum")->MboRemote("wonum")->getMboSet();
			}
			
			$povendor = $povendorSet->getMbo();
			$vendorcode = $povendorSet->getString("vendorcode");
			$ponum = $povendorSet->getString("ponum");
			$wonum = $povendorSet->getString("wonum");
			

			if(empty($wonum)) $wonum = $refwo;
			
			if($quantity < 0 ) $quantity = $quantity*-1;
			$quantity = $retqty;
			
			if(empty($costdate)) $costdate = Xbo::setEnterDate();
			
			(new Set())->useTransactionManager()
			->MboSetRemote("invfifolifocost")
			->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("costdate", $costdate)->setValue("expdate", $expdate)
			->setValue("batchnum", $batchnum)->setValue("financialperiod", $financialperiod)->setValue("unitcost", $unitcost)->setValue("quantity", $quantity)
			->setValue("conditionrate", $conditionrate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)
			->setValue("ponum", $ponum)->setValue("vendorcode", $vendorcode)->setValue("wonum", $wonum)->setValue("transtype", "RETURN")
			->add();
			
		}
	}
	public static function UpdateAllInventoryObject($ob){
		ActionSet::InsertOrUpdateINVENTORY($ob); 
		ActionSet::InsertOrUpdateINVCOST($ob);
		ActionSet::InsertOrUpdateINVFIFOLIFOCOST($ob);
		/*
		requires this information:
		
		$ob->itemnum 
		$ob->towarehouse;
		$ob->conditiontype;
		$ob->conditionrate;
		$ob->unitcost;
		$ob->orgcode;
		$ob->sitecode;
		$ob->toracknum;
		$ob->enterby;
		$ob->enterdate;
		$ob->retqty;
		$ob->quantity;
		$ob->costdate;
		$ob->financialperiod;
		$ob->batchnum;
		$ob->expdate;
		
		*/
	}
	public static function CheckRefundInvoice(){
		$itemnum = $_POST["itemnum"];
		$warehouse = $_POST["warehouse"];
		$conditiontype = $_POST["conditiontype"];
		$invoicenum = $_POST["checkrefundinvoice"];
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
		
		$msg = "itemnotexist";
		
		$set = new Set();
		$invoicetransSet = $set->MboSetRemote("invoicetrans")->setWhere("itemnum='$itemnum' and invoicenum='$invoicenum' $condval")->getMboSet();
		$cnt = $invoicetransSet->counts();
		if($cnt > 0){
			$msg = "itemexist";
		}else{
			$msg = "itemnotexist";
		}
		
		Set::throws(["msg" => $msg]);
	}
	public static function CreateInvoiceTransaction($ob){
		$set = new Set();
		$invoicenum = $ob->invoicenum;
		$itemnum = $ob->itemnum;
		$warehouse = $ob->warehouse;
		$customercode = $ob->customercode;
		$quantity = $ob->quantity;
		$description = $ob->description;
		$unitcost = $ob->unitcost;
		$unitprice = $ob->unitprice;
		$unitprofit = $ob->unitprofit;
		$unitpricediscount = $ob->unitpricediscount;
		$unitprofitdiscount = $ob->unitprofitdiscount;		
		
		$linecost = $ob->linecost;
		$lineprice = $ob->lineprice;
		$lineprofit = $ob->lineprofit;
		$linepricediscount = $ob->linepricediscount;
		$lineprofitdiscount = $ob->lineprofitdiscount;
		
		$conditiontype = $ob->conditiontype;
		$gldebitacct = $ob->gldebitacct3;
		$glcreditacct = $ob->glcreditacct;
		
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$changeby = $enterby;
		$changedate = $enterdate;
		$financialperiod = $ob->financialperiod;
		$transtype = "return-sales";
		
		$invoicedate = null;
		$costdate = null;
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
		
		if (empty($customercode)) $custval = " and (customercode is null or customercode='')";
		else $custval = " and customercode='$customercode'";
		
		$sonum = null;
		$wonum = null;
		$invoicedate = null;
		$invoicelinenum = null;
		$tax = null;
		$costdate = null;
		$linetype = "item";
		$exchangerate = 1;
		$taxcost = 0;
		$taxprice = 0;
		$consignment = 'N';
		$consignmenttype = null;
		$pricecode = null;
		$taxpricemin =0;
		
		if(!empty($invoicenum)){
			$set = new Set();
			$invoiceSet = $set->MboSetRemote("invoicetrans")->setWhere("invoicenum='$invoicenum' and itemnum='".$itemnum."' and warehouse = '".$warehouse."' $condval $custval")
			->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("invoicedate")->MboRemote("invoicelinenum")
			->MboRemote("sonum")->MboRemote("wonum")->MboRemote("invoicedate")->MboRemote("invoicelinenum")->MboRemote("tax")->MboRemote("costdate")
			->MboRemote("taxprice")->MboRemote("consignment")->MboRemote("consignmenttype")->MboRemote("customercode")
			->MboRemote("glcreditacct")->MboRemote("gldebitacct")
			->getMboSet();
			
			
			$invoice = $invoiceSet->getMbo();
			$customercode = $invoice->getString("customercode");
			$unitcost = $invoice->getString("unitcost");
			$unitprice = $invoice->getString("unitprice");
			$unitprofit = $unitprice-$unitcost;
			$unitpricediscount = $invoice->getString("unitpricediscount");
			$unitprofitdiscount = $unitpricediscount-$unitcost;
			
			$linecost = $unitcost * $quantity;
			$lineprice = $unitprice * $quantity;
			$lineprofit = $unitprofit * $quantity;
			$linepricediscount = $unitpricediscount * $quantity;
			$lineprofitdiscount = $unitprofitdiscount * $quantity;
			
			$sonum = $invoice->getString("sonum");
			$wonum = $invoice->getString("wonum");
			$invoicedate = $invoice->getString("invoicedate");
			$invoicelinenum = $invoice->getString("invoicelinenum");
			$tax = $invoice->getString("tax");
			$costdate = $invoice->getString("costdate");
			$taxprice =  $invoice->getString("taxprice");
			$taxpricemin = $taxprice*-1;
			//karena refund , maka glcredit gldebit di balik
			$gldebitacct = $invoice->getString("glcreditacct");
			$glcreditacct = $invoice->getString("gldebitacct");
			$consignment =  $invoice->getString("consignment");
			
			$lineprice = $lineprice*-1;
			$lineprofit = $lineprofit*-1;
			$linepricediscount = $linepricediscount*-1;
			$lineprofitdiscount = $lineprofitdiscount*-1;
		}
		
		$quantity = $quantity*-1;
		
		$set2 = new Set();
		$set2->MboSetRemote("invoicetrans")->setValue("invoicenum", $invoicenum)->setValue("sonum", $sonum)->setValue("wonum", $wonum)->setValue("costdate", $costdate)
		->setValue("customercode", $customercode)->setValue("linetype", $linetype)->setValue("invoicelinenum", $invoicelinenum)->setValue("itemnum", $itemnum)->setValue("description", $description)
		->setValue("exchangerate", $exchangerate)->setValue("quantity", $quantity)->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("taxprice", $taxpricemin)
		->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("taxcost", $taxcost)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
		->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
		->setValue("tax", $tax)->setValue("invoicedate", $invoicedate)->setValue("consignment", $consignment)->setValue("consignmenttype", $consignmenttype)
		->setValue("transtype", $transtype)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("pricecode", $pricecode)
		->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)->setValue("enterby", $enterby)
		->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)
		->add();
		
		$set2 = new Set();
		$invoicechargeSet = $set2->MboSetRemote("invoicetrans")->setWhere("invoicenum='$invoicenum' and linetype='tax'")->getMboSet();
		$cnt = $invoicechargeSet->counts();
		for($i=0; $i<$cnt; $i++){
			$invoice = $invoicechargeSet->getMbo();
			$invoicenum = $invoice->getString("invoicenum");
			$ponum = $invoice->getString("ponum");
			$sonum = $invoice->getString("sonum");
			$wonum = $invoice->getString("wonum");
			$customercode = $invoice->getString("customercode");
			$linetype = $invoice->getString("linetype");
			$invoicelinenum = $invoice->getString("invoicelinenum");
			$itemnum = $invoice->getString("itemnum");
			$description = $invoice->getString("description");
			$exchangerate = $invoice->getString("exchangerate");
			
			$quantity = $invoice->getString("quantity");
			$unitprice = $invoice->getString("unitprice");
			$lineprice = $invoice->getString("lineprice");
			$taxprice = $invoice->getString("taxprice");
			$unitcost = $invoice->getString("unitcost");
			$linecost = $invoice->getString("linecost");
			$taxcost = $invoice->getString("taxcost");
			$unitprofit = $invoice->getString("unitprofit");
			$lineprofit = $invoice->getString("lineprofit");
			$unitpricediscount = $invoice->getString("unitpricediscount");
			
			$linepricediscount = $invoice->getString("linepricediscount");
			$unitprofitdiscount = $invoice->getString("unitprofitdiscount");
			$lineprofitdiscount = $invoice->getString("lineprofitdiscount");
			$tax = $invoice->getString("tax");
			$invoicedate = $invoice->getString("invoicedate");
			$consignment = $invoice->getString("consignment");
			$consignmenttype = $invoice->getString("consignmenttype");
			$transtype = $invoice->getString("transtype");
			$conditiontype = $invoice->getString("conditiontype");
			$warehouse = $invoice->getString("warehouse");
			
			$pricecode = $invoice->getString("pricecode");
			$gldebitacct = $invoice->getString("gldebitacct");
			$glcreditacct = $invoice->getString("glcreditacct");
			$financialperiod = $invoice->getString("financialperiod");
			
			$quantity = $quantity*-1;
			$lineprice = $lineprice*-1;
			
			$setic = new Set();
			$setic = $setic->MboSetRemote("invoicetrans")->setValue("invoicenum", $invoicenum)->setValue("ponum", $ponum)->setValue("sonum", $sonum)
			->setValue("wonum", $wonum)->setValue("customercode", $customercode)->setValue("linetype", $linetype)->setValue("invoicelinenum", $invoicelinenum)
			->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("exchangerate", $exchangerate)->setValue("quantity", $quantity)
			->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("taxprice", $taxprice)->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)->setValue("taxcost", $taxcost)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
			->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
			->setValue("tax", $tax)->setValue("invoicedate", $invoicedate)->setValue("consignment", $consignment)->setValue("consignmenttype", $consignmenttype)
			->setValue("transtype", $transtype)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("pricecode", $pricecode)
			->setValue("gldebitacct", $glcreditacct)->setValue("glcreditacct", $gldebitacct)->setValue("financialperiod", $financialperiod)
			->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
			->setValue("changeby", $changeby)->setValue("changedate", $changedate)
			->add();
		}
	}
	public static function mapReconcileBalance(){
		$invfifolifocostid = $_POST["mapReconcileBalance"];
		
	}
	public static function mapPhysCountQty(){
		$invfifolifocostid = $_POST["mapPhysCountQty"];
		
		$financialperiod = $_POST["financialperiod"];
		$physcountdate = $_POST["physcountdate"];
		$physqty = $_POST["physqty"];
		
		$set = new Set();
		$msg = "recordnotfound";
		$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid= '$invfifolifocostid'")->getMboSet();
		$cnt = $invfifolifocostSet->counts();
		if($cnt==0){
			$msg = "recordnotfound";
		}else{
			$msg = "ok";
			$set = new Set();
			$set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid= '$invfifolifocostid'")
			->setValue("physqty", $physqty)
			->setValue("physcountdate", $physcountdate)
			->setValue("financialperiod", $financialperiod)
			->change();
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		$set::throws($ss);
		
	}
	public static function mapreconcileCurrentQty(){
		$itemnum = $_POST["mapreconcileCurrentQty"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$expdate = $_POST["expdate"];
		$costdate = $_POST["costdate"];
		$batchnum = $_POST["batchnum"];
		$currentQty = $_POST["currentQty"];
		$physqty = $_POST["physqty"];
		$physcountdate = $_POST["physcountdate"];
		$financialperiod = $_POST["financialperiod"];
		$invfifolifocostid = $_POST["invfifolifocostid"];
		$changeby = $_POST["changeby"];
		$wc = "invfifolifocostid='$invfifolifocostid'";
		
		$set = new Set();
		$invflSet = $set->MboSetRemote("invfifolifocost")->setWhere($wc)->MboRemote("conditiontype")
		->MboRemote("unitcost")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("ponum")
		->getMboSet();
		
		$invfl = $invflSet->getMbo();
		$conditiontype = $invfl->getString("conditiontype");
		$unitcost = $invfl->getString("unitcost");
		$orgcode = $invfl->getString("orgcode");
		$sitecode = $invfl->getString("sitecode");
		$ponum = $invfl->getString("ponum");
		
		$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
		$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
				
		$newCurbal = $physqty;
		$oldCurbal = $currentQty;
		$rackval = Xbo::RackWhereClause($racknum);
		$wccostdate = Super::whereDate("costdate", $costdate);
		$wcexpdate = Super::whereDate("expdate", $expdate);
		$itemwc = "itemnum = '$itemnum' and warehouse = '$warehouse' $wccostdate $rackval";
		$transtype = "RECBALADJ";
		$gldebitacct=null;
		$glcreditacct=null;
		$changedate = Xbo::setEnterDate();
		
		if ($newCurbal > $oldCurbal) {
			$gldebitacct = $controlacc;
			$glcreditacct = $shrinkageacc;
		}
		if ($newCurbal < $oldCurbal) {
			$gldebitacct = $shrinkageacc;
			$glcreditacct = $controlacc;
		}
		$oldcost = $oldCurbal*$unitcost;
		$newcost = $newCurbal*$unitcost;
		$qtyVariance = $newCurbal - $oldCurbal;
		$variancecost = $qtyVariance * $unitcost;
				
		$set2 = new Set();
		$set2->MboSetRemote("invfifolifocost")->setWhere($wc)->setValue("quantity", $newCurbal)->change();
		if($qtyVariance !=0){
			$set3 = new Set();
			$set3->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", $transtype)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)
			->setValue("conditiontype", $conditiontype)->setValue("oldquantity", $oldCurbal)->setValue("varianceqty", $qtyVariance)->setValue("quantity", $newCurbal)
			->setValue("oldcost", $oldcost)->setValue("cost", $newcost)->setValue("variancecost", $variancecost)->setValue("avgcost", $unitcost)->setValue("stdcost", $unitcost)
			->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $changeby)
			->setValue("enterdate", $changedate)->setValue("financialperiod", $financialperiod)->setValue("ponum", $ponum)->setValue("batchnum", $batchnum)
			->setValue("costdate", $costdate)->setValue("expdate", $expdate)
			->add();
		}
		
		$itemwc = "itemnum = '$itemnum' and warehouse = '$warehouse' $wcexpdate $rackval";
		$setdel = new Set();
		$setdel->MboSetRemote("invbalance")->setWhere($itemwc)->remove();
		$sets = new Set();
		$invQTYSet = $sets->MboSetRemote("invfifolifocost")->setWhere($wc)
		->group("itemnum, warehouse, racknum, expdate, conditiontype")
		->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("conditiontype")->MboRemote("sum(quantity) as totalqty")->MboRemote("expdate")->getMboSet("distinct");
		
		$invQTYCount = $invQTYSet->counts();

		for($j=0; $j<$invQTYCount; $j++){
			$invQTY = $invQTYSet->getMbo();
			$i_itemnum = $invQTY->getString("itemnum");
			$i_warehouse = $invQTY->getString("warehouse");
			$i_totalqty = $invQTY->getString("totalqty");
			$i_expdate = $invQTY->getString("expdate");
			$i_racknum = $invQTY->getString("racknum");
			$i_conditiontype = $invQTY->getString("conditiontype");
			$rackval = Xbo::RackWhereClause($i_racknum);
			$condval = Xbo::CondTypeWhereClause($i_conditiontype);
			
			$todaydate = Xbo::setEnterDate();
			$otodaydate = strtotime($todaydate);	
			
			if($expdate != null ) {
				$oexpdate = strtotime($expdate);
				$odate = $oexpdate - $otodaydate;
				if ($odate < 0) $isexpired = "Y";
				else $isexpired = "N";
			}else{
				$isexpired = "N";
			}	
			
			$wcexpdate = Super::whereDate("expdate", $i_expdate);
			$setc = new Set();
			$invbc=  $setc->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$i_warehouse' $rackval $condval $wcexpdate")->getMboSet();
			//array_push($itemselectwc, $invbc->sql);
			$cccnt = $invbc->counts();
			if($cccnt == 0){
				$setins = new Set();
				$setins->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $i_conditiontype)->setValue("warehouse", $i_warehouse)
				->setValue("racknum", $i_racknum)->setValue("curbal", $i_totalqty)->setValue("oldcurbal", $i_totalqty)->setValue("physcount", $i_totalqty)
				->setValue("physcountdate", $physcountdate)->setValue("expdate", $i_expdate)->setValue("isexpired", $isexpired)->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)->add();
			}
		}
		$msg = "updated";	
		$ss->msg = $msg;
		
		Set::throws($ss);
	}
	
	public static function updateInvReserveOnItemnum(){
		
		/*
		{
		  "updateInvReserveOnItemnum": "05-00003",
		  "warehouse": "WH-STR03",
		  "racknum": "FLOOR",
		  "conditiontype": "NEW",
		  "mrnum": "",
		  "wonum": "2024/WO-48",
		  "issuedqty": "3",
		  "invreservenum": "11933"
		}
		*/
		$ss = new stdClass;
		$itemnum = $_POST["updateInvReserveOnItemnum"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$expireddate = $_POST["expireddate"];
		$conditiontype = $_POST["conditiontype"];
		$mrnum = $_POST["mrnum"];
		$wonum = $_POST["wonum"];
		$issuedqty = $_POST["issuedqty"];
		$invreservenum = $_POST["invreservenum"];
		
		$rackval = Xbo::RackWhereClause($racknum);
		$condval = Xbo::CondTypeWhereClause($conditiontype);
		
		if (empty($wonum)) $woval = " and (wonum='' or wonum is null)";
		else $woval = " and wonum='$wonum'";
		
		if (empty($mrnum)) $mrval = " and (mrnum='' or mrnum is null)";
		else $mrval = " and mrnum='$mrnum'";
		
				
		$itemwc = "itemnum = '$itemnum' and warehouse = '$warehouse' and invreservenum='$invreservenum' $rackval $condval $woval $mrval";
		$set = new Set();
		
		$invresSet = $set->MboSetRemote("invreserve")->setWhere($itemwc)->MboRemote("quantity")->getMboSet();
		$invres = $invresSet->getMbo();
		$prevQty = $invres->getString("quantity");
		$newQty = $prevQty;
		$msg = "notupdated";
		if($prevQty>0){
			if($prevQty >= $issuedqty){
				$ss->prevQty = $prevQty;
				$newQty = $prevQty-$issuedqty;
				$ss->newQty = $newQty;
				$set = new Set();
				$set->MboSetRemote("invreserve")->setWhere($itemwc)->setValue("quantity", $newQty)->change();
				$msg = "updated";
			}
		}
		
		
		$ss->sqlQuery = $invresSet->sql;
		$ss->msg = $msg;
		$ss->issuedqty = $issuedqty;
		Set::throws($ss);
		
		
		
	}
	public static function mapUpdateFifoLifoPhysQty(){
		$financialperiod = $_POST["updateFifoLifoPhysQty"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$changedate = Xbo::setEnterDate();
		if(isset($_POST["changeby"])){
			$changeby = $_POST["changeby"];
		}else{
			$changeby = Xbo::getLoginUser();
		}
		$ss = new stdClass;
		$wc = "orgcode = '$orgcode' and sitecode='$sitecode' and financialperiod='$financialperiod' and physcountdate is not null";
		$set = new Set();
		$invflSet = $set->MboSetRemote("invfifolifocost")->setWhere($wc)->MboRemote("itemnum")->MboRemote("conditiontype")->MboRemote("racknum")
		->MboRemote("quantity")->MboRemote("physqty")->MboRemote("unitcost")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("warehouse")
		->MboRemote("ponum")->MboRemote("batchnum")->MboRemote("costdate")->MboRemote("expdate")->MboRemote("physcountdate")
		->getMboSet();
		$cnt = $invflSet->counts();
		$itemselectwc = [];
		$itemselectwc2 = [];
		if($cnt >0){
			//$sql = "truncate table invbalance";
			//DB::exec($sql);
			for($i=0; $i<$cnt; $i++){
				$invfl =$invflSet->getMbo();
				$warehouse = $invfl->getString("warehouse");
				$orgcode = $invfl->getString("orgcode");
				$sitecode = $invfl->getString("sitecode");
				$itemnum = $invfl->getString("itemnum");
				$physqty = $invfl->getString("physqty");
				$unitcost = $invfl->getString("unitcost");
				$oldCurbal = $invfl->getString("quantity");
				$conditiontype = $invfl->getString("conditiontype");
				$racknum = $invfl->getString("racknum");
				$ponum = $invfl->getString("ponum");
				$batchnum = $invfl->getString("batchnum");
				$costdate = $invfl->getString("costdate");
				$expdate = $invfl->getString("expdate");
				$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
				$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
				$physcountdate =  $invfl->getString("physcountdate");
				
				$newCurbal = $physqty;
				$oldcost = $oldCurbal*$unitcost;
				$newcost = $newCurbal*$unitcost;
				$qtyVariance = $newCurbal - $oldCurbal;
				$variancecost = $qtyVariance * $unitcost;
				
				$rackval = Xbo::RackWhereClause($racknum);
				$condval = Xbo::CondTypeWhereClause($conditiontype);
				$wcexpdate = Super::whereDate("expdate", $expdate);	
				
				$wccostdate = Super::whereDate("costdate", $costdate);
				
				$itemwc = "itemnum = '$itemnum' and warehouse = '$warehouse' $wccostdate $rackval";
				$set2 = new Set();
				$set2->MboSetRemote("invfifolifocost")->setWhere($itemwc." and " .$wc)->setValue("quantity", $newCurbal)->change();
					
				$transtype = "RECBALADJ";
				$gldebitacct=null;
				$glcreditacct=null;
				
				if ($newCurbal > $oldCurbal) {
					$gldebitacct = $controlacc;
					$glcreditacct = $shrinkageacc;
				}
				if ($newCurbal < $oldCurbal) {
					$gldebitacct = $shrinkageacc;
					$glcreditacct = $controlacc;
				}
				
				if($qtyVariance !=0){
					$set3 = new Set();
					$set3->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", $transtype)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)
					->setValue("conditiontype", $conditiontype)->setValue("oldquantity", $oldCurbal)->setValue("varianceqty", $qtyVariance)->setValue("quantity", $newCurbal)
					->setValue("oldcost", $oldcost)->setValue("cost", $newcost)->setValue("variancecost", $variancecost)->setValue("avgcost", $unitcost)->setValue("stdcost", $unitcost)
					->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $changeby)
					->setValue("enterdate", $changedate)->setValue("financialperiod", $financialperiod)->setValue("ponum", $ponum)->setValue("batchnum", $batchnum)
					->setValue("costdate", $costdate)->setValue("expdate", $expdate)
					->add();
				}
				
				
				$itemwc = "itemnum = '$itemnum' and warehouse = '$warehouse' $wcexpdate $rackval";
				$setdel = new Set();
				$setdel->MboSetRemote("invbalance")->setWhere($itemwc)->remove();
				$sets = new Set();
				$invQTYSet = $sets->MboSetRemote("invfifolifocost")->setWhere($itemwc.$condval." and " .$wc)
				->group("itemnum, warehouse, racknum, expdate, conditiontype")
				->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("conditiontype")->MboRemote("sum(quantity) as totalqty")->MboRemote("expdate")->getMboSet("distinct");
				
				$invQTYCount = $invQTYSet->counts();
		
				for($j=0; $j<$invQTYCount; $j++){
					$invQTY = $invQTYSet->getMbo();
					$i_itemnum = $invQTY->getString("itemnum");
					$i_warehouse = $invQTY->getString("warehouse");
					$i_totalqty = $invQTY->getString("totalqty");
					$i_expdate = $invQTY->getString("expdate");
					$i_racknum = $invQTY->getString("racknum");
					$i_conditiontype = $invQTY->getString("conditiontype");
					$rackval = Xbo::RackWhereClause($i_racknum);
					$condval = Xbo::CondTypeWhereClause($i_conditiontype);
					
					$todaydate = Xbo::setEnterDate();
					$otodaydate = strtotime($todaydate);	
					
					if($expdate != null ) {
						$oexpdate = strtotime($expdate);
						$odate = $oexpdate - $otodaydate;
						if ($odate < 0) $isexpired = "Y";
						else $isexpired = "N";
					}else{
						$isexpired = "N";
					}	
					
					$wcexpdate = Super::whereDate("expdate", $i_expdate);
					$setc = new Set();
					$invbc=  $setc->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$i_warehouse' $rackval $condval $wcexpdate")->getMboSet();
					//array_push($itemselectwc, $invbc->sql);
					$cccnt = $invbc->counts();
					if($cccnt == 0){
						$setins = new Set();
						$setins->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $i_conditiontype)->setValue("warehouse", $i_warehouse)
						->setValue("racknum", $i_racknum)->setValue("curbal", $i_totalqty)->setValue("oldcurbal", $i_totalqty)->setValue("physcount", $i_totalqty)
						->setValue("physcountdate", $physcountdate)->setValue("expdate", $i_expdate)->setValue("isexpired", $isexpired)->setValue("orgcode", $orgcode)
						->setValue("sitecode", $sitecode)->add();
					}
				}
			}
			//sini
			
			$msg = "updated";
		}else{
			$msg = "notupdated";
		}
		$ss->msg = $msg;
		$ss->countdata = $cnt;
		//$ss->itemselectwc = $itemselectwc;
		$ss->inventorySQL = $invflSet->sql;
		Set::throws($ss);
	}
	public static function mapProcessReturnFIFOLIFO(){
		$itemnum = $_POST["mapProcessReturnFIFOLIFO"];
		$batchnum = $_POST["batchnum"];
		$costdate = $_POST["costdate"];
		$tablename = $_POST["tablename"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$retqty = $_POST["retqty"];
		$remarks = $_POST["remarks"];
		$issuedate = $_POST["issuedate"];
		$towarehouse =  $_POST["towarehouse"];
		$toracknum =  $_POST["toracknum"];
		$wccostdate = Super::whereDate("costdate", $costdate);
		$set = new Set();
		$whereclause="itemnum='$itemnum' and batchnum='$batchnum'";
		
		$set2 = new Set();
		$matusereturnSet = $set->MboSetRemote("matusereturn")->setWhere($whereclause)->MboRemote("matusereturnid")->getMboSet();
		$matuse = $matusereturnSet->getMbo();
		$id = $matuse->getString("matusereturnid");
		if($id == null || $id == ""){
			$ss = new stdClass;
			$ss->msg = "issuerecordnotfound";
			$ss->whereclause = $whereclause. " ".$matusereturnSet->sql; 
			Set::throws($ss);
		}else{
			$fo = new stdClass;
			$fo->tablename = $_POST["tablename"];
			$fo->datarecord["warehouse"] = $towarehouse;
			$fo->datarecord["racknum"] = $toracknum;
			$fo->datarecord["retqty"] = $retqty;
			$fo->datarecord["remarks"] = $remarks;
			$fo->datarecord["issuedate"] = $issuedate;
			$fo->datarecord["matusereturnid"] = $id;
			$get = ActionSet::ProcessReturnFIFOLIFO($fo);
			Set::throws($get);
		}
		
	}
	public static function ProcessReturnFIFOLIFO($fo){
		$tablename = $fo->tablename;
		$dr = $fo->datarecord;
		$ob = new stdClass();
		$retvalObj = new stdClass();
		if($tablename == "matsalereturn") {
			$ob->towarehouse = $dr["towarehouse"];
			$ob->toracknum = $dr["toracknum"];
		}else{
			if($tablename == "matusereturn"){
				$ob->towarehouse = $dr["warehouse"];
				$ob->toracknum = $dr["racknum"];
			}
		}
		
		$ob->retqty = $dr["retqty"];
		if(isset($dr["refinvoicenum"])) {
			$ob->invoicenum = $dr["invoicenum"];
		}else{
			$ob->invoicenum = null;
		}
		
		if(isset($dr["remarks"])) {
			$ob->remarks = $dr["remarks"];
		}else{
			$ob->remarks = null;
		}
		
		
		if(isset($dr["issuedate"])) {
			$ob->issuedate = $dr["issuedate"];
		}else{
			$ob->issuedate = null;
			
		}
		
		
		
		if($tablename == "matsalereturn"){
			$set = new Set();
			$matsalereturnid = $dr["matsalereturnid"];
			$matsaletransSet = $set->MboSetRemote($tablename)->setWhere("matsalereturnid=$matsalereturnid")->getMboSet();
			
			$matsaletrans = $matsaletransSet->getMbo();
			
			$ob->itemnum = $matsaletrans->getString("itemnum");
			$ob->sonum = $matsaletrans->getString("sonum");
			$ob->solinenum = $matsaletrans->getString("solinenum");
			$ob->description = $matsaletrans->getString("description");
			$ob->linetype = $matsaletrans->getString("linetype");
			$ob->pricecode = $matsaletrans->getString("pricecode");
			$ob->warehouse = $matsaletrans->getString("warehouse");
			$ob->fromrack = $matsaletrans->getString("fromrack");
			$ob->conditiontype = $matsaletrans->getString("conditiontype");
			
			//RECALCULATE THE QUANTITY
			$setRet = new Set();
			$matuseretSet = $setRet->MboSetRemote("matsalereturn")->setWhere("itemnum='{$ob->itemnum}'")->MboRemote("sum(quantity) as quantity")->getMboSet();
			$matuseret = $matuseretSet->getMbo();
			$ob->quantity = $matuseret->getString("quantity");

			
			$ob->unitcost = $matsaletrans->getString("unitcost");
			$ob->linecost = $matsaletrans->getString("linecost");
			$ob->issuetype = $matsaletrans->getString("issuetype");
			$ob->costdate = $matsaletrans->getString("costdate");
			$ob->expdate = $matsaletrans->getString("expdate");
			$ob->batchnum = $matsaletrans->getString("batchnum");
			$ob->unitprice = $matsaletrans->getString("unitprice");
			$ob->lineprice = $matsaletrans->getString("lineprice");
			$ob->lineprofit = $matsaletrans->getString("lineprofit");
			$ob->unitprofit = $matsaletrans->getString("unitprofit");
			$ob->unitpricediscount = $matsaletrans->getString("unitpricediscount");
			$ob->linepricediscount = $matsaletrans->getString("linepricediscount");
			$ob->unitprofitdiscount = $matsaletrans->getString("unitprofitdiscount");
			$ob->lineprofitdiscount = $matsaletrans->getString("lineprofitdiscount");
			$ob->primarydiscount = $matsaletrans->getString("primarydiscount");
			$ob->secondarydiscount = $matsaletrans->getString("secondarydiscount");
			$ob->discountprice = $matsaletrans->getString("discountprice");
			$ob->gldebitacct = $matsaletrans->getString("gldebitacct");
			$ob->glcreditacct = $matsaletrans->getString("glcreditacct");
			$ob->costtype = $matsaletrans->getString("costtype");
			$ob->transtype = "RETURN";
			$ob->customercode = $matsaletrans->getString("customercode");
			$ob->invoiced = $matsaletrans->getString("invoiced");
			$ob->ispaid = $matsaletrans->getString("ispaid");
			$ob->isrefund = $matsaletrans->getString("isrefund");
			$ob->invoicelinenum = $matsaletrans->getString("invoicelinenum");
			$ob->orgcode = $matsaletrans->getString("orgcode");
			$ob->sitecode = $matsaletrans->getString("sitecode");
			$ob->transnum =  $matsaletrans->getString("transnum");
			$ob->salesman =  $matsaletrans->getString("salesman");
			$ob->paymenttype =  $matsaletrans->getString("paymenttype");
			$taxcode =  $matsaletrans->getString("taxcode");
			$taxvalue = ActionSet::getTaxValue($taxcode);
			
		}else{
			if($tablename == "matusereturn"){
				$matusereturnid = $dr["matusereturnid"];
				$set = new Set();
				$matusereturnSet = $set->MboSetRemote($tablename)->setWhere("matusereturnid=$matusereturnid")->getMboSet();
				$matusereturn = $matusereturnSet->getMbo();
				$ob->itemnum = $matusereturn->getString("itemnum");
				$ob->issueqty = $matusereturn->getString("issueqty");
				$ob->returnqty = $matusereturn->getString("returnqty");
				
				
				//RECALCULATE THE QUANTITY
				$setRet = new Set();
				$matuseretSet = $setRet->MboSetRemote("matusereturn")->setWhere("itemnum='{$ob->itemnum}'")->MboRemote("sum(quantity) as quantity")->getMboSet();
				$matuseret = $matuseretSet->getMbo();
				$ob->quantity = $matuseret->getString("quantity");
				
				
				$ob->unitcost = $matusereturn->getString("unitcost");
				$ob->linecost = $matusereturn->getString("linecost");
				$ob->issuetype = $matusereturn->getString("issuetype");
				$ob->batchnum = $matusereturn->getString("batchnum");
				$ob->expdate = $matusereturn->getString("expireddate");
				$ob->costdate = $matusereturn->getString("costdate");
				$ob->wonum = $matusereturn->getString("wonum");
				$ob->transtype = "RETURN";
				$ob->enterdate = $matusereturn->getString("enterdate");
				$ob->warehouse = $matusereturn->getString("warehouse");
				$ob->racknum = $matusereturn->getString("racknum");
				$ob->description = $matusereturn->getString("description");
				$ob->conditiontype = $matusereturn->getString("conditiontype");
				$ob->gldebitacct = $matusereturn->getString("gldebitacct");
				$ob->glcreditacct = $matusereturn->getString("glcreditacct");
				$ob->orgcode = $matusereturn->getString("orgcode");
				$ob->sitecode = $matusereturn->getString("sitecode");
				$ob->unitprice = $matusereturn->getString("unitprice");
				$ob->lineprice = $matusereturn->getString("lineprice");
				$ob->lineprofit = $matusereturn->getString("lineprofit");
				$ob->unitprofit = $matusereturn->getString("unitprofit");
				$ob->unitpricediscount = $matusereturn->getString("unitpricediscount");
				$ob->linepricediscount = $matusereturn->getString("linepricediscount");
				$ob->unitprofitdiscount = $matusereturn->getString("unitprofitdiscount");
				$ob->lineprofitdiscount = $matusereturn->getString("lineprofitdiscount");
				$ob->primarydiscount = $matusereturn->getString("primarydiscount");
				$ob->secondarydiscount = $matusereturn->getString("secondarydiscount");
				$ob->discountprice = $matusereturn->getString("discountprice");
				$ob->assetnum = $matusereturn->getString("assetnum");
				$ob->location = $matusereturn->getString("location");
				$ob->requestedby = $matusereturn->getString("requestedby");
			}
		}
		$ob->conditionrate = Xbo::getConditionRate($ob->conditiontype, "itemcondition", $ob->itemnum, $ob->warehouse);
		
		$ob->transdate = Xbo::setEnterDate();
		$ob->enterby = Xbo::getLoginUser();
		$ob->changeby = $ob->enterby;
		$ob->changedate = $ob->transdate;
		$ob->enterdate = $ob->transdate;
		$ob->financialperiod = Xbo::getFinancialPeriod();
		
		if($ob->quantity == null) $ob->quantity = 0;	
		if($ob->quantity <0 ){
			$ob->oldqty = $ob->quantity*-1;
		}else{
			$ob->oldqty = $ob->quantity;
		}
		if($ob->retqty >0){
			if($ob->retqty <= $ob->oldqty){
				if(empty($ob->towarehouse) || $ob->towarehouse == "''"){
					$msg = "warehouseisnull";
				}else{
					
					if (empty($ob->conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
					else $condval = " and conditiontype='".$ob->conditiontype."'";
					
					if(empty($ob->toracknum))$rackval = " and (racknum is null or racknum='')"; 
					else $rackval = " and racknum='".$ob->toracknum."'";
					
					ActionSet::UpdateAllInventoryObject($ob);
					
					if($tablename == "matsalereturn"){
						
						$unitpricemin = $ob->unitprice*-1;
						$linepricemin = $ob->retqty*$ob->unitprice*-1;
						$linepricediscountmin = $ob->retqty*$ob->unitpricediscount*-1;
						$taxprice = $taxvalue*$linepricediscountmin;
						
						$lineprofitmin = $ob->retqty*$ob->unitprofit*-1;
						$linecostmin = $ob->retqty*$ob->unitcost;
						
						$discountpricemin = ( $ob->discountprice / $ob->retqty )*-1;
						$lineprofitdiscountmin = $ob->retqty*$ob->unitprofit*-1;
						
						$setms = new Set();
						
						$setms->MboSetRemote("matsaletrans")->setValue("linetype", $ob->linetype)->setValue("itemnum", $ob->itemnum)->setValue("description", $ob->description)->setValue("pricecode", $ob->pricecode)
							->setValue("quantity", $ob->retqty)->setValue("unitprice", $ob->unitprice)->setValue("lineprice", $linepricemin)->setValue("linepricediscount", $linepricediscountmin)->setValue("costtype", $ob->costtype)
							->setValue("lineprofit", $lineprofitmin)->setValue("lineprofitdiscount", $lineprofitdiscountmin)->setValue("unitpricediscount", $ob->unitpricediscount)->setValue("unitprofitdiscount", $ob->unitprofitdiscount)
							->setValue("unitprofit", $ob->unitprofit)->setValue("conditiontype", $ob->conditiontype)->setValue("towarehouse", $ob->towarehouse)->setValue("torack", $ob->toracknum)->setValue("sonum", $ob->sonum)
							->setValue("solinenum", $ob->solinenum)->setValue("gldebitacct",$ob->glcreditacct )->setValue("glcreditacct", $ob->gldebitacct)->setValue("transdate", $ob->enterdate)
							->setValue("transtype", $ob->transtype)->setValue("batchnum", $ob->batchnum)->setValue("issuetype", $ob->issuetype)->setValue("customercode", $ob->customercode)
							->setValue("enterby", $ob->enterby)->setValue("enterdate", $ob->enterdate)->setValue("orgcode", $ob->orgcode)->setValue("sitecode", $ob->sitecode)->setValue("financialperiod", $ob->financialperiod)
							->setValue("remarks", $ob->remarks)->setValue("unitcost", $ob->unitcost)->setValue("linecost", $linecostmin)->setValue("costdate", $ob->costdate)
							->setValue("primarydiscount", $ob->primarydiscount)->setValue("secondarydiscount", $ob->secondarydiscount)->setValue("discountprice", $discountpricemin)
							->setValue("transnum", $ob->transnum)->setValue("salesman", $ob->salesman)->setValue("expdate", $ob->expdate)->setValue("paymenttype", $ob->paymenttype)
							->setValue("taxcode", $taxcode)->setValue("taxprice", $taxprice)
							->add(); 
							
						if($ob->invoicenum != null){	
							$ob->invoicenum = $ob->invoicenum;
							$ob->itemnum = $ob->itemnum; 
							$ob->description = $ob->description; 
							$ob->warehouse = $ob->towarehouse; 
							$ob->customercode = $ob->customercode; 
							$ob->quantity = $ob->retqty; 
							
							$ob->unitcost = $ob->unitcost; 
							$ob->unitprice = $ob->unitprice; 
							$ob->unitprofit = $ob->unitprofit; 
							$ob->unitpricediscount = $ob->unitpricediscount; 
							$ob->unitprofitdiscount = $ob->unitprofitdiscount; 
							
							$ob->linecost = $linecostmin; 
							$ob->lineprice = $linepricemin; 
							$ob->lineprofit = $lineprofitmin; 
							$ob->linepricediscount = $linepricediscountmin; 
							$ob->lineprofitdiscount = $lineprofitdiscountmin; 
							
							$ob->conditiontype = $ob->conditiontype; 
							$ob->gldebitacct = $ob->gldebitacct; 
							$ob->glcreditacct = $ob->glcreditacct; 
							
							$ob->enterby = $ob->enterby; 
							$ob->enterdate = $ob->enterdate; 
							$ob->financialperiod = $ob->financialperiod; 
							
							ActionSet::CreateInvoiceTransaction($ob);
						}	
					}else{
						if($tablename == "matusereturn"){
							if($ob->retqty < 0) $ob->retqty = $ob->retqty*-1;
							if($ob->linecost < 0) $ob->linecost = $ob->linecost*-1;
							$linepricemin = $ob->lineprice*-1;
							$discountpricemin = $ob->discountprice*-1;
							$linepricediscountmin = $ob->linepricediscount*-1;
							$lineprofitmin = $ob->lineprofit*-1;
							$lineprofitdiscountmin = $ob->lineprofitdiscount*-1;
							$set = new Set();
							$invbalanceSet = $set->MboSetRemote("invbalance")
							->setWhere("itemnum='".$ob->itemnum."' and warehouse='".$ob->warehouse."' $condval $rackval")->MboRemote("curbal")->getMboSet();
							$count = $invbalanceSet->counts();
							if($count>0){
								$invbal = $invbalanceSet->getMbo();
								$ob->oldcurbal = $invbal->getString("curbal");
							}else{
								$ob->oldcurbal = 0;
							}
							$ob->newcurbal = $ob->retqty+$ob->oldcurbal;
							$setmu = new Set();
							$setmu->MboSetRemote("matusetrans")->setValue("itemnum", $ob->itemnum)->setValue("description", $ob->description)->setValue("costdate", $ob->costdate)
							->setValue("quantity", $ob->retqty)->setValue("conditiontype", $ob->conditiontype)->setValue("warehouse", $ob->warehouse)->setValue("racknum", $ob->toracknum)
							->setValue("gldebitacct",$ob->glcreditacct )->setValue("glcreditacct", $ob->gldebitacct)->setValue("transtype", $ob->transtype)->setValue("expireddate", $ob->expdate)
							->setValue("enterby", $ob->enterby)->setValue("enterdate", $ob->enterdate)->setValue("changeby", $ob->enterby)->setValue("changedate", $ob->enterdate)
							->setValue("orgcode", $ob->orgcode)->setValue("sitecode", $ob->sitecode)->setValue("unitcost", $ob->unitcost)->setValue("issuetype", $ob->issuetype)
							->setValue("linecost", $ob->linecost)->setValue("wonum", $ob->wonum)->setValue("oldcurbal", $ob->oldcurbal)->setValue("newcurbal", $ob->newcurbal)->setValue("batchnum", $ob->batchnum)
							->setValue("unitprice", $ob->unitprice)->setValue("lineprice", $linepricemin)->setValue("unitprofit", $ob->unitprofit)->setValue("lineprofit", $lineprofitmin)
							->setValue("unitpricediscount", $ob->unitpricediscount)->setValue("linepricediscount", $linepricediscountmin)->setValue("unitprofitdiscount", $ob->unitprofitdiscount)
							->setValue("lineprofitdiscount", $lineprofitdiscountmin)->setValue("remarks", $ob->remarks)->setValue("actualdate", $ob->issuedate)
							->setValue("primarydiscount", $ob->primarydiscount)->setValue("secondarydiscount", $ob->secondarydiscount)->setValue("discountprice", $discountpricemin)
							->setValue("assetnum", $ob->assetnum)->setValue("location", $ob->location)->setValue("requestedby", $ob->requestedby)
							->add();
						}
					}
					
					
					$msg = "returned";
				}
			}else{
				if($ob->retqty > $ob->oldqty){
					$msg = "cannotReturnMoreThanQtySend";
					
				}
			}
		}else{
			if($ob->retqty <= 0 ){
				$msg = "cannotReturnLessThanZero";
			}
		}
		$retvalObj->msg = $msg;
		$retvalObj->return_quantity = $ob->retqty;
		$retvalObj->old_qty = $ob->oldqty;
		return $retvalObj;
	}
	public static function ReturnFIFOLIFO(){
		
		$postvalue = $_POST["returnfifolifo"];
		$tbname = $_POST["tablename"];
		$cnt = count($postvalue);
		$msg = null;
		for($i=0; $i<$cnt; $i++){
			$fo = new stdClass();
			$fo->tablename = $tbname;
			$fo->datarecord = $postvalue[$i];
			$get = ActionSet::ProcessReturnFIFOLIFO($fo);
			$msg = $get->msg;
			
			if($msg != "returned"){
				break;
			}
		}
		if($msg == "returned"){
			Set::throws(["msg"=>$msg]);
		}else{
			Set::throws(["msg"=>$msg]); 
		}
	}
	public static function ReconcileBalance(){
		$financialperiod = $_POST["reconcilebalance"];
		
		$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost ifl")->where("ifl.financialperiod=?", $financialperiod)
		->setRelationship("v_invbalance vib", "ifl.itemnum = vib.itemnum AND ifl.warehouse = vib.warehouse")
		->MboRemote("ifl.itemnum, ifl.physqty, ifl.physcountdate, ifl.quantity, ifl.warehouse, ifl.unitcost, ifl.conditiontype, ifl.racknum, ifl.financialperiod, 
		ifl.invfifolifocostid, ifl.orgcode, ifl.sitecode, ifl.quantity, ifl.batchnum, ifl.costdate, ifl.expdate, vib.avgcost")->getMboSet();
		
		$data = [];
		while($result = $invfifolifocostSet->fetch()){

			$data [] = [
				"itemnum" => $result["itemnum"],
				"warehouse" => $result["warehouse"],
				"racknum" => $result["racknum"],
				"conditiontype" => $result["conditiontype"],
				"physqty" => $result["physqty"],
				"quantity" => $result["quantity"],
				"physcountdate" => $result["physcountdate"],
				"orgcode" => $result["orgcode"],
				"sitecode" => $result["sitecode"],
				"avgcost" => $result["avgcost"],
				"unitcost" => $result["unitcost"],
				"invfifolifocostid" => $result["invfifolifocostid"],
				"financialperiod" => $result["financialperiod"],
				"batchnum" => $result["batchnum"],
				"costdate" => $result["costdate"], 
				"expdate" => $result["expdate"]
			];
			
		}

		XboSet::ReconcileBalanceAdjustment($data, $financialperiod);
		
		Set::throws(["msg"=>"ok", "data"=> $data]);
	}
	
	public static function AdjustPhysicalCountXL($v = null){
		$retvalObj = new stdClass();	
		$itemnum = $v->itemnum;
		$warehouse = $v->warehouse;
		$racknum = $v->racknum;
		$conditiontype = $v->conditiontype;
		$newphyscount = $v->physcount;
		$reorderqty = $v->reorderqty;
		$leadtime = $v->leadtime;
		$safetystock = $v->safetystock;
		$eoqqty = $v->eoqqty;
		$orgcode = $v->orgcode;
		$sitecode = $v->sitecode;
		$integration = $v->integration;
		$expdate = $v->expdate;
		
		$physdate = Xbo::setEnterDate();
		$financialperiod = Xbo::getFinancialPeriod();
		$enterby = Xbo::getLoginUser();
	
		$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
		$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
		
		$wc = "itemnum='$itemnum' and warehouse='$warehouse'"; 
		$set = new Set();
		$set->MboSetRemote("inventory")->setWhere($wc)->setValue("leadtime", $leadtime)->setValue("safetystock", $safetystock)
		->setValue("reorderqty", $reorderqty)->setValue("eoqqty", $eoqqty)->setValue("integration", $integration)->change();
		
		if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
		else $condval = " and conditiontype='$conditiontype'";
		
		if (empty($racknum)) $rackval = " and (racknum is null or racknum='') ";
		else $rackval = " and racknum = '$racknum' ";
		
		$s = new Set();
		$wccr = "$wc $condval $rackval";
		$invbalanceSet = $s->MboSetRemote("invbalance")->setWhere($wccr)->MboRemote("curbal")->getMboSet();
		$invbalance = $invbalanceSet->getMbo();
		$oldcurbal = $invbalance->getString("curbal");
		
		if ($newphyscount > $oldcurbal) {
				$gldebitacct = $controlacc;
				$glcreditacct = $shrinkageacc;
		}else{
			if ($newphyscount < $oldcurbal) {
				$gldebitacct = $shrinkageacc;
				$glcreditacct = $controlacc;
			}else{
				if ($newphyscount == $oldcurbal){
					$gldebitacct = $controlacc;
					$glcreditacct = $controlacc;
				}
			}
		}
		
		if($oldcurbal == $newphyscount) {}
		else{
			$st = new Set();
			$checkphyscountSet = $st->MboSetRemote("invbalance")->setWhere($wccr)->MboRemote("physcount")->getMboSet();
			$checkphyscount = $checkphyscountSet->getMbo();
			$oldphyscount = $checkphyscount->getString("physcount");
			
			if($oldphyscount == $newphyscount){
				$set = new Set();
				$set->MboSetRemote("invbalance")->setWhere($wccr)->setValue("physcount", $newphyscount)->setValue("physcountdate", $physdate)
				->setValue("expdate", $expdate)
				->change();
			}
			else{
				$set = new Set();
				$set->MboSetRemote("invbalance")->setWhere($wccr)->setValue("physcount", $newphyscount)->setValue("physcountdate", $physdate)
				->setValue("expdate", $expdate)
				->change();
				
				$set = new Set();
				$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval");
				$invcost = $invcostSet->MboRemote("stdcost")->MboRemote("avgcost")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
				$avgcost = $invcost->getString("avgcost");
				$stdcost = $invcost->getString("stdcost");
				
				$cost = $newphyscount*$avgcost;
				$oldcost = $oldcurbal*$avgcost;
				$varianceqty = $newphyscount-$oldcurbal;
				$variancecost  = $varianceqty*$avgcost;
				
				$set2 = new Set();
				if($oldcost != $cost){
					$set2->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", "PHYSCNTADJ")->setValue("warehouse", "$warehouse")->setValue("racknum", "$racknum")
					->setValue("conditiontype", "$conditiontype")->setValue("quantity", "$newphyscount")->setValue("oldquantity", "$oldcurbal")->setValue("cost", "$cost")
					->setValue("oldcost", "$oldcost")->setValue("varianceqty", "$varianceqty")->setValue("variancecost", "$variancecost")->setValue("gldebitacct", "$gldebitacct")
					->setValue("glcreditacct", "$glcreditacct")->setValue("orgcode", "$orgcode")->setValue("sitecode", "$sitecode")->setValue("enterby", "$enterby")->setValue("enterdate", "$physdate")
					->setValue("stdcost", "$stdcost")->setValue("avgcost", "$avgcost")->setValue("financialperiod", "$financialperiod")->add();
				}
			}
		}
		
		$retvalObj->msg = "ok";
		return $retvalObj;
	}
	public static function AdjustPhysicalCount(){
		$itemnum = $_POST["itemnum"];
		$warehouse = $_POST["warehouse"];
		$adjustphycount = $_POST["adjustphysicalcount"];
		$countphi = count($adjustphycount);
		$physdate = Xbo::setEnterDate();
		$financialperiod = Xbo::getFinancialPeriod();
		$enterby = Xbo::getLoginUser();
		$s = new Set();
		$inventorySet = $s->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$inventory = $inventorySet->getMbo();
		$orgcode = $inventory->getString("orgcode");
		$sitecode = $inventory->getString("sitecode");
		$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
		$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
		
		for($i=0; $i<$countphi; $i++){
			
			$v = $adjustphycount[$i];
			$invbalanceid = $v["invbalanceid"];
			$racknum = $v["racknum"];
			$newphyscount = $v["newphyscount"];
			
			$s = new Set();
			$invbalanceSet = $s->MboSetRemote("invbalance")->setWhere("invbalanceid=$invbalanceid")->MboRemote("curbal")->MboRemote("conditiontype")->getMboSet();
			$invbalance = $invbalanceSet->getMbo();
			$oldcurbal = $invbalance->getString("curbal");
			$conditiontype =  $invbalance->getString("conditiontype");
			if ($newphyscount > $oldcurbal) {
				$gldebitacct = $controlacc;
				$glcreditacct = $shrinkageacc;
			}else{
				if ($newphyscount < $oldcurbal) {
					$gldebitacct = $shrinkageacc;
					$glcreditacct = $controlacc;
				}else{
					if ($newphyscount == $oldcurbal){
						$gldebitacct = $controlacc;
						$glcreditacct = $controlacc;
					}
				}
			}
			
			if($oldcurbal == $newphyscount) {}
			else{
				$st = new Set();
				$checkphyscountSet = $st->MboSetRemote("invbalance")->setWhere("invbalanceid=$invbalanceid")->MboRemote("physcount")->getMboSet();
				$checkphyscount = $checkphyscountSet->getMbo();
				$oldphyscount = $checkphyscount->getString("physcount");
				
				if($oldphyscount == $newphyscount){
					$set = new Set();
					$set->MboSetRemote("invbalance")->setWhere("invbalanceid=$invbalanceid")->setValue("physcount", $newphyscount)->setValue("physcountdate", $physdate)->change();
				}
				else{
					$set = new Set();
					$set->MboSetRemote("invbalance")->setWhere("invbalanceid=$invbalanceid")->setValue("physcount", $newphyscount)->setValue("physcountdate", $physdate)->change();
					if (empty($conditiontype)) {
						$condval = " and (conditiontype='' or conditiontype is null)";
					}
					else {
						$condval = " and conditiontype='$conditiontype'";
					}
					$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval");
					$invcost = $invcostSet->MboRemote("stdcost")->MboRemote("avgcost")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
					$avgcost = $invcost->getString("avgcost");
					$stdcost = $invcost->getString("stdcost");
					
					$cost = $newphyscount*$avgcost;
					$oldcost = $oldcurbal*$avgcost;
					$varianceqty = $newphyscount-$oldcurbal;
					$variancecost  = $varianceqty*$avgcost;
					
					$set2 = new Set();
					if($oldcost != $cost){
						$set2->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", "PHYSCNTADJ")->setValue("warehouse", "$warehouse")->setValue("racknum", "$racknum")
						->setValue("conditiontype", "$conditiontype")->setValue("quantity", "$newphyscount")->setValue("oldquantity", "$oldcurbal")->setValue("cost", "$cost")
						->setValue("oldcost", "$oldcost")->setValue("varianceqty", "$varianceqty")->setValue("variancecost", "$variancecost")->setValue("gldebitacct", "$gldebitacct")
						->setValue("glcreditacct", "$glcreditacct")->setValue("orgcode", "$orgcode")->setValue("sitecode", "$sitecode")->setValue("enterby", "$enterby")->setValue("enterdate", "$physdate")
						->setValue("stdcost", "$stdcost")->setValue("avgcost", "$avgcost")->setValue("financialperiod", "$financialperiod")->add();
					}
				}
			}
		}
		
		
		Set::throws(["msg"=>"ok"]);
	}
	public static function ChangeConditionRateCost2(){
		$itemnum = $_POST["changecondratecost2"];
		$warehouse = $_POST["warehouse"];
		$changestdcost = $_POST["stdcost"];
		$set = new Set();
		$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and conditionrate='100'")->MboRemote("stdcost")->MboRemote("avgcost")->getMboSet();
		$invcost = $invcostSet->getMbo();
		$stdcost = $invcost->getString("stdcost");
		$avgcost = $invcost->getString("avgcost");
		
		$conditionrate =  ($changestdcost/$stdcost)*100;
		$newstdcost = $stdcost*($conditionrate/100);
		$newavgcost = $avgcost*($conditionrate/100);
		
		Set::throws(["conditionrate"=>$conditionrate, "stdcost"=>$newstdcost, "avgcost"=>$newavgcost]);
	}
	public static function ChangeConditionRateCost(){
		$itemnum = $_POST["changecondratecost"];
		$warehouse = $_POST["warehouse"];
		$conditionrate = $_POST["conditionrate"];
		$set = new Set();
		$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and conditionrate='100'")->MboRemote("stdcost")->MboRemote("avgcost")->getMboSet();
		$invcost = $invcostSet->getMbo();
		$stdcost = $invcost->getString("stdcost");
		$avgcost = $invcost->getString("avgcost");
		
		$newstdcost = $stdcost*($conditionrate/100);
		$newavgcost = $avgcost*($conditionrate/100);
		
		Set::throws(["conditionrate"=>$conditionrate, "stdcost"=>$newstdcost, "avgcost"=>$newavgcost]);
	}
	public static function GetItemConditionCost(){
		$itemnum = $_POST["getitemcondcost"];
		$warehouse = $_POST["warehouse"];
		$conditiontype = $_POST["conditiontype"];
		$set = new Set();
		$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and conditionrate='100'")->MboRemote("stdcost")->MboRemote("avgcost")->getMboSet();
		$invcost = $invcostSet->getMbo();
		$stdcost = $invcost->getString("stdcost");
		$avgcost = $invcost->getString("avgcost");
		
		$set2=  new Set();
		$conditionrate = $set2->MboSetRemote("conditiontype")->setWhere("conditiontype='$conditiontype'")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
		$conditionrate = $conditionrate;
		$newstdcost = $stdcost*($conditionrate/100);
		$newavgcost = $avgcost*($conditionrate/100);
		Set::throws(["conditionrate"=>$conditionrate, "stdcost"=>$newstdcost, "avgcost"=>$newavgcost]);
	}
	
	public static function CheckItemConditionEnabled(){
		$itemnum = $_POST["chkcondition"];
		$warehouse = $_POST["warehouse"];
		
		$set = new Set();
		$invbalanceSet = $set->MboSetRemote("invbalance")->setWhere("conditiontype is null and itemnum='$itemnum' and warehouse='$warehouse'")->getMboSet();
		$cntt = $invbalanceSet->counts();
		
		if($cntt >0){
			$msg= "notconditionenabled";
		}else{
			$msg = "conditionenabled";
		}
		Set::throws(["msg"=>$msg]);
	}
	public static function CheckItemConditionCost(){
		$itemnum = $_POST["checkitemcost"];
		$warehouse = $_POST["warehouse"];
		$conditiontype = $_POST["conditiontype"];
		
		$set = new Set();
		$invcost = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and conditiontype='$conditiontype'")->MboRemote("invcostid")->getMboSet();
		$count = $invcost->counts();
		if($count > 0){
			$msg= "ok";
		}else{
			$msg = "notexist";
		}
		
		Set::throws(["msg"=>$msg]);
	}
	public static function ChangeConversion(){
		$changeconversion = $_POST["changeconversion"];
		$itemnum = $_POST["itemnum"];
		$warehouse = $_POST["warehouse"];
		
		$set = new Set();
		$sumBalance = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse = '$warehouse' ")->MboRemote("sum(curbal) as sumcurbal")->getMboSet()->getMbo()->getString("sumcurbal");
		
		$conversionvalue = $sumBalance/$changeconversion;
		$round = round($conversionvalue);
		Set::throws(["conversionvalue"=>$round]);
		
	}
	public static function getConversionUnit($itemnum, $tomeasureunit, $issueunit){
		$itemval=" and itemnum='$itemnum'";
		$wc = "frommeasureunit='$tomeasureunit' and tomeasureunit='$issueunit' $itemval";
		$set2 = new Set();
		$conversion = $set2->MboSetRemote("conversion")->setWhere($wc)->MboRemote("conversion")->getMboSet()->getMbo()->getString("conversion");
		if($conversion == null){
			$conversion = null;
		}else{
			$conversion = $conversion;
		}
		
		
		if($conversion == null){
			$wc = "frommeasureunit='$tomeasureunit' and tomeasureunit='$issueunit'";
			$set22 = new Set();
			$conversion = $set22->MboSetRemote("conversion")->setWhere($wc)->MboRemote("conversion")->getMboSet()->getMbo()->getString("conversion");
		}
		
		if($conversion == null){
			$conversion = null;
		}else{
			$conversion = $conversion;
		}
		return $conversion;
	}
	public static function SyncMultiConversion($itemnum, $warehouse){
		
		
		$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->MboRemote("issueunit")->getMboSet();
		$item = $itemSet->getMbo();
		$issueunit = $itemSet->getString("issueunit");
		
		$invConversionSet = (new Set())->useTransactionManager()->MboSetRemote("invconversion")->where("itemnum=? and warehouse = ?", $itemnum, $warehouse)
		->MboRemote("tomeasureunit")->MboRemote("invconversionid")->getMboSet();
		
		while($result = $invConversionSet->fetch()){
			$tomeasureunit = $result["tomeasureunit"];
			$invconversionid = $result["invconversionid"];
			$conversion = ActionSet::getConversionUnit($itemnum, $tomeasureunit, $issueunit);
			$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("itemnum=? and warehouse = ?", $itemnum, $warehouse)->MboRemote("sum(quantity) as sumqty")->getMboSet();
			$invfifolifo = $invfifolifocostSet->getMbo();
			$sumqty = $invfifolifo->getString("sumqty");
			
			
			$curbalconversion = round(floatval($sumqty) * floatval($conversion));
			(new Set())->useTransactionManager()->MboSetRemote("invconversion")->where("invconversionid=?", $invconversionid)
			->setValue("conversion", $conversion)
			->setValue("curbalconversion", $curbalconversion)
			->change();
		}
	}
	public static function MultiConversion(){
		$countmsrunit = count( $_POST["multiconversions"]);
		$itemnum = $_POST['itemnum'];
		$warehouse = $_POST['warehouse'];
		for($i=0; $i<$countmsrunit; $i++){
			$set = new Set();
			$measureid = explode("`", $_POST["multiconversions"][$i])[1];
			$measureSet = $set->MboSetRemote("measureunit")->setWhere("measureunitid='$measureid'")->MboRemote("measureunitcode")->getMboSet();
			$measure = $measureSet->getMbo();
			$measureunitcode = $measure->getString("measureunitcode");
			
			$set2 = new Set();
			$checkset = $set2->MboSetRemote("invconversion")->setWhere("itemnum ='$itemnum' and warehouse = '$warehouse' and tomeasureunit='$measureunitcode'")->MboRemote("tomeasureunit")->getMboSet();
			$cnt = $checkset->counts();
			if($cnt == 0){
				$set2->MboSetRemote("invconversion")->setValue("tomeasureunit", $measureunitcode)->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->add();
			}
			
		}
		Set::throws(["msg"=>"added"]);
	}
	public static function RFIDUpdate(){
		
		$reader = $_POST["rfidupdate"];
		$antenna = $_POST["antenna"];
		$newsn = $_POST["sn"];
		$changeby = "MXSRFID";
		$changedate = Xbo::setEnterDate();
		
		//mapping
		$set = new Set();
		$rfidMapSet = $set->MboSetRemote("rfidmap")->setWhere("reader='$reader' and antenna='$antenna'")->MboRemote("warehouse")->MboRemote("racknum")
		->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$rfidMap = $rfidMapSet->getMbo();
		$warehouse = $rfidMapSet->getString("warehouse");
		$newracknum = $rfidMapSet->getString("racknum");
		$orgcode = $rfidMapSet->getString("orgcode");
		$sitecode = $rfidMapSet->getString("sitecode");
		//end mapping
		
		//get existing record
		$set2 = new Set();
		$invbalanceSet = $set2->MboSetRemote("invbalance")->setRelationship("item", "invbalance.itemnum = item.itemnum")
		->setWhere("invbalance.rfidsn='$newsn'")->MboRemote("invbalance.itemnum")->MboRemote("invbalance.curbal")->MboRemote("invbalance.oldcurbal")
		->MboRemote("rfidsn")->MboRemote("racknum")->MboRemote("item.description")->getMboSet();
		
		$countib = $invbalanceSet->counts();
		if($countib > 0){
			$invbalance = $invbalanceSet->getMbo();
			$itemnum = $invbalance->getString("itemnum");
			$itemdesc = $invbalance->getString("description");
			$oldsn = $invbalance->getString("rfidsn");
			$oldracknum = $invbalance->getString("racknum");
			$curbal = $invbalance->getString("curbal");
			$oldcurbal = $invbalance->getString("oldcurbal");
			Set::throws(["msg"=>"validate", "itemnum"=>$itemnum, "sn"=>$newsn, 
						"warehouse"=>$warehouse, "newracknum"=>$newracknum, "oldracknum"=>$oldracknum, "curbal"=>$curbal, 
						"itemdesc"=>$itemdesc, "changeby"=>$changeby, "changedate"=>$changedate
						]);
			$set3 = new Set();
			$set3->MboSetRemote("invbalance")->setWhere("itemnum = '$itemnum' and rfidsn='$oldsn' and racknum = '$oldracknum'")
			->setValue("racknum", $newracknum)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->change();
			
		}else{
			Set::throws(["msg"=>"itemdoesnotexist"]);
		}
		
		
	}
	public static function UpdateRFQLine(){
		$upd = $_POST["updaterfqline"];
		$count = count($upd);
		$msg = null;
		for($i=0; $i<$count; $i++){
			$itemnum = $upd[$i]["itemnum"];
			$warehouse = $upd[$i]["warehouse"];
			$racknum = $upd[$i]["racknum"];
			$newrfqnum = $upd[$i]["newrfqnum"];
			$process = $upd[$i]["process"];
			$set = new Set();
			if($process == "rfq"){
				$is = new Set();
				$isExist = $is->MboSetRemote("rfqline")->setWhere("itemnum='$itemnum' and rfqnum='$newrfqnum'")->MboRemote("itemnum")->getMboSet();
				$cnt = $isExist->counts();
				if($cnt == 0){
					$msg = "notexist";
					break;
				}else{
					$msg = "ok";
					$set->MboSetRemote("rfqline")->setWhere("itemnum='$itemnum' and rfqnum='$newrfqnum'")->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->change();
					$set2 = new Set();
					$invbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and racknum='$racknum'")->setValue("inspectstatus", "auctioning")->change();
				}
				
			}
			
		}
		Set::throws(["msg"=>$msg]);
	}
	public static function ChangeInspectionStatus(){
		$inspack = $_POST["changeinspitems"];
		$countinspack = count($inspack);
		$enterdate = Xbo::setEnterDate();
		$updrfqnum = null;
		$itemauctionarr = [];
		
		for($i=0; $i<$countinspack; $i++){
			$newstatus = $inspack[$i]["newinspectstatus"];
			$invbalanceid = $inspack[$i]["invbalanceid"];
			if(isset($inspack[$i]["ponum"])){
				$ponum = $inspack[$i]["ponum"];
			}else{
				$ponum = null;
			}
			
			
			$set2 = new Set();
			$invbalanceSet  = $set2->MboSetRemote("invbalance")->setWhere("invbalanceid='$invbalanceid'")->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("racknum")->getMboSet();
			$invbalance = $invbalanceSet->getMbo();
			$itemnum = $invbalance->getString("itemnum");
			$warehouse = $invbalance->getString("warehouse");
			$racknum = $invbalance->getString("racknum");
			$inspectStatusSet = $set2->MboSetRemote("inspectionstatus")->setValue("ponum", $ponum)->setValue("itemnum", $itemnum)->setValue("status", $newstatus)
			->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("startdate", $enterdate)->add();
			
			if($newstatus == "auctioning"){
				$msg = "auction";
				$obj = new stdClass();
				$obj->itemnum = $itemnum;
				$obj->warehouse = $warehouse;
				$obj->racknum = $racknum;
				$obj->process = "rfq";
				array_push($itemauctionarr, $obj);
			}else{
				$msg="ok";
				$set = new Set();
				$invbalanceSet = $set->MboSetRemote("invbalance")->setWhere("invbalanceid='$invbalanceid'")->setValue("inspectstatus", $newstatus)->change();
			}
		}
		if(count($itemauctionarr)>0){
			Set::throws(["msg"=>"nextprocess", "sequentialprocess"=>$itemauctionarr]);
		}else{
			if($msg== "ok"){
			Set::throws(["msg"=>"ok"]);
			}else{
			}
		}
	}
	public static function AssembleItem(){
		$itemnum = $_POST["assembleitem"];
		$warehouse = $_POST["warehouse"];
		$orgcode = $_POST["organization"];
		$sitecode = $_POST["sitecode"];
		$qty = $_POST["qty"];
		$conditiontype = $_POST["conditiontype"];
		$racknum = $_POST["racknum"];
		$changeby = Xbo::getLoginUser();
		$changedate = Xbo::setEnterDate();
						
		$condval = empty($conditiontype) ? " and (conditiontype is null or conditiontype='') " : " and conditiontype = '$conditiontype' ";
		$rackval = empty($racknum) ? " and (racknum is null or racknum='') " : " and racknum = '$racknum' ";	
		
		
		$set = new Set();
		$itemstructSet = $set->MboSetRemote("itemstruct")->setRelationship("itemcondition", "itemcondition.itemnum = itemstruct.subitemnum")
				->setWhere("itemstruct.itemnum='$itemnum'")->MboRemote("itemstruct.subitemnum")->MboRemote("itemstruct.quantity")
				->MboRemote("itemcondition.conditiontype")->MboRemote("itemcondition.conditionrate")->getMboSet();		
		$count = $itemstructSet->counts();
		
		$canassemble = true;
		$msg = null;
		$assembler = [];
		
		
		for($i=0; $i<$count; $i++){
			$itemstruct = $itemstructSet->getMbo();
			$subitemnum = $itemstruct->getString("subitemnum");
			$quantity = $itemstruct->getString("quantity");
			$conditiontype2 = $itemstruct->getString("conditiontype");
			$conditionrate = $itemstruct->getString("conditionrate");
			if($conditionrate == 100 || empty($conditionrate)){
				if (empty($conditiontype2)) $condval2 = " and (conditiontype is null or conditiontype='') ";
				else $condval2 = " and conditiontype = '$conditiontype2' ";
				$set2=  new Set();
				$invbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum='$subitemnum' and warehouse='$warehouse' $condval2 $rackval")->MboRemote("curbal")->getMboSet();
				$countInv = $invbalanceSet->counts();
				
				if($countInv>0){
					$invbalance = $invbalanceSet->getMbo();
					$childcurbal = $invbalance->getString("curbal");
					if($childcurbal>0){
						if($childcurbal >= $quantity){
							$canassemble = true;
							$set3 = new Set();
							$oldchildcurbal = $childcurbal;
							$newchildcurbal = $childcurbal-($quantity*$qty);
							$assemblerSet = $set3->MboSetRemote("invbalance")->setWhere("itemnum='$subitemnum' and warehouse='$warehouse' $condval2 $rackval")
							->setValue("curbal", $newchildcurbal)->setValue("oldcurbal", $oldchildcurbal)->setValue("changeby", $changeby)->setValue("changedate", $changedate);
							array_push($assembler, $assemblerSet);
							$msg = "assembled";
						}else{
							$canassemble = false;
							$msg = "failtoassembled";
							$assembler = [];
							break;
						}
					}else{
						$canassemble = false;
						$msg = "failtoassembled";
						$assembler = [];
						break;
					}
				}else{
					$canassemble = false;
					$msg = "failtoassembled";
					$assembler = [];
					break;
				}
			}else{
				continue;
			}
		}
		if($msg == "assembled"){
			$countassembler = count($assembler);
			for($i=0; $i<$countassembler; $i++){
				$assembler[$i]->change();
			}
			$set4=  new Set();
			$invbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("curbal")->getMboSet();
			$countinvparent = $invbalanceSet->counts();
			$set5 = new Set();
			if($countinvparent >0 ){
				$invbalance = $invbalanceSet->getMbo();
				$parentcurbal = $invbalance->getString("curbal");
				$newparentcurbal = $parentcurbal+$qty;
				$set5 = $set5->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->setValue("curbal", $newparentcurbal)->setValue("oldcurbal", $parentcurbal)
				->setValue("changeby", $changeby)->setValue("changedate", $changedate)->change();
			}else{
				$msg = "failtoassembled";
			}
		}else{
			$msg = "failtoassembled";
		}
		
		Set::throws(["msg"=>$msg]);
		
	}
	/*
	public static function DisassembleItem(){
		$itemnum = $_POST["disasembleitem"];
		$warehouse = $_POST["warehouse"];
		$orgcode = $_POST["organization"];
		$sitecode = $_POST["sitecode"];
		$qty = $_POST["qty"];
		$conditiontype = $_POST["conditiontype"];
		$racknum = $_POST["racknum"];
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='') ";
		else $condval = " and conditiontype = '$conditiontype' ";
		if (empty($racknum)) $rackval = " and (racknum is null or racknum='') ";
		else $rackval = " and racknum = '$racknum' ";
		
		$set = new Set();
		$invbalanceSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("curbal")->getMboSet();
		$count = $invbalanceSet->counts();
		if($count>0){
			$invbal = $invbalanceSet->getMbo();
			$oldcurbal = $invbal->getString("curbal");
			
			if($oldcurbal >= $qty){
				
				$newCurbalParent = $oldcurbal-$qty;
				
				$set2 = new Set();
				$itemstructSet = $set2->MboSetRemote("itemstruct")->setRelationship("itemcondition", "itemcondition.itemnum = itemstruct.subitemnum")
				->setWhere("itemstruct.itemnum='$itemnum'")->MboRemote("itemstruct.subitemnum")->MboRemote("itemstruct.quantity")
				->MboRemote("itemcondition.conditiontype")->MboRemote("itemcondition.conditionrate")->getMboSet();
				
				$count2 = $itemstructSet->counts();
				$enterby = Xbo::getLoginUser();
				$enterdate = Xbo::setEnterDate();
				$changeby = $enterby;
				$changedate = $enterdate;
				
				if($count2 >0 ){
					for($i=0; $i<$count2; $i++){
						$itemtruct = $itemstructSet->getMbo();
						$subitemnum  = $itemtruct->getString("subitemnum");
						$quantity  = $itemtruct->getString("quantity");
						$conditiontype2 = $itemtruct->getString("conditiontype");
						$conditionrate = $itemtruct->getString("conditionrate");
						
						if($conditionrate == 100 || empty($conditionrate)){
							if (empty($conditiontype2)) $condval2 = " and (conditiontype is null or conditiontype='') ";
							else $condval2 = " and conditiontype = '$conditiontype2' ";
							
							$set3 = new Set();
							$childInvbalanceSet = $set3->MboSetRemote("invbalance")->setWhere("itemnum='$subitemnum' and warehouse='$warehouse' $condval2 $rackval")->MboRemote("curbal")->getMboSet();
							$count3 = $childInvbalanceSet->counts();
							$childInvbalance = $childInvbalanceSet->getMbo();
							
							if($count3 > 0){
								$oldchildcurbal = $childInvbalance->getString("curbal");
								$newchildcurbal = $oldchildcurbal+($quantity*$qty);
								
								$setupdChild = new Set();
								$setupdChild->MboSetRemote("invbalance")->setWhere("itemnum='$subitemnum' and warehouse='$warehouse' $condval2 $rackval")->setValue("curbal", $newchildcurbal)
								->setValue("oldcurbal", $oldchildcurbal)->setValue("changedate", $changedate)->setValue("changeby", $changeby)->change();
							}else{
								$newchildcurbal = $quantity*$qty;
								XboSet::AddToInventory(null, [
								"itemnum"=>$subitemnum, "warehouse"=>$warehouse, "defrack"=>$racknum, "orgcode"=>$orgcode, 
								"sitecode"=>$sitecode, "conditiontype"=>$conditiontype, "curbal"=>$newchildcurbal
								], "kit");
							}
							$newchildcurbal = 0;
						}
					}
					$setupdParent = new Set();
					$NewinvbalanceSet = $setupdParent->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->setValue("curbal", $newCurbalParent)
					->setValue("oldcurbal", $oldcurbal)->change();
				}
				
				Set::throws(["msg"=>"disassembled"]);
			}else{
				Set::throws(["msg"=>"failtodisassembled"]);
			}
			
			
		}else{
			Set::throws(["msg"=>"failtodisassembled"]);
		}
		
	}
	*/
	
	public static function CheckIsKit(){
		$itemnum = $_POST["checkiskit"];

		$set = new Set();
		$itemStructSet = $set->MboSetRemote("itemstruct")->setWhere("itemnum='$itemnum'")->MboRemote("subitemnum")->getMboSet();
		$cnt = $itemStructSet->counts();
		if($cnt>0){
			
			Set::throws(["msg"=>"kit"]);
		}else{
			Set::throws(["msg"=>"nonkit"]);
		}
	}
	public static function mapIssueCurrentItem(){
		$_POST["issuecuritem"] = $_POST["mapIssueCurrentItem"];
		$_POST["warehouse"] =  $_POST["warehouse"];
		$_POST["racknum"] = $_POST["racknum"];
		$_POST["batchnum"]= $_POST["batchnum"];
		$itemnum = $_POST["issuecuritem"];
		$batchnum = $_POST["batchnum"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		


		if (empty($racknum)) $rackval = " and (racknum is null or racknum='') ";
		else $rackval = " and racknum = '$racknum' ";
		
		$set = new Set();
		$invSet = $set->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and batchnum = '$batchnum'  and warehouse='$warehouse' $rackval")->MboRemote("conditiontype")->MboRemote("sitecode")->getMboSet();
		$inv = $invSet->getMbo();
		$_POST["conditiontype"] = $inv->getString("conditiontype");
		$_POST["sitecode"] = $inv->getString("sitecode");
		
		$_POST["orgcode"] = $_POST["orgcode"];
		$_POST["wotranstype"] = $_POST["wotranstype"];
		$_POST["issuedate"]= $_POST["issuedate"];
		$_POST["wonum"] =  $_POST["wonum"];
		$wonum = $_POST["wonum"];
		$set = new Set();
		$woset = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("glaccount as gldebitacct")->MboRemote("assetnum")->MboRemote("location")->getMboSet();
		$wo = $woset->getMbo();
		
		
		$_POST["gldebitacct"] = $wo->getString("gldebitacct");
		$_POST["assetnum"] = $wo->getString("assetnum");
		$_POST["location"] = $wo->getString("location");
		
		$_POST["glcreditacct"] = Xbo::getwhgl($warehouse, "noprint");
		$_POST["quantity"] = $_POST["quantity"];
		$_POST["requestedby"] = $_POST["requestedby"];
		
		ActionSet::IssueCurrentItem();
		
	}
	
	public static function getTaxValue($taxcode){
		$ts = new Set();
		if(empty($taxcode)){
			$taxvalue = 0;
		}else{
			$taxSet = $ts->MboSetRemote("tax")->setWhere("taxcode='$taxcode'")->MboRemote("value")->getMboSet();
			$tax = $taxSet->getMbo();
			$taxvalue = $tax->getString("value");
			$taxvalue = $taxvalue/100;
		}
		
		return $taxvalue;
	}
	public static function SellItem()
	{
		
		$itemnum =  $_POST["invSellItem"];
		$conditiontype = $_POST["conditiontype"];
		$warehouse = $_POST["warehouse"];
		$racknum =$_POST["racknum"];
		$pricecode = $_POST["pricecode"];
		$costtype = $_POST["costtype"];
		$sellqty = $_POST["sellqty"];
		$transtype = $_POST["transtype"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$remarks = $_POST["remarks"];
		$refund = $_POST["refund"];
		$salesman = $_POST["salesman"];
		$paymenttype = $_POST["paymenttype"];
		$transnum = $_POST["transnum"];
		$taxcode = $_POST["taxcode"];
		$taxvalue = ActionSet::getTaxValue($taxcode);
		
		$unitcommission = Xbo::GetUnitCommission($itemnum, $warehouse, $pricecode, $conditiontype);
											
		if(isset($_POST["customercode"])) $customercode = $_POST["customercode"];
		else $customercode = "GENERAL";
		$linetype = "item";
		$condval = "";
		$rackval = "";
		if (empty($racknum)) { 
			$rackval = " and (racknum is null or racknum='')"; 
			$fromrackval = " and (fromrack is null or fromrack='')"; 
		}
		else { 
			$rackval = " and racknum = '$racknum'"; 
			$fromrackval = " and fromrack = '$racknum'"; 
		}
		if (empty($conditiontype)) { $condval = " and (conditiontype ='' or conditiontype is null) "; }
		else { $condval = " and conditiontype = '$conditiontype'";	}
		
		$financialperiod = Xbo::getFinancialPeriod();
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$set = new Set();
		if($costtype == "FIFO" || $costtype == "LIFO") {
			$rackvalavg = "";
			$crSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackvalavg $condval")->MboRemote("sum(curbal) as oldcurbal")->getMboSet();
		}else{
			if($costtype == "AVG") { 
				$rackvalavg = $rackval;
				$crSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackvalavg $condval")->MboRemote("curbal as oldcurbal")->getMboSet();
			}
		}
		
		$oldcurbal = $crSet->getMbo()->getString("oldcurbal");
	
		$itemSet = $set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->MboRemote("issueunit")->getMboSet()->getMbo();
		$saleunit = $itemSet->getString("issueunit");
		$description = $itemSet->getString("description");
		
		
		$seti = new Set();
		$inventorySet = $seti->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("issuetype")->getMboSet();
		$inventory = $inventorySet->getMbo();
		$issuetype = $inventory->getString("issuetype");
		
		
		if (empty($oldcurbal) || $oldcurbal < 1) $oldcurbal = 0;
		if ($transtype == "SALE") {
			if ($sellqty <= 0) {
				Set::throws(["msg" => "cannotSendZeroQuantity"]);
			}else{
				if ($oldcurbal < $sellqty) {
					Set::throws(["msg" => "cannotSellMoreThanLineQty", "sellqty" => $oldcurbal, "saleunit" => $saleunit]);
				}else{
					if ($sellqty > 0 && $sellqty <= $oldcurbal) {
						if ($sellqty > 0 && $sellqty <= $oldcurbal) {
							$ip = new Set();
							$invpriceSet = $ip->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and pricecode='$pricecode' $condval")
							->MboRemote("unitprice")->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("applytoall")->MboRemote("customergroup")->getMboSet();
							
							$invprice = $invpriceSet->getMbo();
							$unitprice = $invprice->getString('unitprice');
							$primarydiscount = $invprice->getString('primarydiscount');
							
							if(empty($unitprice)) $unitprice = 0;
							if(empty($primarydiscount)) $primarydiscount = 0;
							
							$secondarydiscount = $invprice->getString('secondarydiscount');
							if(empty($secondarydiscount)) $secondarydiscount = 0;
							
							$applytoall = $invprice->getString('applytoall');
							$customergroup = $invprice->getString('customergroup');
							
							$discountprice = 0;
							$unitpricediscount = 0;
							if($primarydiscount !=0 ){
								$discprice = ($primarydiscount*$unitprice)/100;
								$discountprice1 = $discprice;
								$unitpricediscount = $unitprice-$discprice;
								
								if($secondarydiscount !=0 ){
									$discprice = $secondarydiscount*$unitpricediscount/100;
									$discountprice2 = $discprice;
									$unitpricediscount = $unitpricediscount-$discprice;
								}else{
									$discountprice2 = 0;
								}
								
								$discountprice = $discountprice1+$discountprice2;
							}
							if($primarydiscount == 0 && $secondarydiscount == 0){
								$unitpricediscount = $unitprice;
							}
							$lineprice = $sellqty * $unitprice;
							
							$linepricediscount = $sellqty * $unitpricediscount;
							
							if($paymenttype == "kredit"){
								$glcreditacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode); // stok/penjualan berkurang
								$gldebitacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); //nambah piutang
							}else{
								if($paymenttype == "cashonbank"){
									$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode); // nambah kas BANK perusahaan
								}else{
									if($paymenttype == "cashonhand"){
										$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode); // nambah kas on HAND perusahaan
									}
								}
								$glcreditacct = Xbo::getControlAcct($warehouse, $orgcode, $sitecode); // mengurangi stok persediaan
							}
							
							if (empty($glcreditacct)) $glcreditacct = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
						
						
							if ($costtype == null ||$costtype == "") $costtype = Xbo::getCostType($orgcode);
							else $costtype = $costtype;
							
							$colnamecost = "avgcost";
							if($costtype == "AVG"){
								$colnamecost = "avgcost";
							}else{
								if($costtype == "STDCOST"){
									$colnamecost = "stdcost";
								}else{
									if($costtype == "LASTCOST"){
										$colnamecost = "lastcost";
									}
								}
							}
							
							
							$unitcost = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote($colnamecost)->getMboSet()->getMbo()->getString($colnamecost);
							$unitprofit = $unitprice - $unitcost;
							$lineprofit = $unitprofit * $sellqty;
							
							$unitprofitdiscount = $unitpricediscount - $unitcost;
							$lineprofitdiscount = $unitprofitdiscount * $sellqty;
							
							$linecost = $unitcost*$sellqty;
							$sellqtymin = $sellqty * -1;
							$linecostmin = $linecost*-1;
							
							$taxprice = $taxvalue*$linepricediscount;
							$unittaxprice = $taxprice/$sellqty;
							
							$linecommission = $unitcommission*$sellqty;
							
							$matsale = $set->MboSetRemote("matsaletrans")->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("description", $description)
							->setValue("quantity", $sellqtymin)->setValue("unitprice", $unitprice)->setValue("unitpricediscount", $unitpricediscount)->setValue("lineprice", $lineprice)->setValue("linepricediscount", $linepricediscount)->setValue("warehouse", $warehouse)
							->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("transdate", $enterdate)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
							->setValue("transtype", $transtype)->setValue("remarks", $remarks)->setValue("customercode", $customercode)->setValue("pricecode", $pricecode)->setValue("costtype", $costtype)
							->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
							->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("salesman", $salesman)->setValue("financialperiod", $financialperiod)
							->setValue("unitcost", $unitcost)->setValue("linecost", $linecostmin)->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)
							->setValue("discountprice", $discountprice)->setValue("transnum", $transnum)->setValue("paymenttype", $paymenttype)->setValue("taxcode", $taxcode)->setValue("taxprice", $taxprice)
							->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission);
							
							$setf = new Set();
							$fifotrans = $setf->MboSetRemote("matsaletrans")->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("description", $description)
							->setValue("unitprice", $unitprice)->setValue("unitpricediscount", $unitpricediscount)->setValue("warehouse", $warehouse)
							->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("transdate", $enterdate)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
							->setValue("transtype", $transtype)->setValue("remarks", $remarks)->setValue("customercode", $customercode)->setValue("pricecode", $pricecode)
							->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("salesman", $salesman)
							->setValue("financialperiod", $financialperiod)->setValue("paymenttype", $paymenttype)->setValue("taxcode", $taxcode)
							->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)->setValue("discountprice", $discountprice)->setValue("transnum", $transnum)
							->setValue("unitcommission", $unitcommission);
							
							$fl = new stdClass();
							$fl->tablename = "matsaletrans";
							$fl->transtype = $transtype;
							$fl->itemnum = $itemnum;
							$fl->warehouse = $warehouse;
							$fl->conditiontype = $conditiontype;
							$fl->racknum = $racknum;
							$fl->issuedqty = $sellqtymin;
							$fl->condval = $condval;
							$fl->rackval = $rackval;
							$fl->avgtrans = $matsale;
							$fl->fifotrans = $fifotrans;
							$fl->pricecode = $pricecode;
							$fl->unitprice = $unitprice;
							$fl->unitpricediscount  = $unitpricediscount;
							$fl->unitcost = $unitcost;
							$fl->costtype = $costtype;
							$fl->unittaxprice = $unittaxprice;
							$fl->unitcommission = $unitcommission;
							//SELL ITEMS
							$execfifo = XboSet::IssuedFIFOLIFO($fl);
							
							if($execfifo == "notenoughfifoqty"){
								Set::throws(["msg"=>"notenoughfifoqty"]);
							}else{
								$set = new Set();
								$newcurbal = $oldcurbal - $sellqty;
								$changeBal = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)->change();
								Set::throws(["msg"=>"sent"]);
								$basecurrency = $set->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("basecurrency")->getMboSet()->getMbo()->getString("basecurrency");
								$sellqtyFormat = number_format($sellqty, 2);
								$linepriceFormat = number_format($lineprice, 2);
								XboSet::AdjustConversionStock($itemnum, $warehouse);
								$activity = "sold <font color=blue><b>$sellqtyFormat $saleunit</b></font> of <font color=blue><b>$description</b></font> to general customer for <font color=blue><b>$linepriceFormat $basecurrency.</b></font>";
								Xbo::setActivity($activity);
							}
						}
					}
				}
			}
		}
		if ($transtype == "RETURN") {
			if ($sellqty <= 0) {
				if($issuetype == "FIFO" || $issuetype == "LIFO"){
					Set::throws([
						"msg" => "fifoliforeturn", 
						"itemnum" =>  $_POST["invSellItem"],
						"conditiontype" => $_POST["conditiontype"],
						"warehouse" => $_POST["warehouse"],
						"racknum" => $_POST["racknum"],
						"pricecode" => $_POST["pricecode"],
						"issuetype" => $issuetype,
						"sellqty" => $_POST["sellqty"],
						"transtype" => $_POST["transtype"],
						"orgcode" => $_POST["orgcode"],
						"sitecode" => $_POST["sitecode"],
						"remarks" => $_POST["remarks"],
						"customercode" => "GENERAL",
						"linetype" => "item",
					]);
				}else{
					Set::throws(["msg" => "cannotReturnLessThanZero"]);
				}
			}else{
				if ($sellqty > 0) {
					$s = new Set();
					if(empty($issuetype)) $issuetype = $costtype;
					$lpSet = $s->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' and warehouse = '$warehouse' and issuetype='$issuetype' and transtype = 'SALE' $fromrackval $condval ")
							->MboRemote("max(matsaletransid) as matsaletransid")->getMboSet();
					//echo $lpSet->sql;	
					$lp = $lpSet->getMbo();		
					$matsaletransid = $lp->getString("matsaletransid");
					
					$set = new Set();
					$lastPriceSet = $set->MboSetRemote("matsaletrans")->setWhere("matsaletransid='$matsaletransid'")
									->MboRemote("unitprice")->MboRemote("unitcost")->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")
									->MboRemote("unitpricediscount")->MboRemote("unitprofitdiscount")->MboRemote("unitprofit")
									->getMboSet();
								
					$lastprice = $lastPriceSet->getMbo();
					$unitprice = $lastprice->getString("unitprice");
					if($unitprice == null) $unitprice = 0; 
					
					$unitcost = $lastprice->getString("unitcost");
					if($unitcost == null) $unitcost = 0; 
					
					$unitprofit = $lastprice->getString("unitprofit");
					if($unitprofit == null) $unitprofit = 0; 
					
					$primarydiscount = $lastprice->getString("primarydiscount");
					if($primarydiscount == null) $primarydiscount = 0; 
					
					$secondarydiscount = $lastprice->getString("secondarydiscount");
					if($secondarydiscount == null) $secondarydiscount = 0; 
					
					$discountprice = $lastprice->getString("discountprice");
					if($discountprice == null) $discountprice = 0; 
					
					$unitpricediscount = $lastprice->getString("unitpricediscount");
					if($unitpricediscount == null) $unitpricediscount = 0; 
					
					$unitprofitdiscount = $lastprice->getString("unitprofitdiscount");
					if($unitprofitdiscount == null) $unitprofitdiscount = 0; 
					
					$conditionrate = Xbo::getConditionRate($conditiontype);
					if (empty($conditionrate)) $unitprice = $unitprice;
					else $unitprice = $unitprice * ($conditionrate / 100);
					
					$linecost = $unitcost * $sellqty;
					$lineprice = $unitprice * $sellqty;					
					$lineprofit = $unitprofit * $sellqty;	
					$linepricediscount = $unitpricediscount*$sellqty;
					$lineprofitdiscount = $unitprofitdiscount*$sellqty;
					
					$discountpricemin = $discountprice*-1;
					$linepricemin = $lineprice*-1;
					$lineprofitmin = $lineprofit*-1;
					$linepricediscountmin = $linepricediscount*-1;
					$lineprofitdiscountmin = $lineprofitdiscount*-1;
					
					if ( $unitprice == null ) {
						Set::throws(["msg"=>"noInventoryPrice"]);
					}else {
						if($paymenttype == "kredit"){
							$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode); // stok/penjualan berkurang
							$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); //nambah piutang
						}else{
							if($paymenttype == "cashonbank"){
								$glcreditacct = Xbo::getCashOnBankAcct($orgcode, $sitecode); // nambah kas BANK perusahaan
							}else{
								if($paymenttype == "cashonhand"){
									$glcreditacct = Xbo::getCashOnHandAcct($orgcode, $sitecode); // nambah kas on HAND perusahaan
								}
							}
							$gldebitacct = Xbo::getControlAcct($warehouse, $orgcode, $sitecode); // mengurangi stok persediaan
						}
						
						
						if($transnum != null){
							$ss = new Set();
							$mset = $ss->MboSetRemote("matsaletrans")->setWhere("transnum='$transnum'")->MboRemote("costdate")->MboRemote("expdate")->MboRemote("batchnum")
							->MboRemote("paymenttype")->MboRemote("transnum")->MboRemote("customercode")->MboRemote("salesman")->MboRemote("glcreditacct")->MboRemote("gldebitacct")->getMboSet();
							$ms = $mset->getMbo();
							$costdate = $ms->getString("costdate");
							$expdate = $ms->getString("expdate");
							$batchnum = $ms->getString("batchnum");
							$paymenttype = $ms->getString("paymenttype");//ada
							$transnum = $ms->getString("transnum");
							$customercode = $ms->getString("customercode");//ada
							$salesman = $ms->getString("salesman"); // ada
							$gldebitacct = $ms->getString("glcreditacct");
							$glcreditacct = $ms->getString("gldebitacct");
						}else{
							$costdate = null;
							$expdate = null;
							$batchnum = null;
							$transnum = null;
							$glcreditacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
						}
						
						$taxprice = $taxvalue*$linepricediscountmin;
						$unittaxprice = $taxprice/$sellqty;
						
						$set->MboSetRemote("matsaletrans")->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("pricecode", $pricecode)
						->setValue("quantity", $sellqty)->setValue("unitprice", $unitprice)->setValue("lineprice", $linepricemin)->setValue("costtype", $costtype)->setValue("lineprofit", $lineprofitmin)
						->setValue("unitprofit", $unitprofit)->setValue("conditiontype", $conditiontype)->setValue("towarehouse", $warehouse)->setValue("torack", $racknum)
						->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("transdate", $enterdate)->setValue("transtype", $transtype)
						->setValue("customercode", $customercode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->setValue("financialperiod", $financialperiod)->setValue("remarks", $remarks)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("issuetype", "AVG")
						->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)->setValue("discountprice", $discountpricemin)->setValue("unitpricediscount", $unitpricediscount)
						->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("linepricediscount", $linepricediscountmin)->setValue("lineprofitdiscount", $lineprofitdiscountmin)
						->setValue("paymenttype", $paymenttype)->setValue("salesman", $salesman)->setValue("taxcode", $taxcode)->setValue("taxprice", $taxprice)
						->setValue("costdate", $costdate)->setValue("expdate", $expdate)->setValue("batchnum", $batchnum)->setValue("transnum", $transnum)
						->add();
						
						if(isset($_POST["refund"])) $isrefund = $_POST["refund"];
						else $isrefund = "N";
						
						
						if($isrefund == "Y"){
						
							$invoicenum = $_POST["invoicenum"];
							$ob = new stdClass();
							$ob->invoicenum = $invoicenum;
							$ob->itemnum = $itemnum; 
							$ob->description = $description; 
							$ob->warehouse = $warehouse; 
							$ob->customercode = $customercode; 
							$ob->quantity = $sellqty; 
							
							$ob->unitcost = $unitcost; 
							$ob->unitprice = $unitprice; 
							$ob->unitprofit = $unitprofit; 
							$ob->unitpricediscount = $unitpricediscount; 
							$ob->unitprofitdiscount = $unitprofitdiscount; 
							
							
							$ob->linecost = $linecost; 
							$ob->lineprice = $linepricemin; 
							$ob->lineprofit = $lineprofitmin; 
							$ob->linepricediscount = $linepricediscountmin; 
							$ob->lineprofitdiscount = $lineprofitdiscountmin; 
							
							$ob->conditiontype = $conditiontype; 
							$ob->gldebitacct = $gldebitacct; 
							$ob->glcreditacct = $glcreditacct; 
							
							$ob->enterby = $enterby; 
							$ob->enterdate = $enterdate; 
							$ob->financialperiod = $financialperiod; 
							$ob->unittaxprice = $unittaxprice;
							ActionSet::CreateInvoiceTransaction($ob);
							
						}
						
						$isExist = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->MboRemote("itemnum")->getMboSet()->counts();
						if ($isExist > 0) {
							$newcurbal = $oldcurbal + $sellqty;
							$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)->change();
							Set::throws(["msg" => "returned"]);
						}
						else {
							$set->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)
							->setValue("curbal", $sellqty)->setValue("oldcurbal", 0)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)
							->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
							Set::throws(["msg" => "returned"]);
						}
						$invcostSet = $set->MboSetRemote("invcost");
						$isExist = $invcostSet->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->getMboSet()->counts();
						if($isExist == 0){
							$newStdCost = $invcostSet->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and conditiontype='NEW'")->MboRemote("stdcost")->getMboSet()->getMbo()->getString("stdcost");
							$stdcost = $newStdCost * ($conditionrate / 100);
							$avgcost = 0;
							$oldcost = 0;
							$lastcost = 0;
							$glaccount = Xbo::getGLAccount($itemnum);
							$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);
							$invcostadjacc = Xbo::getInvCostAdjAcct($warehouse, $orgcode, $sitecode);
							$set->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)->setValue("conditionrate", $conditionrate)
							->setValue("stdcost", $stdcost)->setValue("avgcost", $avgcost)->setValue("oldcost", $oldcost)->setValue("lastcost", $lastcost)->setValue("glaccount", $glaccount)
							->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)
							->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->query->add();
						}
						$sellqtyFormat = number_format($sellqty, 2);
						XboSet::AdjustConversionStock($itemnum, $warehouse);
						$activity = "has received return of item <font color=blue><b>$sellqtyFormat $saleunit</b></font> of <font color=blue><b>$description</b></font> from general customer to $warehouse warehouse.";
						Xbo::setActivity($activity);
					}
				}
			}
		}
	}
	public static function InitAction()

	{
		$x = explode("^", $_GET["initAction"]);
		$table = $x[0];
		$tableID = $table . "id";
		$tableid = $x[1];
		
		$tableSet = (new Set())->useTransactionManager()->MboSetRemote($table)->setWhere("$tableID='$tableid'")->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
		$itemnum = $tableSet->getString("itemnum");
		$warehouse = $tableSet->getString("warehouse");
		$orgcode = $tableSet->getString("orgcode");
		$sitecode = $tableSet->getString("sitecode");
		
		
		$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance")->setWhere("itemnum = '$itemnum' and warehouse = '$warehouse' and orgcode = '$orgcode' and sitecode = '$sitecode'")->MboRemote("sum(curbal) as totalcurbal")->getMboSet();
		$totalcurbal = $invbalanceSet->getMbo()->getString("totalcurbal");
		$nettrevenue = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("itemnum = '$itemnum' and warehouse = '$warehouse' and orgcode = '$orgcode' and sitecode = '$sitecode'")->MboRemote("sum(lineprofit) as nettrevenue")->getMboSet()->getMbo()->getString("nettrevenue");
		$grossrevenue = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("itemnum = '$itemnum' and warehouse = '$warehouse' and orgcode = '$orgcode' and sitecode = '$sitecode'")->MboRemote("sum(lineprice) as grossrevenue")->getMboSet()->getMbo()->getString("grossrevenue");
		$reservedqty = (new Set())->useTransactionManager()->MboSetRemote("invreserve")->setWhere("itemnum = '$itemnum' and warehouse = '$warehouse'")->MboRemote("sum(quantity) as reservedqty")->getMboSet()->getMbo()->getString("reservedqty");
		$expquantity =(new Set())->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum = '$itemnum' and warehouse = '$warehouse' and orgcode = '$orgcode' and sitecode = '$sitecode' and isexpired='Y'")->MboRemote("sum(curbal) as expiredqty")->getMboSet()->getMbo()->getString("expiredqty");
		
		if ($nettrevenue == null) $nettrevenue = 0;
		if ($grossrevenue == null) $grossrevenue = 0;
		if ($totalcurbal == null) $totalcurbal = 0;
		if ($expquantity == null) $expquantity = 0;
		if ($reservedqty == null) $reservedqty = 0;
	
		$availableqty = $totalcurbal - $expquantity - $reservedqty;
		Set::throws(["reservedqty" => $reservedqty, "totalcurbal" => $totalcurbal, "nettrevenue" => $nettrevenue, "grossrevenue" => $grossrevenue, "expquantity" => $expquantity, "availableqty" => $availableqty]);
	}
	public static function CostAdjustment($value=null)
	{
		
		if($value == null) {
			$x = explode("`", $_POST["costAdj"]);
			$id = $x[1];
			$newStdCost = $_POST["newstdcost"];
			$newAvgCost = $_POST["newavgcost"];
			$synfifolifoqty = $_POST["synfifolifoqty"];
		}else{
			$newStdCost = $value["newstdcost"];
			$newAvgCost = $value["newavgcost"];
			$synfifolifoqty = $value["synfifolifoqty"];
		}
		
		
		
		$msg = null;
		$financialperiod = Xbo::getFinancialPeriod();
		
		if ($newAvgCost < 0 || $newStdCost < 0) $msg = "CannotAdjustLessThanZero"; 
		else {
			$set = new Set();
			if($value == null){
				$invcostSet = $set->MboSetRemote("invcost")->setWhere("invcostid = '$id'");
				$invcost = $invcostSet->MboRemote("itemnum")->MboRemote("warehouse")->MboRemote("conditiontype")->MboRemote("stdcost")->MboRemote("avgcost")
					->MboRemote("invcostadjacc")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
				$itemnum = $invcost->getString("itemnum");
				$warehouse = $invcost->getString("warehouse");
				$conditiontype = $invcost->getString("conditiontype");
				if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
				else $condval = " and conditiontype='$conditiontype'";
				
				$oldStdcost = $invcost->getString("stdcost");
				$oldAvgcost = $invcost->getString("avgcost");
				
				$invcostadjacc = $invcost->getString("invcostadjacc");
				$orgcode = $invcost->getString("orgcode");
				$sitecode = $invcost->getString("sitecode");
			}else{
				$itemnum = $value["itemnum"];
				$warehouse = $value["warehouse"];
				$conditiontype = $value["conditiontype"];
				if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
				else $condval = " and conditiontype='$conditiontype'";
				$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval");
				$invcost = $invcostSet->MboRemote("stdcost")->MboRemote("avgcost")->MboRemote("invcostadjacc")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
				
				$oldStdcost = $invcost->getString("stdcost");
				$oldAvgcost = $invcost->getString("avgcost");
				
				$invcostadjacc = $invcost->getString("invcostadjacc");
				$orgcode = $invcost->getString("orgcode");
				$sitecode = $invcost->getString("sitecode");
				
			}
			
			$controlacc = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
			$changeby = Xbo::getLoginUser();
			$changedate = Xbo::setEnterDate();
			
			$sb = new Set();
			$invbalanceSet = $sb->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("sum(curbal) as sumcurbal")->getMboSet();
			$invbalance = $invbalanceSet->getMbo();
			$sumcurbal = $invbalance->getString("sumcurbal");
			
			if($oldStdcost == null){ $oldStdcost = 0; }
			if ($oldStdcost != $newStdCost) {
				$set->MboSetRemote("invcost")->setWhere("invcostid = '$id'")->setValue("stdcost", $newStdCost)->change();
				$variancecost = $newStdCost - $oldStdcost;
				
				if ($newStdCost > $oldStdcost) {
					$gldebitacct = $controlacc;
					$glcreditacct = $invcostadjacc;
				}
				if ($newStdCost < $oldStdcost) {
					$gldebitacct = $invcostadjacc;
					$glcreditacct = $controlacc;
				}
				$transtype = 'STDCOSTADJ';
				$set->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", $transtype)->setValue("warehouse", $warehouse)
				->setValue("conditiontype", $conditiontype)->setValue("quantity", 0)->setValue("oldquantity", 0)
				->setValue("oldcost", $oldStdcost)->setValue("variancecost", $variancecost)->setValue("cost", $newStdCost)->setValue("avgcost", $oldAvgcost)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)->setValue("enterby", $changeby)->setValue("enterdate", $changedate)->setValue("financialperiod", $financialperiod)->add();
				
				$msg = "adjustedstdcost";
			}
			if ($oldAvgcost != $newAvgCost) {
				$set->MboSetRemote("invcost")->setWhere("invcostid = '$id'")->setValue("avgcost", $newAvgCost)->change();
				Xbo::AdjustWTMaterialCost($itemnum, $warehouse, $newAvgCost);
				$variancecost = $newAvgCost - $oldAvgcost;
				if ($newAvgCost > $oldAvgcost) {
					$gldebitacct = $controlacc;
					$glcreditacct = $invcostadjacc;
				}
				if ($newAvgCost < $oldAvgcost) {
					$gldebitacct = $invcostadjacc;
					$glcreditacct = $controlacc;
				}
				$transtype = 'AVGCOSTADJ';
				$variancecost = $newAvgCost - $oldAvgcost;
				
				$set->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", $transtype)->setValue("warehouse", $warehouse)->setValue("cost", $newAvgCost)
				->setValue("conditiontype", $conditiontype)->setValue("quantity", 0)->setValue("oldquantity", 0)->setValue("variancecost", $variancecost)
				->setValue("oldcost", $oldAvgcost)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)->setValue("enterby", $changeby)->setValue("enterdate", $changedate)->setValue("financialperiod", $financialperiod)->add();
				$msg="avgcostadjusted";
			}
			
			if($synfifolifoqty == "Y"){
				$set = new Set();
				$issuetype = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("issuetype")->getMboSet()->getMbo()->getString("issuetype");
			
				if($issuetype == "FIFO" || $issuetype == "LIFO"){
					if($issuetype == "FIFO"){
						$asd = "min";
					}else{
						if($issuetype == "LIFO"){
							$asd = "max";
						}
					}
					$set = new Set();
					if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
					else $condval = " and conditiontype='$conditiontype'";
					
					$wc = "itemnum='$itemnum' and warehouse='$warehouse' $condval and quantity>0";
					$wc2 = " $wc and costdate in (select $asd(costdate) from invfifolifocost where $wc )";
					
					$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")
					->setWhere($wc2)->MboRemote("costdate")->MboRemote("unitcost")
					->getMboSet();
					
					$cnt = $invfifolifocostSet->counts();
					$invfifolifocost = $invfifolifocostSet->getMbo();
					$costdate = $invfifolifocost->getString("costdate");
					$floldunitcost = $invfifolifocost->getString("unitcost");
					$wheredate = Super::whereDate("costdate", $costdate);
					if($cnt>0){
						
						$set = new Set();
						$set->MboSetRemote("invfifolifocost")->setWhere("$wc $wheredate ")->setValue("unitcost", $newAvgCost)->change();
						
						$variancecost = $newAvgCost - $floldunitcost;
						$set = new Set();
						$set->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", "FLCOSTADJ")->setValue("warehouse", $warehouse)->setValue("cost", $newAvgCost)
						->setValue("conditiontype", $conditiontype)->setValue("quantity", 0)->setValue("oldquantity", 0)->setValue("variancecost", $variancecost)
						->setValue("oldcost", $floldunitcost)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("orgcode", $orgcode)
						->setValue("sitecode", $sitecode)->setValue("enterby", $changeby)->setValue("enterdate", $changedate)->setValue("financialperiod", $financialperiod)->add();
					}
				}
			}
			
			
			
		}
		if($msg == null){
			Set::throws(["msg"=>"noadjustment"]);
		}else{
			Set::throws(["msg"=>"adjusted"]);
		}
	}

	public static function getItemImages()

	{
		$payload = Super::getParam("getItemImages");
		$inventoryid = $payload["id"];
	
		
		$itemnum = (new Set())->useTransactionManager()->MboSetRemote("inventory")
		->where("inventoryid=?", $inventoryid)->getMboValue("itemnum");
	
	
		$fileSet = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")
		->setRelationship("item", "item.itemid = fileinfo.ownerid")
		->where("fileinfo.ownertable='ITEM' and item.itemnum=?", $itemnum)
		->MboRemote("fileinfo.filename")->MboRemote("fileinfo.type as ftype")->MboRemote("fileinfo.description as fdesc")->getMboSet();

		$fsname = ContextPath::getFullHostName();

		$context = "<table><tr>";

		$content_context = [];
		while($fs = $fileSet->fetch()){
			$fname = $fs["filename"];
			$ftype = $fs["ftype"];
			$fdesc = $fs["fdesc"];
			$thumbPath = $fsname . "/attachments/thumbs/" . "thumb_" . $fname;
			$path = $fsname . "/attachments/" . $fname;
			$content = "";
			if ($ftype != "ytb") {
				$content = "<img src='$thumbPath'>";
			}
			if ($ftype == "ytb") {
				$content = "<iframe width='300' height='195' src='$fname' frameborder='0' allowfullscreen> </iframe>";
			}

			$drw = "<td style='vertical-align:middle;'><a href='$path' style='text-decoration: none; ' target='_blank'>$content</a></td>";
			$content_context [] = $drw;
		}

		if(!empty($content_context)){
			$content = implode("", $content_context);
			$context .= $content."</tr><table>";
			Super::sendJsonResponse("success", $context);
		}

		Super::sendJsonResponse("success", "No attachment found");
		
	}
	public static function SetConsignmentQuantity()
	{
		$itemnum = $_POST["setconsignqty"];
		$warehouse = $_POST["warehouse"];
		$fdate = $_POST["fromdate"];
		$tdate = $_POST["todate"];
		$fromdate = Xbo::getDate($fdate);
		$todate = Xbo::getDate($tdate);
		
		$conditiontype = $_POST["conditiontype"];
		$consignmenttype = $_POST["consignmenttype"];
		if (empty($conditiontype)) {
			$condval = " and (conditiontype is null or conditiontype='')";
		}
		else {
			$condval = " and conditiontype='$conditiontype'";
		}
		$set = new Set();
		$mx = new Mxs();
		
		
		if($fdate == null || $tdate == null){
			Set::throws(["msg"=>"daterangenull"]);
		}else{
			if($conditiontype == null){
				$ct = new Set();
				$invbalanceSet = $ct->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum'")->MboRemote("conditiontype")->getMboSet();
				$countt = $invbalanceSet->counts();
				if($countt> 0){
					Set::throws(["msg"=>"conditionenabled"]);
					return null;
				}
			}
			if ($consignmenttype == "customer") {
				$fromdate = $mx->dateFormat($fromdate);
				$todate = $mx->dateFormat($todate);
					
				$matsaleSet = $set->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and transdate>=$fromdate and transdate<=$todate $condval and sonum is null  and invoiced='N'")->MboRemote("sum(quantity) as quantity")->getMboSet();
				$quantity = $matsaleSet->getMbo()->getString("quantity") * -1;
					
				$maxDateSet = $set->MboSetRemote("invconsign")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and consigntype='customer' $condval")->MboRemote("max(todate) as maxdate")->getMboSet();
				$maxdate = $maxDateSet->getMbo()->getString("maxdate");
				if($maxdate == null){
					$sumprevConsignQty =0;
				}else{
					$invconsignSet = $set->MboSetRemote("invconsign")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval and todate<=$maxdate")->MboRemote("sum(consignqty) as sumconsignqty")->getMboSet();
					$sumprevConsignQty = $invconsignSet->getMbo()->getString("sumconsignqty");
				}
					
				$newqty = $quantity - $sumprevConsignQty;
				if ($newqty > 0) Set::throws(["msg" => $newqty]);
				if ($newqty <= 0) Set::throws(["msg" => "noqtyduringperiod"]);
			}else {
				if ($consignmenttype == "vendor") {
					$chs = new Set();
					
					$matrecSet = $set->MboSetRemote("matusetrans")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and enterdate>='$fromdate' and enterdate<='$todate' $condval")
					->MboRemote("sum(quantity) as quantity")->getMboSet();						
					$quantity = $matrecSet->getMbo()->getString("quantity") * -1;
					
					$matsaleSet = $set->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and enterdate>='$fromdate' and enterdate<='$todate' $condval  and sonum is null  and invoiced='N'")
					->MboRemote("sum(quantity) as quantity")->getMboSet();	
					$quantity2 = $matsaleSet->getMbo()->getString("quantity") * -1;
					
					$maxDateSet = $set->MboSetRemote("invconsign")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and consigntype='vendor' $condval")->MboRemote("max(todate) as maxdate")->getMboSet();
					$maxdate = $maxDateSet->getMbo()->getString("maxdate");
					if($maxdate == null){
						$sumprevConsignQty =0;
					}else{
						$invconsignSet = $set->MboSetRemote("invconsign")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval and todate<=$maxdate")->MboRemote("sum(consignqty) as sumconsignqty")->getMboSet();
						$sumprevConsignQty = $invconsignSet->getMbo()->getString("sumconsignqty");
					}
					$totalQuantity = $quantity+$quantity2;
					
					$newqty = $totalQuantity - $sumprevConsignQty;
					if ($newqty > 0) {
						Set::throws(["msg" => $newqty]);
					}
					if ($newqty <= 0) {
						Set::throws(["msg" => "noqtyduringperiod"]);
					}
				}else{
					if ($consignmenttype == null) {
						Set::throws(["msg" => "consigntypenull"]);
					}
				}
			}
		}
	}
	
	public static function GetConsignmentInvoice()
	{
		$invoicenum = $_POST["getconsigninvoice"];
		$set = new Set();
		$invoiceSet = $set->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("vendorcode")->MboRemote("customercode")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$vendorcode = $invoice->getString("vendorcode");
		$customercode = $invoice->getString("customercode");
		if (empty($customercode) && empty($vendorcode)) {
			$whom = null;
			$consignmenttype = "none";
		}else{
			if (empty($vendorcode)) {
				$whom = $customercode;
				$consignmenttype = "customer";
			}else{
				if (empty($customercode)) {
					$whom = $vendorcode;
					$consignmenttype = "vendor";
				}
			}
		}
		Set::throws(["whom" => $whom, "consignmenttype" => $consignmenttype]);
	}
	
	public static function SetInventoryPrice()

	{
		$itemnum = $_POST["setinvprice"];
		$warehouse = $_POST["warehouse"];
		$pricecode = $_POST["pricecode"];
		$conditiontype = $_POST["conditiontype"];
		$aa = 0;
		$condval =null;
		if (empty($conditiontype)) {
			$setct = new Set();
			$invcostct = $setct->MboSetRemote("invcost")->setWhere("itemnum='$itemnum'")->MboRemote("conditiontype")->getMboSet();
			$aa = $invcostct->counts();
			if($aa>0){
			}else{
				$condval = " and (conditiontype is null or conditiontype='')";	
			}
		}
		else {
			$condval = " and conditiontype='$conditiontype'";
		}
		$set = new Set();
		$pricerate = $set->MboSetRemote("pricegroup")->setWhere("pricecode='$pricecode'")->MboRemote("pricerate")->getMboSet()->getMbo()->getString("pricerate");
		$invpriceSet = $set->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("itemnum")->getMboSet();
		$countInvprice = $invpriceSet->counts();
		
		if ($countInvprice == 0) {
			if ($pricerate == 100) {
				Set::throws(["msg" => "added"]);
			}else {
				if($aa>0 && $conditiontype == null){
					if($pricerate <100){
						$set2 = new Set();
						$invpriceSet = $set2->MboSetRemote("invprice")->setWhere("invprice.itemnum='$itemnum' and invprice.warehouse='$warehouse' and pricegroup.pricerate=100")
						->setRelationship("pricegroup", "invprice.pricecode=pricegroup.pricecode")->MboRemote("invprice.pricecode")->getMboSet();
						$count = $invpriceSet->counts();
						if($count == 0){
							Set::throws(["msg" => "mustenter100pricerate"]);
						}else{
							Set::throws(["msg" => "added"]);
						}
					}else{
						Set::throws(["msg" => "added"]);
					}
					
				}else{
					if($aa>0 && $conditiontype != null){
						Set::throws(["msg" => "mustenter100pricerate"]);
					}
				}
			}
		}
		else {
			$invpriceSet = $set->MboSetRemote("invprice")->setRelationship("pricegroup", "invprice.pricecode = pricegroup.pricecode")
			->setWhere("invprice.itemnum='$itemnum' and invprice.warehouse='$warehouse' and pricegroup.pricerate=(select max(pricerate) from invprice where invprice.itemnum='$itemnum' and pricegroup.pricerate=100 ) $condval")
			->MboRemote("invprice.unitprice")->getMboSet()->getMbo();
			$unitprice = $invpriceSet->getString("unitprice");
		
			if($unitprice == null) {
				Set::throws(["msg" => 0]);
			}else{
				$invpriceSet2 = $set->MboSetRemote("pricegroup")->setWhere("pricecode='$pricecode'")->MboRemote("islesser")->getMboSet()->getMbo();
				$islesser = $invpriceSet2->getString("islesser");
				if ($islesser == "N") {
					if($pricerate == 100){
						$percentage  = 100;
					}else{
						$percentage = 100 + $pricerate;
					}
				}
				else {
					$percentage = 100 - $pricerate;
				}
				$newprice = $unitprice * ($percentage / 100);
			
				if($aa>0 && $conditiontype == null){
					if($conditiontype == null){
						Set::throws(["msg" => $newprice]);
					}else{
						Set::throws(["msg" => 0]);
					}
				}else{
					if($aa>0 && $conditiontype != null){
						Set::throws(["msg" => $newprice]);
					}else{
						if($aa == 0 && $conditiontype != null) {
							Set::throws(["msg" => $newprice]);
						}else{
							Set::throws(["msg" => $newprice]);
						}
					}
				}
			}
		}
	}
	
	public static function WorkorderGLAccount()
	{
		$wonum = $_GET["woglaccount"];
		$set = new Set();
		$wo = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("glaccount")->MboRemote("assetnum")->MboRemote("location")->getMboSet()->getMbo();
		$glaccount = $wo->getString("glaccount");
		$assetnum = $wo->getString("assetnum");
		$location = $wo->getString("location");
		Set::throws(["gldebitacct" => $glaccount,"assetnum" => $assetnum,"location" => $location]);
	}
	public static function AssetGLAccount()
	{
		$assetnum = $_GET["assetglaccount"];
		$set = new Set();
		$asset = $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->MboRemote("glaccount")->MboRemote("locnum")->getMboSet()->getMbo();
		$glaccount = $asset->getString("glaccount");
		$locnum = $asset->getString("locnum");
		Set::throws(["gldebitacct" => $glaccount, "location"=>$locnum, "wonum"=>null]);
	}
	public static function LocationGLAccount()
	{
		$locnum = $_GET["locglaccount"];
		$set = new Set();
		$glaccount = $set->MboSetRemote("locations")->setWhere("locnum='$locnum'")->MboRemote("glaccount")->getMboSet()->getMbo()->getString("glaccount");
		Set::throws(["gldebitacct" => $glaccount, "assetnum"=>null, "wonum"=>null]);
	}
	public static function SelectTransferUI(){
		$itemnum = $_POST["selectui"];
		$warehouse = $_POST["warehouse"];
		$ui = "fifolifoui";
		$set = new Set();
		$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("quantity")->getMboSet();
		$count  = $invfifolifocostSet->counts();
		if($count>0){
			$ui = "fifolifoui";
		}else{
			$ui = "stdui";
		}
		
		$ss = new stdClass;
		$ss->ui = $ui;
		Set::throws($ss);
	}
	public static function FIFOLIFOTransferItems($fl){
		$itemnum = $fl->itemnum;
		$fromwh = $fl->fromwarehouse;
		$fromrack = $fl->fromrack;
		$condtype = $fl->conditiontype;
		$qty = $fl->quantity;		
		$towh = $fl->towarehouse;
		$torack = $fl->torack;
		$invfifolifocostid = $fl->invfifolifocostid;
		$orderby = "desc";
		
		if($fl->initquantity > 0){
			$newqty = $fl->initquantity-$fl->transferqty;
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid=?", $invfifolifocostid)->setValue("quantity", $newqty)->change();
		}else{
			throw new Exception("The source quantity is insufficient for transfer");
		}
	}
	public static function StockOpnameFL(){
		$stockopnamefl =  $_POST["stockopnamefl"];
		$cnt = count($stockopnamefl);
		$tfl = new stdClass;
		$msgr = [];
		
		for($i=0; $i<$cnt; $i++){
			$tt = $stockopnamefl[$i];
			$id = $tt["id"];
			$newqty = $tt["newqty"];
			$costdate = $tt["costdate"];
			$expdate = $tt["expdate"];
			$warehouse = $tt["warehouse"];
			$quantity = $tt["quantity"];
			$racknum = $tt["racknum"];
			$batchnum = $tt["batchnum"];
			
			$set = new Set();
			$invfl = $set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$id'")->MboRemote("itemnum")->MboRemote("conditiontype")->getMboSet()->getMbo();
			$conditiontype = $invfl->getString("conditiontype");
			$itemnum = $invfl->getString("itemnum");
					
			$set = new Set();
			$set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$id'")->setValue("quantity", $newqty)->change();
			
			if(empty($racknum)) {
				$rackval = " and (racknum is null or racknum = '')";
			}else{
				$rackval = " and racknum = '$racknum' ";
			}
			
			if(empty($conditiontype)){
				$condval = " and (conditiontype is null or conditiontype = '')";
				$conditionrate = 0;
			}else{
				$condval = " and conditiontype = '$condtype' ";
				$conditionrate = Xbo::getConditionRate($conditiontype);
			}
		
		
			$set = new Set();
			$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->setValue("curbal", $newqty)->change();
			$newobj = new stdClass();
			$newobj->curbal = $newqty;
			$newobj->id = $invbalanceid;
			$newobj->transtype = "RECBALADJ";
			XboSet::CurrentBalanceAdjustment(null, $newobj, "nomsg");
		}
		
		$aa = new stdClass;
		$aa->msg = "stockupdated";
		Set::throws($aa);
	}
	
	public static function mapTransferItems(){
		/*
		towarehouse=WH-B, 
		toracknum=Drawer-01, 
		fromracknum=, 
		fromwarehouse=WH-B, 
		batchnum=62-571-1712, 
		mapTransferItems=12, 
		costdate=2022-03-30 13:03:00, 
		expdate=, 
		transferqty=3, 
		actualdate=2023-09-30 13:45:20, 
		invfifolifocostid=15
		*/
		$itemnum = $_POST["mapTransferItems"];
		$fromwarehouse = $_POST["fromwarehouse"];
		$fromracknum = $_POST["fromracknum"];
		$expdate = $_POST["expdate"];
		$costdate = $_POST["costdate"];
		$batchnum = $_POST["batchnum"];
		$towarehouse = $_POST["towarehouse"];
		$toracknum = $_POST["toracknum"];
		$transferqty = $_POST["transferqty"];
		$transferdate = $_POST["actualdate"];
		$invfifolifocostid = $_POST["invfifolifocostid"];
		$quantity =  $_POST["quantity"];
		$transferfl = [];
		$tt = new stdClass;
		
		$tt = array();
		$tt["id"] = $invfifolifocostid;
		$tt["quantity"] = $quantity;
		$tt["towarehouse"] = $towarehouse;
		$tt["toracknum"] = $toracknum;
		$tt["transferdate"] = $transferdate;
		$tt["itemnum"] = $itemnum;
		$tt["batchnum"] = $batchnum;
		$tt["expdate"] = $expdate;
		$tt["costdate"] = $costdate;
		$tt["racknum"] = $fromracknum;
		$tt["transferqty"] = $transferqty;
		
		array_push($transferfl, $tt);
		
		$_POST["transferfl"] = $transferfl;
		ActionSet::TransferItemFIFOLIFO();
	}
	
	
	public static function TransferItemFIFOLIFO(){
		$transferfl = Super::getParam("transferfl");
		$tfl = new stdClass;
		$msgr = [];
		$feedback_array = [];
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();

		foreach($transferfl as $row){
			$id = $row["id"];
			$invfifolifoSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost ifl")->where("ifl.invfifolifocostid=?", $id)
			->setRelationship("item it", "ifl.itemnum = it.itemnum")
			->MboRemote("ifl.orgcode, ifl.sitecode, ifl.warehouse, ifl.racknum, ifl.itemnum, ifl.conditiontype, ifl.unitcost, ifl.costdate, ifl.invfifolifocostid, it.description")
			->getMboSet();

			$invfifolifo = $invfifolifoSet->getMbo();
			$itemnum = $invfifolifo->getString("itemnum");
			$itemdesc = $invfifolifo->getString("description");
			$fromwarehouse = $invfifolifo->getString("warehouse");
			$fromracknum = $invfifolifo->getString("racknum");
			$unitcost = $invfifolifo->getString("unitcost");
			$conditiontype = $invfifolifo->getString("conditiontype");
			$costdate = $invfifolifo->getString("costdate");
			$orgcode = $invfifolifo->getString("orgcode");
			$sitecode = $invfifolifo->getString("sitecode");
			$quantity = $row["quantity"];
			$towarehouse = $row["towarehouse"];
			$toracknum = $row["toracknum"];
			$transferdate = $row["transferdate"];
			$transferqty = $row["transferqty"];
			$batchnum = $row["batchnum"];
			$expdate = $row["expdate"];
			$conditionrate = Xbo::getConditionRate($conditiontype);
			$financialperiod = Xbo::getFinancialPeriod();
			$invfifolifocostid = $id;
			
			if ($transferqty <= 0) {
				throw new Exception("Transfer quantity for item $itemnum must be greater than zero.");
			}

			if (empty($towarehouse)) {
				throw new Exception("Destination warehouse (towarehouse) is required for item $itemnum.");
			}

			$transferDateObj = new DateTime(date('Y-m-d', strtotime($transferdate)));
			$todayObj = new DateTime(date('Y-m-d'));

			if ($transferDateObj > $todayObj) {
				throw new Exception("Transfer date for item $itemnum cannot be in the future.");
			}

			$tfl->orgcode = $orgcode;
			$tfl->sitecode = $sitecode;
			$tfl->itemnum = $itemnum;
			$tfl->initquantity = $quantity;
			$tfl->batchnum = $batchnum;
			$tfl->fromwarehouse = $fromwarehouse;
			$tfl->fromrack = $fromracknum;
			$tfl->fromracknum = $fromracknum;
			$tfl->towarehouse = $towarehouse;
			$tfl->torack = $toracknum;
			$tfl->toracknum = $toracknum;
			$tfl->quantity = $transferqty;
			$tfl->transferqty = $transferqty;
			$tfl->conditiontype = $conditiontype;
			$tfl->conditionrate = Xbo::getConditionRate($conditiontype);
			$tfl->unitcost = $unitcost;
			$tfl->financialperiod = Xbo::getFinancialPeriod();
			$tfl->enterby = $enterby;
			$tfl->enterdate = $enterdate;
			$tfl->costdate = $costdate;
			$tfl->expdate = $expdate;
			$tfl->invfifolifocostid = $id;


			ActionSet::FIFOLIFOTransferItems($tfl);

			$ob = new stdClass();
			$ob->expdate = $expdate;
			$ob->costdate = $costdate;
			$ob->batchnum = $batchnum;
			$ob->itemnum = $itemnum;
			$ob->towarehouse = $towarehouse;
			$ob->toracknum = $toracknum;
			$ob->racknum = $fromracknum;
			$ob->conditiontype = $conditiontype;
			$ob->enterby = $enterby;
			$ob->enterdate = $enterdate;
			$ob->orgcode = $orgcode;
			$ob->sitecode = $sitecode;
			$ob->quantity = $quantity;
			$ob->financialperiod = Xbo::getFinancialPeriod();
			$ob->conditionrate = Xbo::getConditionRate($conditiontype);
			$ob->unitcost = $unitcost;					
			$ob->retqty = $transferqty;
			$ob->fromwarehouse = $fromwarehouse;
			$ob->fromracknum = $fromracknum;
			$ob->wonum = null;
			//ActionSet::InsertOrUpdateINVFIFOLIFOCOST($ob);
			ActionSet::UpdateAllInventoryObject($ob);
		}

		
		$aa = new stdClass;
		$aa->msg = "transfered";
		Set::throws($aa);
	}
	public static function TransferItems($ss = null, $printjson=null)

	{
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		$fl = new stdClass();
		if($ss == null){
			$itemnum = $_POST["transferItem"];
			$fromrack = $_POST["fromrack"];
			$fromwh = $_POST["fromwh"];
			$towh = $_POST["towh"];
			$torack = $_POST["torack"];
			$condtype = $_POST["conditiontype"];
			$qty = $_POST["qty"];
			
			if(isset($_POST["actualdate"])){
				$actualdate = $_POST["actualdate"];
			}else{
				$actualdate = $enterdate;
			}
		}else{
			$itemnum = $ss->itemnum;
			$fromrack =  $ss->fromracknum;
			$fromwh =  $ss->fromwarehouse;
			$towh =  $ss->towarehouse;
			$torack = $ss->toracknum;
			$condtype =  $ss->conditiontype;
			$qty =  $ss->transferqty;
			
			if(isset($ss->actualdate)){
				$actualdate = $ss->actualdate;
			}else{
				$actualdate = $enterdate;
			}
		}
		
		
		$set = new Set();
		$description = $set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
		$invItemnum = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse = '$towh'")->MboRemote("itemnum")->getMboSet();
		$countinvItemnum = $invItemnum->counts();
		
		if(empty($torack)) {
			$torackval = "and (racknum='' or racknum is null) ";
		}else{
			$torackval = " and racknum='$torack' ";
		}
		if (empty($fromrack)) {
			$fromrackval = "and (racknum='' or racknum is null) ";
		}
		else {
			$fromrackval = "and racknum='$fromrack'";
		}
		if (empty($condtype)) {
			$fromcondval = "and (conditiontype='' or conditiontype is null)";
		}
		else {
			$fromcondval = "and conditiontype='$condtype'";
		}
		//FROM WAREHOUSE
		$invbalanceSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse = '$fromwh' $fromrackval $fromcondval")->MboRemote("curbal")->MboRemote("expdate")->getMboSet();
		
		$invbalance = $invbalanceSet->getMbo();
		$oldCurbalFrom = $invbalance->getString("curbal");
		if($oldCurbalFrom == null) $oldCurbalFrom =0;
		$expdate = $invbalanceSet->getString("expdate");
		$newCurbalFrom = $oldCurbalFrom - $qty;
		
		$warehouseSet = $set->MboSetRemote("warehouse")->setWhere("warehouse='$towh'")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("controlacc")->getMboSet()->getMbo();
		$orgcode = $warehouseSet->getString("orgcode");
		$sitecode = $warehouseSet->getString("sitecode");
		$gldebitacct = $warehouseSet->getString("controlacc");
		$glcreditacct = $set->MboSetRemote("warehouse")->MboRemote("controlacc")->setWhere("warehouse='$fromwh'")->getMboSet()->getMbo()->getString("controlacc");
		$invCostSet = $set->MboSetRemote("invcost")->MboRemote("stdcost")->MboRemote("avgcost")->MboRemote("oldcost")->MboRemote("lastcost")
		->setWhere("itemnum='$itemnum' and warehouse = '$fromwh' $fromcondval")->getMboSet()->getMbo();
		$unitcost = $invCostSet->getString("stdcost");
		if($unitcost == null) $unitcost =0;
		$linecost = $unitcost*$qty;
		$stdcost = $invCostSet->getString("stdcost");
		$oldcost = $invCostSet->getString("oldcost");
		$lastcost = $invCostSet->getString("lastcost");
		$avgcost = $invCostSet->getString("avgcost");		
			if ($qty > $oldCurbalFrom) {
				if($printjson == null){
					Set::throws(["msg"=>"QtyBiggerThanCurbal"]);
				}else{
					return "QtyBiggerThanCurbal";
				}
			}else{
				if ($qty <= 0) {
					if($printjson == null){
						Set::throws(["msg"=>"QtyBiggerThanCurbal"]);
					}else{
						return "QtyBiggerThanCurbal";
					}
					
				}else{
					if ($qty <= $oldCurbalFrom && $qty > 0) {
						$newexpdate = null;
						if($newCurbalFrom==0){
							$newexpdate = null;
						}else{
							$newexpdate = $expdate;
						}
						$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse = '$fromwh' $fromrackval $fromcondval")
						->setValue("curbal", $newCurbalFrom)->setValue("oldcurbal", $oldCurbalFrom)->setValue("expdate", $newexpdate)->change();
						
						//TO WAREHOUSE
						$invbalanceSetR = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse = '$towh' $torackval $fromcondval")
						->MboRemote("curbal")->MboRemote("racknum")->getMboSet();
						
						$invbalancR = $invbalanceSetR->getMbo();
						
						$oldCurbalTo = $invbalancR->getString("curbal");
						$isRackExist = $invbalancR->getString("racknum");
						
						$countrack= $invbalanceSetR->counts();
						if ($countrack>0) {
							//CHANGE
							$newCurbalTo = $oldCurbalTo + $qty;
							$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse = '$towh' $torackval $fromcondval")
							->setValue("curbal", $newCurbalTo)->setValue("expdate", $expdate)->change();
						}else {
							//ADD
							$set->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $condtype)->setValue("warehouse", $towh)
								->setValue("racknum", $torack)->setValue("curbal", $qty)->setValue("oldcurbal", $qty)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
								->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
								->setValue("expdate", $expdate)->add();
								
							$isExistInvCost = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$towh' $fromcondval")->MboRemote("itemnum")->getMboSet()->counts();
							if ($isExistInvCost == 0) {
								$conditionrate = Xbo::getConditionRate($condtype, "itemcondition", $itemnum, $fromwh);
								$glaccount = Xbo::getGLAccount($itemnum);
								$shrinkageacc = Xbo::getShrinkageAcct($towh, $orgcode, $sitecode);
								$invcostadjacc = Xbo::getInvCostAdjAcct($towh, $orgcode, $sitecode);
								$set->MboSetRemote("invcost")->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("itemnum", $itemnum)->setValue("warehouse", $towh)
								->setValue("conditiontype", $condtype)->setValue("conditionrate", $conditionrate)->setValue("stdcost", $stdcost)->setValue("avgcost", $avgcost)
								->setValue("oldcost", $oldcost)->setValue("lastcost", $lastcost)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)
								->setValue("invcostadjacc", $invcostadjacc)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
							}
						}
						
						$transtype = "TRANSFER";
						$financialperiod = Xbo::getFinancialPeriod();
						//add history transaction between warehouse;
						$set->MboSetRemote("matrectrans")->setValue("itemnum", $itemnum)->setValue("description", $description)
						->setValue("warehouse", $fromwh)->setValue("towarehouse", $towh)->setValue("racknum", $fromrack)->setValue("toracknum", $torack)
						->setValue("conditiontype", $condtype)->setValue("quantity", $qty)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
						->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("transtype", $transtype)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("financialperiod", $financialperiod)->setValue("actualdate", $actualdate)
						->add();
						
						if ($countinvItemnum  == 0) {
							//add item if inventory does not exist;
							$set->MboSetRemote("inventory")
								->setValue("itemnum", $itemnum)->setValue("warehouse", $towh)
								->setValue("racknum", $torack)->setValue("reorderqty", 0)
								->setValue("leadtime", 0)->setValue("eoqqty", 1)->setValue("safetystock", 0)->setValue("reorder", "Y")
								->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
								->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
						}
						
						$set2 = new Set();
						$invPriceSet = $set2->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse = '$fromwh' $fromcondval")
						->MboRemote("unitprice")->MboRemote("pricecode")->MboRemote("defaultprice")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
						$countInvPrice = $invPriceSet->counts();
							
						if($countInvPrice > 0){
							for($i=0; $i<$countInvPrice; $i++){
								$invPrice = $invPriceSet->getMbo();
								$unitprice = $invPrice->getString("unitprice");
								$pricecode = $invPrice->getString("pricecode");
								$defaultprice = $invPrice->getString("defaultprice");
								$orgcode = $invPrice->getString("orgcode");
								$sitecode = $invPrice->getString("sitecode");
									
								$chkSet = new Set();
								$invpricechkSet = $chkSet->MboSetRemote("invprice")->setWhere("itemnum = '$itemnum' and warehouse = '$towh' and pricecode='$pricecode' and orgcode= '$orgcode' and sitecode= '$sitecode'")
								->MboRemote("pricecode")->getMboSet();
									
								$countinvpricechkSet = $invpricechkSet->counts();
								if($countinvpricechkSet==0){
									$set3 = new Set();
									$invpriceSet = $set3->MboSetRemote("invprice")->setValue("itemnum", $itemnum)->setValue("warehouse", $towh)
									->setValue("pricecode", $pricecode)->setValue("unitprice", $unitprice)->setValue("conditiontype", $condtype)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
									->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->add();
								}
							}
						}	
						XboSet::AdjustConversionStock($itemnum, $fromwh);
						XboSet::AdjustConversionStock($itemnum, $towh);
						if($printjson == null){
							Set::throws(["msg"=>"transfered"]);
						}else{
							return "transfered";
						}
						
					}
				}
			}
	}
	public static function WriteConsignmentInvoice()
	{
		$invoicenum = $_POST["writeconsigninvoice"];
		$itemnum = $_POST["itemnum"];
		$warehouse = $_POST["warehouse"];
		$fromdate = $_POST["fromdate"];
		$todate = $_POST["todate"];
		$conditiontype = $_POST["conditiontype"];
		$consignmenttype = $_POST["consignmenttype"];
		$pricecode = $_POST["pricecode"];
		$tax = $_POST["tax"];
		$quantity = $_POST["consignqty"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$whom = $_POST["whom"];
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		if (empty($conditiontype)) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}
		else {
			$condval = " and conditiontype='$conditiontype' ";
		}
		$set = new Set();
		$itembo = $set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->MboRemote("issueunit")->getMboSet()->getMbo();
		$itemdesc = $itembo->getString("description");
		$saleunit = $itembo->getString("issueunit");
		$costtype = Xbo::getCostType($orgcode);
		$unitcost = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote($costtype)->getMboSet()->getMbo()->getString($costtype);
		$unitprice = $set->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and pricecode='$pricecode' $condval")->MboRemote("unitprice")->getMboSet()
		->getMbo()->getString("unitprice");
		
		
		if($consignmenttype == "customer"){
			if($unitprice == null ){
				Set::throws(["msg" => "unitpricenotfound"]);
			}else{
				
				$unitprofit = $unitprice - $unitcost;
				$lineprofit = $unitprofit * $quantity;
				$taxvalue = $set->MboSetRemote("tax")->setWhere("taxcode = '$tax'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
				$lineprice = $unitprice * $quantity;
				$taxcost = $lineprice * ($taxvalue / 100);
				
				if ($consignmenttype == "customer") {
					$gldebitacct = Xbo::getRevenueAcct($orgcode, $sitecode);
					$glcreditacct = Xbo::getCustomerAcct($whom, $orgcode, $sitecode);
					$set2 = new Set();
					$set2->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("customercode", $whom)->change();
				}
				if ($consignmenttype == "vendor") {
					$gldebitacct = Xbo::getVendorAcct($whom, $orgcode, $sitecode);
					$glcreditacct = Xbo::getRevenueAcct($orgcode, $sitecode);
					$set2->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("vendorcode", $whom)->change();
				}
				$invoicelinenum = Xbo::getLineNumber("invoiceline", "invoicelinenum", "invoicenum='$invoicenum'");
				$set->MboSetRemote("invoiceline")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)
				->setValue("linetype", "item")->setValue("glcreditacct", $glcreditacct)
				->setValue("gldebitacct", $gldebitacct)->setValue("itemnum", $itemnum)->setValue("description", $itemdesc)->setValue("quantity", $quantity)
				->setValue("saleunit", $saleunit)->setValue("unitcost", $unitprice)->setValue("linecost", $lineprice)->setValue("conversionfactor", 1)
				->setValue("tax", $tax)->setValue("taxcost", $taxcost)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
				->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->add();
				
				$set3 = new Set();
				$invoicelinecost = $set3->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
				$invoicechargecost = $set3->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
				$totalcost = $invoicelinecost + $invoicechargecost;
				
				$setupd = new Set();
				$setupd->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("totalcost", $totalcost)->change();
				$set4 = new Set();
				
				if ($consignmenttype == "customer") {
					$matsaleSet = $set->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and transdate>=$fromdate and transdate<=$todate $condval and sonum is null")
					->setValue("invoiced", "Y")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)->change();
					$set->MboSetRemote("invconsign")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
					->setValue("fromdate", $fromdate)->setValue("todate", $todate)->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)->setValue("consignqty", $quantity)
					->setValue("consigntype", $consignmenttype)->setValue("enterby", $enterby)->setValue("changeby", $enterby)->setValue("enterdate", $enterdate)->setValue("changedate", $enterdate)->add();
				}
				
				Set::throws(["msg" => "consignitemadded"]);
			}
		}else{
			if($consignmenttype == "vendor"){
				$gldebitacct = Xbo::getVendorAcct($whom, $orgcode, $sitecode);
				$glcreditacct = Xbo::getRevenueAcct($orgcode, $sitecode);
				$taxvalue = $set->MboSetRemote("tax")->setWhere("taxcode = '$tax'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
				$linecost = $unitcost*$quantity;
				$taxcost = $linecost * ($taxvalue / 100);
				
				$set = new Set();
				$invoicelinenum = Xbo::getLineNumber("invoiceline", "invoicelinenum", "invoicenum='$invoicenum'");
				$set->MboSetRemote("invoiceline")->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)
				->setValue("linetype", "item")->setValue("glcreditacct", $glcreditacct)
				->setValue("gldebitacct", $gldebitacct)->setValue("itemnum", $itemnum)->setValue("description", $itemdesc)->setValue("quantity", $quantity)
				->setValue("orderunit", $saleunit)->setValue("conversionfactor", 1)
				->setValue("tax", $tax)->setValue("taxcost", $taxcost)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
				->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->add();
				
				$set3 = new Set();
				$invoicelinecost = $set3->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
				$invoicechargecost = $set3->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote(" sum(linecost) as linecost ")->getMboSet()->getMbo()->getString("linecost");
				$totalcost = $invoicelinecost + $invoicechargecost;
				
				$set->MboSetRemote("invconsign")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)->setValue("fromdate", $fromdate)
				->setValue("todate", $todate)->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)->setValue("consignqty", $quantity)
				->setValue("consigntype", $consignmenttype)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)
				->add();
				
				$setupd = new Set();
				$setupd->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->setValue("vendorcode", $whom)->setValue("totalcost", $totalcost)->change();
				$set4 = new Set();
				Set::throws(["msg" => "consignitemadded"]);
				
			}
		}
	}
	
	public static function IssueReservedItems(){
		$value = $_POST["issuereserverditems"];
		$countRI = count($value);
		$newmsg = null;
		for($i=0; $i<$countRI; $i++){
			$val = $value[$i];
			$msg = ActionSet::ProcessReservedItems($value[$i]);
			if($msg == "notissued"){
				$newmsg = $msg;
				break;
			}else{
				if($msg == "notexist"){
					$newmsg = $msg;
					break;
				}else{
					if($msg == "notenoughfifoqty"){
						$newmsg = $msg;
						break;
					}else{
						if($msg == "cantfounddefaultprice"){
							$newmsg = $msg;
							$ss = new stdClass();
							$ss->itemnum = $val["itemnum"];
							$ss->warehouse = $val["warehouse"];
							$ss->racknum = $val["racknum"];
							$ss->conditiontype = $val["conditiontype"];
							$ss->msg = $newmsg;
							Set::throws($ss);
							return $ss;
							break;
						}else{
							$newmsg = "issued";
						}
					}
					
				}
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
	public static function mapProcessReservedItems(){
		
		$ss = new stdClass();
		$itemnum = $_POST["mapProcessReservedItems"];
		$wonum = $_POST["wonum"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$conditiontype = $_POST["conditiontype"];
		$pricecode = $_POST["pricecode"];
		$quantity = $_POST["quantity"];
		$issuedqty = $_POST["issuedqty"];
		$curbal = $_POST["curbal"];
		$id = $_POST["id"];
		$mrnum = $_POST["mrnum"];
		if(isset($_POST["invreservenum"])){
			$invreservenum = $_POST["invreservenum"];
		}else{
			$invreservenum = null;
		}
		
		$issuedate = $_POST["issuedate"];
		
		$value = array();
		$value["wonum"] = $wonum;
		$value["itemnum"] = $itemnum;
		$value["warehouse"] = $warehouse;
		$value["racknum"] = $racknum;
		$value["conditiontype"] = $conditiontype;
		$value["pricecode"] = $pricecode;
		$value["quantity"] = $quantity;
		$value["issuedqty"] = $issuedqty;
		$value["id"] = $id;
		$value["mrnum"] = $mrnum;
		$value["issuedate"] = $issuedate;
		$value["invreservenum"] = $invreservenum;
		
		
		$newmsg = null;
		$msg = ActionSet::ProcessReservedItems($value);
		if($msg == "notissued"){
			$newmsg = $msg;
		}else{
			if($msg == "notexist"){
				$newmsg = $msg;
			}else{
				if($msg == "notenoughfifoqty"){
					$newmsg = $msg;
				}else{
					if($msg == "cantfounddefaultprice"){
						$newmsg = $msg;
						$ss->itemnum = $value["itemnum"];
						$ss->warehouse = $value["warehouse"];
						$ss->racknum = $value["racknum"];
						$ss->conditiontype = $value["conditiontype"];
						$ss->msg = $newmsg;
						Set::throws($ss);
						return $ss;
					}else{
						$newmsg = "issued";

					}
				}
				
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
	public static function ProcessReservedItems($value)

	{
		$wonum = $value["wonum"];
		$itemnum = $value["itemnum"];
		$warehouse = $value["warehouse"];
		$racknum = $value["racknum"];
		$conditiontype = $value["conditiontype"];
		$pricecode = $value["pricecode"];
		$quantity = $value["issuedqty"];
		$requestedqty = $value["quantity"];
		$id = $value["id"];
		$mrnum = $value["mrnum"];
		$issuedate = $value["issuedate"];
		
		if (empty($conditiontype)) {
			$condval = " and (conditiontype is null or conditiontype='') ";
		}
		else {
			$condval = " and conditiontype = '$conditiontype' ";
		}
		if (empty($racknum)) {
			$rackval = " and (racknum is null or racknum='') ";
		}
		else {
			$rackval = " and racknum = '$racknum' ";
		}
		$quantityMinus = $quantity * -1;
		$set = new Set();
		$items = $set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->MboRemote("stocktype")->getMboSet()->getMbo();
		
		$itemdesc = $items->getString("description");
		$stocktype = $items->getString("stocktype");
		
		$isconsignment = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse = '$warehouse'")->MboRemote("consignment")->getMboSet()->getMbo()->getString("consignment");
		
		$set2 = new Set();
		//$InvbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("curbal")->MboRemote("expdate")->getMboSet();
		$InvbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("sum(curbal) as curbal")->getMboSet();
					
		$countinvbal = $InvbalanceSet->counts();
		if($countinvbal>0){
			$invbal = $InvbalanceSet->getMbo();
			$curbal = $invbal->getString("curbal");
			//$expireddate = $invbal->getString("expdate");
			
			if ($curbal >= $quantity) {
				$set = new Set();
				$woset = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("assetnum")->MboRemote("location")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
				$orgcode = $woset->getString("orgcode");
				$sitecode = $woset->getString("sitecode");
				$assetnum = $woset->getString("assetnum");
				$location = $woset->getString("location");
				$glcredit = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
				$gldebit = Xbo::getwogl($wonum);
				$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("avgcost")->getMboSet();
				$invcost = $invcostSet->getMbo();
				$avgcost = $invcost->getString("avgcost");
				$linecost = $avgcost * $quantity;
				$enterdate = Xbo::setEnterDate();
				$enterby = Xbo::getLoginUser();
				$newcurbal = $curbal - $quantity;
				$linecostMinus = $linecost * -1;
				$transtype = "ISSUE";
				
				$bo = new Xbo();
				$pricing = $bo->getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype, $pricecode);
				$pricing_msg = $pricing->msg;
				
				if($stocktype == "SELLABLE") {
					
					if($pricing_msg == "cantfounddefaultprice") {
						return "cantfounddefaultprice";
					}
				}
				
				
				$primarydiscount = $pricing->primarydiscount;
				$secondarydiscount = $pricing->secondarydiscount;
				$discountprice = $pricing->discountprice;
				
				$unitprice = $pricing->unitprice;
				$unitpricediscount = $pricing->unitpricediscount;
				
				$lineprice = $unitprice*$quantity;
				$linepricediscount = $unitpricediscount*$quantity;
				
				$unitprofit = $unitprice-$avgcost;
				$unitprofitdiscount = $unitpricediscount-$avgcost;
				
				$lineprofit = $unitprofit*$quantity;
				$lineprofitdiscount = $unitprofitdiscount*$quantity;
						
				$unitcommission = Xbo::GetUnitCommission($itemnum, $warehouse, $pricecode, $conditiontype);
				$linecommission = $unitcommission*$quantity;

				$setin = new Set();
				$matreserv = $setin->MboSetRemote("matusetrans")->setValue("wonum", $wonum)->setValue("mrnum", $mrnum)->setValue("assetnum", $assetnum)->setValue("location", $location)
				->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("quantity", $quantityMinus)
				->setValue("oldcurbal", $curbal)->setValue("newcurbal", $newcurbal)->setValue("unitcost", $avgcost)->setValue("linecost", $linecostMinus)->setValue("enterby", $enterby)
				->setValue("gldebitacct", $gldebit)->setValue("glcreditacct", $glcredit)->setValue("transtype", $transtype)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)
				->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("description", $itemdesc)->setValue("isconsignment", $isconsignment)
				->setValue("unitprice", $unitprice)->setValue("unitpricediscount", $unitpricediscount)
				->setValue("lineprice", $lineprice)->setValue("linepricediscount", $linepricediscount)
				->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)->setValue("actualdate", $issuedate)
				->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
				->setValue("secondarydiscount", $secondarydiscount)->setValue("primarydiscount", $primarydiscount)->setValue("discountprice", $discountprice)
				->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission);
				
				$setf = new Set();
				$fifotrans = $setf->MboSetRemote("matusetrans")->setValue("wonum", $wonum)->setValue("mrnum", $mrnum)->setValue("assetnum", $assetnum)->setValue("location", $location)
				->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $warehouse)
				->setValue("enterby", $enterby)->setValue("gldebitacct", $gldebit)->setValue("glcreditacct", $glcredit)->setValue("transtype", $transtype)
				->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)->setValue("description", $itemdesc)->setValue("isconsignment", $isconsignment)
				->setValue("unitprice", $unitprice)->setValue("unitpricediscount", $unitpricediscount)->setValue("actualdate", $issuedate)
				->setValue("secondarydiscount", $secondarydiscount)->setValue("primarydiscount", $primarydiscount)->setValue("discountprice", $discountprice)
				->setValue("unitcommission", $unitcommission);
				
				//expdate
				$fl = new stdClass();
				$fl->tablename = "matusetrans";
				$fl->transtype = $transtype;
				$fl->itemnum = $itemnum;
				$fl->warehouse = $warehouse;
				$fl->conditiontype = $conditiontype;
				$fl->racknum = $racknum;
				$fl->issuedqty = $quantityMinus;
				$fl->condval = $condval;
				$fl->rackval = $rackval;
				$fl->avgtrans = $matreserv;
				$fl->fifotrans = $fifotrans;
				$fl->unitcost = $avgcost;
				$fl->unitprice = $unitprice;
				$fl->unitpricediscount = $unitpricediscount;
				$fl->unitcommission = $unitcommission;
				//PROCESS RESERVED ITEMS
				$execfifo = XboSet::IssuedFIFOLIFO($fl);

				if($execfifo == "notenoughfifoqty"){
					return "notenoughfifoqty";
				}else{
					$setup = new Set();
					$setup->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->setValue("curbal", $newcurbal)->setValue("oldcurbal", $curbal)
					->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->change();
					$restqty = $requestedqty - $quantity;
					if($restqty <= 0) {
						$setdel = new Set();
						$setdel->MboSetRemote("invreserve")->setWhere("invreserveid=$id")->remove();
					}else{
						$setresv = new Set();
						$setresv->MboSetRemote("invreserve")->setWhere("invreserveid='$id'")->setValue("quantity", $restqty)->change();
					}
					Xbo::calculateWOCost($wonum);
					XboSet::AdjustConversionStock($itemnum, $warehouse);
					return "issued";
				}
				
			}
			else {
				return "notissued";
			}
		}else{
			return "notexist";
		}
		
	}
	public static function UpdateExpiredQuantity()

	{
		
		$iflSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere("expdate is not null")
		->MboRemote("invfifolifocostid")
		->MboRemote("itemnum, warehouse, expdate")
		->getMboSet();
		
		$expiredItems = [];
		while($result = $iflSet->fetch()){
			$todaydate = Xbo::setEnterDate();
			$otodaydate = strtotime($todaydate);
			$expdate = $result["expdate"];
			$oexpdate = strtotime($expdate);
			$odate = $oexpdate - $otodaydate;
			$invfifolifocostid = $result["invfifolifocostid"];
			$itemnum = $result["itemnum"];
			$warehouse = $result["warehouse"];
			if ($odate < 0) {
				$expiredItems[] = [
					"itemnum" => $itemnum
				];
				(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("invfifolifocostid='$invfifolifocostid'")->setValue("isexpired", "Y")->change();
			}
			else {
				(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")->setValue("isexpired", "N")->change();
			}
			ActionSet::SyncMultiConversion($itemnum, $warehouse);
			XboSet::AdjustConversionStock($itemnum, $warehouse);
		}
		
		Super::sendJsonResponse("success", "Expired Item has been updated", $expiredItems);
	}
	public static function ReturnCustomerBalance(){
		$value = $_POST["retcustbal"];
		$countdp = count($value);
		$msg =null;
		for($i=0; $i<$countdp; $i++){
			$msg = ActionSet::ProcessReturnCustomerBalance($value[$i]);
			if($msg == "quantityexceed"){
				break;
			}else{
				if($msg == "belowzeroisnotallowed"){
					
					break;
				}else{
					if($msg == "returned"){
						continue;
					}
				}
			}
		}
		Set::throws(["msg"=>$msg]);
	}
	public static function ProcessReturnCustomerBalance($value){
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$changeby = $enterby;
		$changedate = $enterdate;
		$todaydate = $enterdate;
		$otodaydate = strtotime($todaydate);	
		$financialperiod = Xbo::getFinancialPeriod();
		$isexpired = null;
		
		$custbalanceid = $value["custbalanceid"];
		$retQty = $value["retqty"];
		$towarehouse = $value["warehouse"];
		$toracknum = $value["racknum"];
		$actualdate = $value["actualdate"];
		
		
		$whset = new Set();
		$warehouseSet = $whset->MboSetRemote("warehouse")->setWhere("warehouse='$towarehouse'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
		$orgcode = $warehouseSet->getString("orgcode");
		$sitecode = $warehouseSet->getString("sitecode");
		
		$set = new Set();
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->MboRemote("customercode")->MboRemote("wonum")->MboRemote("itemnum")
						->MboRemote("expdate")->MboRemote("curbal")->MboRemote("issuedqty")->MboRemote("droppedqty")->MboRemote("conditiontype")->MboRemote("fromwarehouse")
						->MboRemote("fromracknum")->MboRemote("costdate")->MboRemote("batchnum")->getMboSet();
						
		$custbalance = $custbalanceSet->getMbo();
			
		$customercode = $custbalance->getString("customercode");
		$wonum = $custbalance->getString("wonum");
		$itemnum = $custbalance->getString("itemnum");
		$expdate = $custbalance->getString("expdate");
		$custcurbal = $custbalance->getString("curbal");
		$issuedqty = $custbalance->getString("issuedqty");
		$droppedqty = $custbalance->getString("droppedqty");
		$conditiontype = $custbalance->getString("conditiontype");
		$fromwarehouse = $custbalance->getString("fromwarehouse");
		$fromracknum = $custbalance->getString("fromracknum");
		$costdate = $custbalance->getString("costdate");
		$batchnum = $custbalance->getString("batchnum");
		
		$set = new Set();
		$woSet = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("assetnum")->MboRemote("location")->getMboSet();
		$wo = $woSet->getMbo();
		
		$assetnum = $woSet->getString("assetnum");
		$location = $woSet->getString("location");
		
		if($expdate != null ) $oexpdate = strtotime($expdate);
			
		$setcn = new Set();
		$isconsignment = $setcn->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse = '$fromwarehouse'")->MboRemote("consignment")->getMboSet()->getMbo()->getString("consignment");
		//get item description
		$itemSet = new Set();
		$itemdesc = $itemSet->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
		//end item description
			
		$rackval=null; $condval=null;
		if (empty($conditiontype)) $condval = "and (conditiontype='' or conditiontype is null)";
		else $condval = " and conditiontype='$conditiontype' ";
		
		if (empty($toracknum)) $rackval = "and (racknum='' or racknum is null)";
		else $rackval = " and racknum='$toracknum' ";	
		
		if($expdate == null) $dateval = null;
		else $dateval =Super::whereDate("expdate", $expdate);
		
		if($costdate == null) $costdateval = null;
		else $costdateval = Super::whereDate("costdate", $costdate);
			
			if($retQty>0){
			
				if($retQty<=$custcurbal){
					
					$si = new Set();
					$inventorySet = $si->MboSetRemote("inventory")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' ")->getMboSet();
					$inv = $inventorySet->counts();
					
					//GET LAST COST TO BE ADDED AS AVERAGE COST IN THE NEW WAREHOUSE
					$st = new Set();
					$matusetransSet = $st->MboSetRemote("matusetrans")->setWhere("itemnum='$itemnum' and wonum='$wonum' and warehouse = '$fromwarehouse' and transtype='issue' $condval $costdateval")
					->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("unitprofit")->MboRemote("unitprofitdiscount")
					->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")->MboRemote("issuetype")
					->getMboSet();
					
					$matuse = $matusetransSet->getMbo();
					$unitcost = $matuse->getString("unitcost");
					//get price
					$unitprice = $matuse->getString("unitprice");
					if(empty($unitprice)) $unitprice = 0;	
					$unitpricediscount = $matuse->getString("unitpricediscount");
					if(empty($unitpricediscount)) $unitpricediscount = 0;	
					$unitprofit = $matuse->getString("unitprofit");
					if(empty($unitprofit)) $unitprofit = 0;	
					$unitprofitdiscount = $matuse->getString("unitprofitdiscount");
					if(empty($unitprofitdiscount)) $unitprofitdiscount = 0;	
					$discountprice = $matuse->getString("discountprice");
					if(empty($discountprice)) $discountprice = 0;	
					$primarydiscount = $matuse->getString("primarydiscount");
					if(empty($primarydiscount)) $primarydiscount = 0;
					$secondarydiscount = $matuse->getString("secondarydiscount");
					if(empty($secondarydiscount)) $secondarydiscount = 0;
					$issuetype = $matuse->getString("issuetype");
					if(empty($issuetype)){
						$issuetype = InventoryCostType($itemnum, $fromwarehouse, $orgcode);
					}
					//GET GL ACCOUNT FROM THE LAST WAREHOUSE
					$st2 = new Set();
					$warehouseSet = $st2->MboSetRemote("warehouse")->setWhere("warehouse='$fromwarehouse' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("controlacc")->MboRemote("shrinkageacc")
					->MboRemote("invcostadjacc")->getMboSet()->getMbo();
					$controlacc = $warehouseSet->getString("controlacc");
					$shrinkageacc = $warehouseSet->getString("shrinkageacc");
					$invcostadjacc = $warehouseSet->getString("invcostadjacc");
					$glaccount = Xbo::getGLAccount($itemnum);
				
					$st2 = new Set();
					$conditionrate = $st2->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' $condval")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
					
					if($inv == 0){
						$si = new Set();
						$si->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
						->setValue("eoqqty", 1)->setValue("reorder", "Y")->add();
						
						if($conditionrate < 100){
							$se = new Set();
							$invcostSet =$se->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and conditionrate='100' and warehouse='$towarehouse'")->getMboSet();
							$ca = $invcostSet->counts();
							if($ca == 0){
								$st2 = new Set();
								$conditiontype100 = $st2->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditionrate=100")->MboRemote("conditiontype")->getMboSet()->getMbo()->getString("conditiontype");
						
								$unitcost100 = ($unitcost*100)/$conditionrate;
								$se = new Set();
								$se->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("conditiontype", $conditiontype100)
								->setValue("conditionrate", 100)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
								->setValue("stdcost", $unitcost100)->setValue("avgcost", $unitcost100)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
							}else{}
						}
						
						$sc = new Set();
						$sc->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("conditiontype", $conditiontype)
						->setValue("conditionrate", $conditionrate)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
						->setValue("stdcost", $unitcost)->setValue("avgcost", $unitcost)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
					}
					
					$set2= new Set();
					$invbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' $condval $rackval $dateval")->MboRemote("itemnum")
									->MboRemote("curbal")->getMboSet();
					$invbalance = $invbalanceSet->getMbo();
					$countinvbal = $invbalanceSet->counts();
					$oldcurbal = $invbalanceSet->getString("curbal");
					if(empty($oldcurbal))$oldcurbal=0;
					
					if($expdate != null ){
						$odate = $oexpdate - $otodaydate;
						if ($odate < 0) $isexpired = "Y";
						else $isexpired = "N";
					}else{
						$isexpired = 'N';
					}
					$set3 = new Set();
					if($countinvbal==0){
						$newcurbal = $retQty;
						$set3 = $set3->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $towarehouse)
								->setValue("racknum", $toracknum)->setValue("curbal", $newcurbal)->setValue("oldcurbal", $newcurbal)->setValue("enterby", $enterby)
								->setValue("enterdate", $enterdate)->setValue("expdate", $expdate)->setValue("isexpired", $isexpired)->setValue("changeby", $changeby)
								->setValue("changedate", $changedate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
					}else{
						$newcurbal = $retQty+$oldcurbal;
						$set3 = $set3->MboSetRemote("invbalance")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' $condval $rackval $dateval")
								->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)
								->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->change();
					}
					//update custbalance curbal
					$newcustcurbal = $custcurbal-$retQty;
					$newdroppedqty = $droppedqty-$retQty;
					if($newcustcurbal==0 && $issuedqty <= 0){
						$set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->remove();
					}else{
						$set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->setValue("curbal", $newcustcurbal)->setValue("droppedqty", $newdroppedqty)->change();
					}
					//end update custbalance;		
					//insert matusetrans;
					$set5 = new Set();
					$quantity = $retQty;
					$set6 = new Set();
					
					$unitcost = Xbo::getAVGCost($itemnum, $towarehouse, $conditiontype);
					$linecost = $quantity*$unitcost;
					$transtype = "RETURN";
					$glcreditacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode); 
					$gldebitacct = Xbo::getGLAccount($itemnum);
					
					$lineprice = $unitprice*$quantity;			
					$linepricemin = $lineprice*-1;
					
					$linepricediscount = $unitpricediscount*$quantity;
					$linepricediscountmin = $linepricediscount*-1;
					
					$lineprofit = $unitprofit*$quantity;	
					$lineprofitmin = $lineprofit*-1;
					
					$lineprofitdiscount = $unitprofitdiscount*$quantity;	
					$lineprofitdiscountmin = $lineprofitdiscount*-1;
					
					$discountpricemin = $discountprice*-1;
					$primarydiscount = $matuse->getString("primarydiscount");
					$secondarydiscount = $matuse->getString("secondarydiscount");
					
					$issuetype = $matuse->getString("issuetype");
					
					
					$set5->MboSetRemote("matusetrans")->setValue("wonum", $wonum)->setValue("description", $itemdesc)->setValue("itemnum", $itemnum)->setValue("expireddate", $expdate)
						->setValue("conditiontype", $conditiontype)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)->setValue("quantity", $quantity)
						->setValue("oldcurbal", $oldcurbal)->setValue("newcurbal", $newcurbal)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
						->setValue("transtype", $transtype)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("orgcode", $orgcode)
						->setValue("sitecode", $sitecode)->setValue("isconsignment", $isconsignment)->setValue("costdate", $costdate)
						->setValue("unitprice", $unitprice)->setValue("lineprice", $linepricemin)->setValue("unitpricediscount", $unitpricediscount)
						->setValue("linepricediscount", $linepricediscountmin)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofitmin)
						->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscountmin)->setValue("discountprice", $discountpricemin)
						->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)
						->setValue("issuetype", $issuetype)->setValue("actualdate", $actualdate)->setValue("assetnum", $assetnum)->setValue("location", $location)
						->add();
							
							
					//end matusetrans
					$ob = new stdClass();
					$ob->itemnum = $itemnum;
					$ob->towarehouse = $towarehouse;
					$ob->toracknum = $toracknum;
					$ob->conditiontype = $conditiontype;
					$ob->enterby = $enterby;
					$ob->enterdate = $enterdate;
					$ob->orgcode = $orgcode;
					$ob->sitecode = $sitecode;
					$ob->quantity = $quantity;
					$ob->costdate = $costdate;
					$ob->financialperiod = $financialperiod;
					$ob->conditionrate = $conditionrate;
					$ob->unitcost = $unitcost;
					$ob->batchnum = $batchnum;
					$ob->expdate = $expdate;
					$ob->retqty = $quantity;
					$ob->fromracknum = $fromracknum;
					ActionSet::InsertOrUpdateINVFIFOLIFOCOST($ob);
					
					//sumwocost
					$sumActualCost = Xbo::sumWOActualCost($wonum);
					$sumPlanCost = Xbo::sumWOPlanCost($wonum);
					$variancecost = $sumPlanCost-$sumActualCost;
					$set5->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("actualcost", $sumActualCost)
					->setValue("plannedcost",$sumPlanCost)->setValue("variancecost", $variancecost)->change();
					XboSet::AdjustConversionStock($itemnum, $towarehouse);
					
					
					return "returned";
				}else{
					if($retQty>$custcurbal){
						return "quantityexceed";
					}
				}
			}else{
				if($retQty<=0){
					return "belowzeroisnotallowed";
				}
			}
	}
} 


Class Charts {
	
	public static function ROPAnalysis(){
		$payload = Super::getParam();
		$startdate = $payload['startdate'] ?? null;
		$enddate = $payload['enddate'] ?? null;
		
		
		if (empty($startdate) || empty($enddate)) {
			ActionSet::sendJsonResponse("error", "Start Date or End Date cannot be empty.");
			return;
		}
		
		// Hitung jumlah bulan dalam rentang waktu
		$date1 = new DateTime($startdate);
		$date2 = new DateTime($enddate);
		$interval = $date1->diff($date2);
		$months_in_range = $interval->y * 12 + $interval->m + ($interval->d / 30.44);
		if ($months_in_range < 1) $months_in_range = 1; // Hindari pembagian dengan nol
		
		$chartData = [
			'categories' => [], // Item names for x-axis
			'currentROP' => [],
			'recommendedROP' => [],
			'currentSS' => [],
			'recommendedSS' => [],
			'justifications' => [] // For tooltip data
		];
		
		$set = new Set();
		$invSet = $set->MboSetRemote("inventory")
		->setRelationship("item", "inventory.itemnum = item.itemnum")
		->MboRemote("inventory.itemnum")
		->MboRemote("item.description")
		->MboRemote("warehouse")
		->MboRemote("reorderqty")
		->MboRemote("safetystock")
		->getMboSet();
			
		if($invSet->isEmpty()){
			ActionSet::sendJsonResponse("error", "Inventory data not found");
			return;
		}
		
		// ===============
		// 1: AMBIL SEMUA DATA PERMINTAAN (DEMAND)
		// ===================
		$setDemand = new Set();
		$allDemandData = $setDemand->MboSetRemote("matusetrans")
			// Ganti 'where' yang rawan dengan yang aman. Tidak ada lagi filter per item.
			->where("actualdate BETWEEN ? AND ? AND transtype='ISSUE'", $startdate, $enddate)
			->MboRemote("itemnum, ABS(SUM(quantity)) as totalIssuedQty, DATE_FORMAT(actualdate, '%Y-%m') as issue_month")
			->group("itemnum, issue_month") // Group berdasarkan item DAN bulan
			->getMboSet();

		// Siapkan dictionary untuk menyimpan data permintaan per item
		$demandDataByItem = [];
		while ($demandRow = $allDemandData->fetch()) {
			$itemnum = $demandRow['itemnum'];
			$qty = floatval($demandRow['totalIssuedQty']);

			// Jika item ini belum ada di dictionary, buatkan entri baru
			if (!isset($demandDataByItem[$itemnum])) {
				$demandDataByItem[$itemnum] = [
					'total_consumption' => 0,
					'monthly_consumption' => []
				];
			}
			// Tambahkan data ke kamus
			$demandDataByItem[$itemnum]['total_consumption'] += $qty;
			$demandDataByItem[$itemnum]['monthly_consumption'][] = $qty;
		}

		// ==================
		// 2: AMBIL SEMUA DATA LEAD TIME
		// ========
		$setLeadtime = new Set();
		$allLeadtimeData = $setLeadtime->MboSetRemote("matrectrans mt")
			->setRelationship("po", "mt.ponum=po.ponum")
			->setRelationship("poline pl", "mt.ponum=pl.ponum AND mt.polinenum=pl.polinenum AND po.ponum = pl.ponum")
			->where("mt.enterdate BETWEEN ? AND ?", $startdate, $enddate)
			->MboRemote("mt.itemnum, AVG(DATEDIFF(mt.actualdate, po.orderdate)) as actual_avg_leadtime, MAX(DATEDIFF(mt.actualdate, po.orderdate)) as actual_max_leadtime")
			->group("mt.itemnum")
			->getMboSet();
			
		// Siapkan "kamus" untuk menyimpan data lead time per item
		$leadtimeDataByItem = [];
		while ($leadtimeRow = $allLeadtimeData->fetch()) {
			$itemnum = $leadtimeRow['itemnum'];
			$leadtimeDataByItem[$itemnum] = [
				'avg_leadtime' => floatval($leadtimeRow['actual_avg_leadtime']),
				'max_leadtime' => floatval($leadtimeRow['actual_max_leadtime'])
			];
		}

		// =========
		// 3: LOOP
		// ============
		$setInv = new Set();
		$invSet = $setInv->MboSetRemote("inventory iv")
			->MboRemote("iv.itemnum")->MboRemote("iv.warehouse")->MboRemote("iv.reorderqty")->MboRemote("iv.safetystock")->MboRemote("it.description")
			->setRelationship("item it", "iv.itemnum = it.itemnum")
			->getMboSet();
			
		if($invSet->isEmpty()){
			ActionSet::sendJsonResponse("error", "Inventory data not found");
			return;
		}

		while ($inv = $invSet->fetch()) {
			$currentItemnum = $inv['itemnum'];
			$warehouse = $inv['warehouse'];
			$current_rop = floatval($inv['reorderqty']);
			$current_ss = floatval($inv['safetystock']);
			$description = $inv['description'];
			
			$demandInfo = $demandDataByItem[$currentItemnum] ?? ['total_consumption' => 0, 'monthly_consumption' => []];
			$totalIssuedQty = $demandInfo['total_consumption'];
			$monthly_consumption = $demandInfo['monthly_consumption'];
			
			// Ambil data lead time dari kamus
			$leadtimeInfo = $leadtimeDataByItem[$currentItemnum] ?? null;
			$actual_avg_leadtime_days = $leadtimeInfo ? $leadtimeInfo['avg_leadtime'] : 30; // Default 30 hari
			$actual_max_leadtime_days = $leadtimeInfo ? $leadtimeInfo['max_leadtime'] : $actual_avg_leadtime_days * 1.5;

			// ===============
			// LOGIKA KALKULASI 
			// =============
			
			$avg_consumption_monthly = ($months_in_range > 0) ? $totalIssuedQty / $months_in_range : 0;
			$max_consumption_monthly = !empty($monthly_consumption) ? max($monthly_consumption) : $avg_consumption_monthly;
			
			$safety_stock_units = ($max_consumption_monthly / 30.44 * $actual_max_leadtime_days) - ($avg_consumption_monthly / 30.44 * $actual_avg_leadtime_days);
			$recommended_ss = ceil(max(0, $safety_stock_units));

			$demand_during_leadtime = ($avg_consumption_monthly / 30.44) * $actual_avg_leadtime_days;
			$recommended_rop = ceil($demand_during_leadtime + $recommended_ss);
					// Apply minimum rules
					if ($totalIssuedQty > 0) {
						if ($recommended_ss < 1) {
							$recommended_ss = 1;
						}
						
						$recalculated_rop = $recommended_ss + ceil($avg_consumption_monthly);
						if ($recommended_rop < $recalculated_rop) {
							$recommended_rop = $recalculated_rop;
						}
					}
					
					if ($recommended_rop <= $recommended_ss) {
						$recommended_rop = $recommended_ss + 1;
					}
					
					// =================================================================================
					// 5. CREATE JUSTIFICATION TEXT
					// =================================================================================
					$justification = [
						'item' => $currentItemnum,
						'description' => $description,
						'warehouse' => $warehouse,
						'analysis_period' => round($months_in_range, 1) . ' months',
						'total_consumption' => round($totalIssuedQty, 2),
						'avg_monthly_consumption' => round($avg_consumption_monthly, 2),
						'max_monthly_consumption' => round($max_consumption_monthly, 2),
						'avg_leadtime_days' => round($actual_avg_leadtime_days, 1),
						'max_leadtime_days' => round($actual_max_leadtime_days, 1),
						'demand_during_leadtime' => round($demand_during_leadtime, 2),
						'safety_stock_calculation' => round($safety_stock_units, 2),
						'rop_justification' => "ROP = Demand during lead time ({$demand_during_leadtime}) + Safety Stock ({$recommended_ss})",
						'ss_justification' => "SS = (Max consumption/day × Max lead time) - (Avg consumption/day × Avg lead time)",
						'current_vs_recommended' => [
							'current_rop' => $current_rop,
							'current_ss' => $current_ss,
							'rop_change' => $recommended_rop - $current_rop,
							'ss_change' => $recommended_ss - $current_ss
						]
					];
					
					// Add to chart data
					
					$chartData['categoriesDetailed'][] = ["currentItemnum" => $currentItemnum, "desc"=>substr($description, 0, 20)];
					$chartData['warehouse'][] = $warehouse;
					$chartData['description'][] = $description;
					$chartData['currentROP'][] = $current_rop;
					$chartData['recommendedROP'][] = $recommended_rop;
					$chartData['currentSS'][] = $current_ss;
					$chartData['recommendedSS'][] = $recommended_ss;
					$chartData['justifications'][] = $justification;
				}
				
		// =================================================================================
		// 6. PREPARE RESPONSE FOR JAVASCRIPT FUNCTION
		// =================================================================================
		
		// Prepare charts data in the format expected by your generateROPChart function
		$charts = [
			'title' => [
				'text' => 'ROP & Safety Stock Analysis',
				'subtext' => "Period: {$startdate} to {$enddate}",
				'left' => 'center',
				'textStyle' => [
					'fontSize' => 20,
					'color' => '#2c3e50'
				]
			],
			'tooltip' => [
				'trigger' => 'axis',
				'backgroundColor' => 'rgba(255, 255, 255, 0.95)',
				'borderColor' => '#dedede',
				'borderWidth' => 1,
				'textStyle' => [
					'color' => '#000',
					'fontSize' => 12
				],
				'axisPointer' => [
					'type' => 'cross',
					'label' => [
						'backgroundColor' => '#667eea'
					]
				]
			],
			'legend' => [
				'data' => ['Current ROP', 'Recommended ROP', 'Current Safety Stock', 'Recommended Safety Stock'],
				'top' => '8%',
				'textStyle' => [
					'fontSize' => 12,
					'color' => '#2c3e50'
				]
			],
		  
			'toolbox' => [
				'show' => true,
				'orient' => 'horizontal',
				'left' => 'right',
				'top' => '8%',
				'feature' => [
					'mark' => [
						'show' => true,
						'title' => [
							'mark' => 'Mark',
							'markUndo' => 'Undo',
							'markClear' => 'Clear'
						]
					],
					'dataView' => [
						'show' => true,
						'readOnly' => false,
						'title' => 'Data View',
						'lang' => ['Data View', 'Close', 'Refresh']
					],
					'magicType' => [
						'show' => true,
						'type' => ['line', 'bar', 'stack'],
						'title' => [
							'line' => 'Line Chart',
							'bar' => 'Bar Chart',
							'stack' => 'Stacked Chart'
						]
					],
					'restore' => [
						'show' => true,
						'title' => 'Reset'
					],
					'saveAsImage' => [
						'show' => true,
						'title' => 'Save as Image',
						'type' => 'png',
						'name' => 'ROP_SafetyStock_Analysis'
					]
				],
				'iconStyle' => [
					'borderColor' => '#666'
				],
				'emphasis' => [
					'iconStyle' => [
						'borderColor' => '#3E98C5'
					]
				]
			],
		   
				'dataZoom' => [
					[
						'show' => true,
						'realtime' => true,
						'start' => 30, // Mulai dari 30% 
						'end' => 70, 
						'xAxisIndex' => [0],
						'type' => 'slider',
						'bottom' => '5%',
						'height' => 20,
						'handleIcon' => 'path://M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23.1h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
						'handleSize' => '80%',
						'handleStyle' => [
							'color' => '#fff',
							'shadowBlur' => 3,
							'shadowColor' => 'rgba(0, 0, 0, 0.6)',
							'shadowOffsetX' => 2,
							'shadowOffsetY' => 2
						],
						'textStyle' => [
							'color' => '#333'
						],
						'borderColor' => '#90979c'
					],
					[
						'type' => 'inside',
						'realtime' => true,
						'start' => 70, // Zoom 30% dari data (menampilkan 30% terakhir)
						'end' => 100,
						'xAxisIndex' => [0],
						'zoomOnMouseWheel' => true,
						'moveOnMouseMove' => true,
						'moveOnMouseWheel' => false
					]
				],
				'grid' => [
					'left' => '3%',
					'right' => '4%',
					'bottom' => '12%', // Diperbesar untuk memberi ruang pada dataZoom slider
					'top' => '18%',
					'containLabel' => true
				],
				'xAxis' => [
					'type' => 'category',
					'boundaryGap' => false,
					//'data' => $chartData['categories'],
					'data' => array_map(function($item) {
						return "{$item['currentItemnum']}\n{$item['desc']}";
					}, $chartData['categoriesDetailed']),
					'axisLabel' => [
						'rotate' => 45,
						'interval' => 0,
						'textStyle' => [
							'fontSize' => 11
						]
					]
				],
				'yAxis' => [
					'type' => 'value',
					'name' => 'Quantity',
					'nameTextStyle' => [
						'color' => '#666',
						'fontSize' => 12
					],
					'axisLabel' => [
						'formatter' => '{value}'
					]
				],
				'series' => [
					
					[
						'name' => 'Current ROP',
						'type' => 'line',
						'data' => $chartData['currentROP'],
						'lineStyle' => [
							'color' => '#ff7f0e', 
							'width' => 3
						],
						'itemStyle' => [
							'color' => '#ff7f0e'
						],
						'symbol' => 'circle',
						'symbolSize' => 8,
						'smooth' => true
					],
					[
						'name' => 'Recommended ROP',
						'type' => 'line',
						'data' => $chartData['recommendedROP'],
						'lineStyle' => [
							'color' => '#2ca02c', 
							'width' => 3
						],
						'itemStyle' => [
							'color' => '#2ca02c'
						],
						'symbol' => 'circle',
						'symbolSize' => 8,
						'smooth' => true
					],
					[
						'name' => 'Current Safety Stock',
						'type' => 'line',
						'data' => $chartData['currentSS'],
						'lineStyle' => [
							'color' => '#d62728', 
							'width' => 3, 
							'type' => 'dashed'
						],
						'itemStyle' => [
							'color' => '#d62728'
						],
						'symbol' => 'triangle',
						'symbolSize' => 8,
						'smooth' => true
					],
					[
						'name' => 'Recommended Safety Stock',
						'type' => 'line',
						'data' => $chartData['recommendedSS'],
						'lineStyle' => [
							'color' => '#1f77b4', 
							'width' => 3, 
							'type' => 'dashed'
						],
						'itemStyle' => [
							'color' => '#1f77b4'
						],
						'symbol' => 'triangle',
						'symbolSize' => 8,
						'smooth' => true
					]
				]
			];
		
		$response = [
			'status' => 'success',
			'message' => 'ROP Analysis completed successfully',
			'data' => [
				'items' => $chartData['categories'],
				'warehouse' => $chartData['warehouse'],
				'description' => $chartData['description'],
				'current_rop' => $chartData['currentROP'],
				'recommended_rop' => $chartData['recommendedROP'],
				'current_ss' => $chartData['currentSS'],
				'recommended_ss' => $chartData['recommendedSS'],
				'justifications' => $chartData['justifications'],
				'summary' => [
					'total_items_analyzed' => count($chartData['categories']),
					'analysis_period' => $months_in_range . ' months',
					'date_range' => $startdate . ' to ' . $enddate
				]
			],
			'charts' => $charts 
		];
		
		echo json_encode($response);
		
	}
	public static function ABCAnalysis(){
		
		$zora_data = new stdClass; 
		$startDate = $_POST["startdate"];
        $endDate = $_POST["enddate"];

        $conn = DB::getMXConnection();
		
		

        $startDate = mysqli_real_escape_string($conn, $startDate);
        $endDate = mysqli_real_escape_string($conn, $endDate);
		$set = new Set();
		$items = []; 

		$itemSet = $set->MboSetRemote("item i")
			->setRelationship("matusetrans m", "i.itemnum = m.itemnum")
			->where("m.enterdate BETWEEN ? AND ?", $startDate, $endDate)
			->MboRemote(
				"i.itemnum, " .
				"i.description, " .
				"i.marketrarityranking, " .
				"i.criticalityranking, " .
				"i.usageranking, " .
				"SUM(m.quantity) AS issueqty, " .
				"m.unitcost, " .
				"SUM(m.quantity * m.unitcost) * -1 AS usage_value"
			)
			->group("i.itemnum, i.description, m.unitcost, i.marketrarityranking, i.criticalityranking, i.usageranking")
			->order("usage_value DESC")
			->getMboSet(); 

		while ($row = $itemSet->fetch()) {
			$items[] = $row;
		}
        usort($items, function($a, $b) {
            return $b['usage_value'] - $a['usage_value']; 
        });
		
        $totalValue = 0;
        foreach ($items as $item) {
            $totalValue += floatval($item['usage_value']);
        }

        if (count($items) == 0) {
            echo json_encode(['data' => [], 'message' => 'No data found for given date range.']);
            exit;
        }

        if ($totalValue == 0) {
            echo json_encode(['data' => [], 'message' => '0 Usage Value. Make sure the data is correct.']);
            exit;
        }
        $cumulativePercentageValue = 0;
        $cumulativePercentageItems = 0;
        $numItems = count($items);
        $results = [];
		
		// Data untuk Chart ECharts
        $chartCategories = []; // Kategori (misalnya, nama item)
        $chartValues = [];     // Nilai (Cumulative Percentage Value)


        // Hitung persentase dan kumulatif *sebelum* menentukan ranking
        // Kumpulkan semua itemnum yang akan di proses
		
		$itemnums = array_column($items, 'itemnum');

		// Jika tidak ada item, tidak perlu lanjut
		if (empty($itemnums)) {
			// return atau Set::throws([]);
			return;
		}
		$itemnumList = "'" . implode("', '", $itemnums) . "'"; // Aman karena dari data internal

		// AMBIL SEMUA DATA FIFO/LIFO DALAM SATU TEMBAKAN
	
		$setFifo = new Set();
		$allFifoData = $setFifo->MboSetRemote("invfifolifocost ivl")
			->setRelationship("inventory iv", "ivl.itemnum = iv.itemnum and ivl.warehouse = iv.warehouse")
			// Ganti filter per item menjadi filter untuk SEMUA item sekaligus
			->setWhere("ivl.itemnum IN ($itemnumList)")
			->group("ivl.itemnum, ivl.warehouse")
			->MboRemote("ivl.itemnum, ivl.warehouse, iv.safetystock, iv.leadtime, sum(ivl.quantity) as total_qty")
			->getMboSet();

		// Siapkan kamus data FIFO/LIFO, dengan struktur [itemnum][warehouse]
		$fifoDataByItem = [];
		while ($fifoRow = $allFifoData->fetch()) {
			$itemnum = $fifoRow['itemnum'];
			$warehouse = $fifoRow['warehouse'];
			$fifoDataByItem[$itemnum][$warehouse] = [
				'total_qty' => floatval($fifoRow['total_qty']),
				'safetystock' => floatval($fifoRow['safetystock']),
				'leadtime' => floatval($fifoRow['leadtime'])
			];
		}

		// AMBIL SEMUA DATA STOCK ON THE WAY DALAM SATU TEMBAKAN

		$setStock = new Set();
		$allStockData = $setStock->MboSetRemote("poline pl")
			->setRelationship("po", "po.ponum = pl.ponum")
			->setRelationship(
				"(SELECT ponum, polinenum, SUM(quantity) as total_received 
				  FROM matrectrans 
				  GROUP BY ponum, polinenum) as received_summary", 
				"pl.ponum = received_summary.ponum AND pl.polinenum = received_summary.polinenum"
			)
			// Ganti filter per item menjadi filter untuk SEMUA item
			->where("pl.itemnum IN ($itemnumList) AND pl.receiptstatus IN ('NONE', 'PARTIAL') AND po.status = 'sent'")
			// Group berdasarkan item untuk mendapatkan total per item
			->group("pl.itemnum")
			->MboRemote("pl.itemnum, SUM(pl.orderqty - IFNULL(received_summary.total_received, 0)) as stockontheway")
			->getMboSet();

		// Siapkan kamus data Stock on the Way
		$stockDataByItem = [];
		while ($stockRow = $allStockData->fetch()) {
			$stockDataByItem[$stockRow['itemnum']] = floatval($stockRow['stockontheway']);
		}

		// LOOP
	
		foreach ($items as $index => $item) {
			$itemnum = $item['itemnum'];
			$stockData = [];

			// --- TIDAK ADA LAGI QUERY DI SINI ---
			
			// Ambil data dari kamus
			$warehousesForItem = $fifoDataByItem[$itemnum] ?? [];
			$stockontheway = $stockDataByItem[$itemnum] ?? 0;
			
			// Loop melalui data warehouse yang sudah ada di memori (BUKAN query)
			foreach ($warehousesForItem as $warehouse => $fifoData) {
				$total_qty = $fifoData['total_qty'];
				$safetystock = $fifoData['safetystock'];
				$leadtime = $fifoData['leadtime'];
				
				$alert_message = "";
				$total_available = $total_qty + $stockontheway;

				// KALKULASI ALERT
			
				if ($total_qty < $safetystock) {
						if ($stockontheway == 0) {
							$alert_message = "CRITICAL: Stock below safety level – Replenishment REQUIRED IMMEDIATELY.";
						} else {
							if ($total_available >= $safetystock) {
								$alert_message = "MONITOR: Stock below safety level, replenishment in transit – Monitor delivery.";
							} else {
								$alert_message = "CRITICAL: Stock below safety level, replenishment in transit – Quantity insufficient. Consider increasing order.";
							}
						}
					} elseif ($total_qty == $safetystock) {
						if ($stockontheway == 0) {
							$alert_message = "WARNING: Stock at safety threshold – Monitor closely. Consider replenishment soon.";
						} else {
							$alert_message = "OK: Stock at safety threshold, replenishment in transit – Monitor stock levels and delivery.";
						}
					} else {
						if ($stockontheway > 0) {
							$alert_message = "INFO: Stock above safety level, replenishment in transit – Monitor for potential overstock.";
						} else {
							$alert_message = "OK: Stock above safety level – No action required.";
						}
					} 
				
				
				
				$stockData[] = [
					'warehouse' => $warehouse,
					'total_qty' => $total_qty,
					'safetystock' => $safetystock,
					'stockontheway' => $stockontheway,
					'leadtime' => $leadtime,
					'alert_message' => $alert_message
				];
			}
			

			$percentageContribution = ($totalValue != 0) ? (floatval($item['usage_value']) / $totalValue) * 100 : 0;
			$cumulativePercentageItems = (($index + 1) / $numItems) * 100;
			
			$results[] = [
				'itemnum' => $itemnum,
				'description' => $item['description'],
				'marketrarityranking' => $item['marketrarityranking'],
				'criticalityranking' => $item['criticalityranking'],
				'usageranking' => $item['usageranking'],
				'percentage_contribution' => round($percentageContribution, 2),
				'cumulative_percentage_value' => round(0, 2),
				'cumulative_percentage_items' => round($cumulativePercentageItems, 2),
				'ranking' => '',
				'stockData' => $stockData // $stockData yang sudah diisi di loop atas
			];

			$chartCategories[] = $item['description'];
		}
				
				
		$classAThreshold = 70;  // Batas untuk Kelas A
        $classBThreshold = 90;  // Batas untuk Kelas B
        $cumulativeValue = 0;

        foreach ($results as &$result) {  
            $cumulativeValue += $result['percentage_contribution'];  
             $result['cumulative_percentage_value'] = round($cumulativeValue, 2); //Update Cumulative Value
            $chartValues[] = round($cumulativeValue, 2); 
            if ($cumulativeValue <= $classAThreshold) {
                $result['ranking'] = 'A';
            } elseif ($cumulativeValue <= $classBThreshold) {
                $result['ranking'] = 'B';
            } else {
                $result['ranking'] = 'C';
            }
			
			$set = new Set();
			$itemnum = $result['itemnum'];
			$usageranking = $result['ranking'];
			$set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->setValue("usageranking",$usageranking )->change();
        }

        $chartResult = [
            'categories' => $chartCategories,
            'values' => $chartValues,
            'type' => 'line'  // Tipe chart
        ];

        header('Content-Type: application/json');
        echo json_encode(['data' => $results, 'charts' => $chartResult]);
        exit;

        mysqli_close($conn);
	}
	
	
	public static function getZoraSummary($results){
    $max_payload_size = 204800; // Example: 200KB (in bytes) - Replace with the actual value
    
    // 1. Prepare data for AI (as discussed before: aggregate, filter, select features)
    $critical_spareparts = [];
    foreach ($results as $result) {
        $stockData = $result['stockData'][0]; // Get stock data (assuming [0] always exists)
        if (($result['ranking'] == 'A' || $result['ranking'] == 'B') && 
            $result['criticalityranking'] == 'A' &&
            strpos($stockData['alert_message'], 'CRITICAL') !== false) {

            $critical_spareparts[] = [
                'itemnum' => $result['itemnum'],
                'description' => $result['description'],
                'percentage_contribution' => $result['percentage_contribution'],
                'marketrarityranking' => $result['marketrarityranking'],
                'usageranking' => $result['usageranking'],
                'total_qty' => $stockData['total_qty'],
                'safetystock' => $stockData['safetystock'],
                'stockontheway' => $stockData['stockontheway'],
                'alert_message' => $stockData['alert_message']
            ];
        }
    }

    // 2. Create the data object for AI (use only critical_spareparts)
    $zora_data = new stdClass;
    $zora_data->critical_spareparts = $critical_spareparts;

    // 3. Check payload size BEFORE sending to AI
    $data_json = json_encode($zora_data);
    if (strlen($data_json) > $max_payload_size) {
        return "Data is too large to be processed. Please reduce the date range or apply filters."; //User friendly message
    } else {
        // 4. Call the AI if the data size is within limits
        $zora = Zora::init();
        if ($zora['status'] === "success") {
            $orchestrator = $zora['init'];
            //Improved Prompt
            $prompt = "You are an expert inventory analyst. Analyze the following list of CRITICAL spare parts (category A or B items with criticality ranking A and a CRITICAL alert message). 
			Provide recommendations for every item to ensure availability, considering lead time, criticality, and market rarity.";
			
            $zora_summary = $orchestrator->summarize($zora_data, $prompt);
			return $zora_summary;
        } else {
            return "Cannot communicate with ZORA.";
        }
    }
}

}

Class LookupUI{
	
	public static function TransferItemUI(){
	
		$wh = $_POST["warehouse"];
		$itemnum = $_POST["transferItemUI"];
		$set = new Set();
		$invbalSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$wh' ")->MboRemote("racknum")->MboRemote("conditiontype")->getMboSet();
		$singlRack = $invbalSet->counts();
		if ($singlRack == 1) {
			$invbal = $invbalSet->getMbo();
			$racknum = $invbal->getString("racknum");
			$conditiontype = $invbal->getString("conditiontype");
			Set::throws(["fromrack"=>$racknum, "conditiontype"=>$conditiontype]);
		}
		else {
			Set::throws(["fromrack"=>null, "conditiontype"=>null]);
		}
	}
	
	public static function IssueCurrentItem(){
	
		$wh = $_POST["warehouse"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$itemnum = $_POST["issuecuritemui"];
		$set = new Set();
		$glcreditacct = Xbo::getwhgl($wh, "noprint");
		$invbalSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$wh' ")->MboRemote("racknum")->MboRemote("conditiontype")->getMboSet();
		$singlRack = $invbalSet->counts();
		if ($singlRack == 1) {
			$invbal = $invbalSet->getMbo();
			$racknum = $invbal->getString("racknum");
			$conditiontype = $invbal->getString("conditiontype");
			Set::throws(["controlacc"=>$glcreditacct, "racknum"=>$racknum, "conditiontype"=>$conditiontype]);
		}
		else {
			Set::throws(["controlacc" => $glcreditacct]);
		}
	}
	public static function DefaultPriceBalance()
	{
		
		$itemnum = $_POST["defpricebalance"];
		$warehouse = $_POST["warehouse"];
		$set = new Set();
		$pricecode = $set->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and defaultprice='Y'")->getMboSet()->getMbo()->getString("pricecode");
		$invbalSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' ")->MboRemote("racknum")->MboRemote("conditiontype")->getMboSet();
		$singlRack = $invbalSet->counts();
		$set2 = new Set();
		$costtype = $set2->MboSetRemote("organization")->setRelationship("inventory", "organization.orgcode = inventory.orgcode")->setWhere(" inventory.itemnum='$itemnum'")
		->MboRemote("organization.costtype")->getMboSet()->getMbo()->getString("costtype");
		
		$datenum = Xbo::setEnterDate();
		$transnum = strtotime($datenum);
		
		$set3 = new Set();
		$orgbankaccountSet = $set3->MboSetRemote("orgbankaccount")->setWhere("isdefault='Y'")->MboRemote("bankaccount")->getMboSet();
		$orgbankaccount = $orgbankaccountSet->getMbo();
		$bankaccount = $orgbankaccount->getString("bankaccount");
		if ($singlRack == 1) {
			$invbal = $invbalSet->getMbo();
			$racknum = $invbal->getString("racknum");
			$conditiontype = $invbal->getString("conditiontype");
			Set::throws(["transnum"=>$transnum, "pricecode"=>$pricecode, "racknum"=>$racknum, "conditiontype"=>$conditiontype, "transtype"=>"SALE", "costtype"=>$costtype, "bankaccount"=>$bankaccount]);
		}
		else {
			Set::throws(["transnum"=>$transnum, "pricecode"=>$pricecode, "racknum"=>null, "transtype"=>"SALE", "costtype"=>$costtype, "conditiontype"=>null, "bankaccount"=>$bankaccount]);
		}
	}
}



Class ValidateTables {
	

	public static function tbcx(){
		$payload = Super::getParam("tbcx");
		$dataPackage = $payload["dataPackage"];

		$childTable = $dataPackage["datasetChild"];
		$deleteBy = $dataPackage["ownership"]["userid"];
		$parent = $dataPackage["ownership"]["xboset"];
		$parentid = $dataPackage["ownership"]["xbosetid"];
		$selectedRows =  $dataPackage["selectedRows"];

		if($childTable == "invcost" || $childTable == "invbalance" || 
			$childTable == "v_invbalance" || $childTable == "invvendor" || 
			$childTable == "matrectrans" || $childTable == "matusetrans" ||
			$childTable == "matsaletrans" || $childTable == "invtrans" 
		){
			Super::sendJsonResponse("error", "Cannot Delete Audit Tables & Transaction Tables.");

		} elseif($childTable == "invfifolifocost"){
			foreach($selectedRows as $row){
				$rowid = $row["invfifolifocostid"];
				$ifl = (new Set())->MboSetRemote("invfifolifocost")->where("invfifolifocostid = ?", $rowid)->getMboSet()->getMbo();
				$quantity = floatval($ifl->getDecimal("quantity"));
				if($quantity == 0){
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();
				}else{
					Super::sendJsonResponse("error", "Cannot Delete Inventory FIFO LIFO COST due to quantity is still exist: $quantity");
				}
			}
		}
	}
}
	

?>