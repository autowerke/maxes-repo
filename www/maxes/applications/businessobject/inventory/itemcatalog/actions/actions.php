<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");

Class ActionSet {

	public static function getItemStructNum(){
		$itemstructid = Super::getParam("getItemStructNum");
		$subitemnum = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->where("itemstructid=?", $itemstructid)->getMboValue("subitemnum");
		Super::sendJsonResponse("success", "Itemnum retrieved", ["subitemnum" => $subitemnum]);
	}

	
	public static function prodLogCharts(){
		// 1. Ambil Parameter dari JS
		$payload = Super::getParam("prodLogCharts");
		$targetItemNum = isset($payload["itemnum"]) ? $payload["itemnum"] : null;

		if (empty($targetItemNum)) {
			Set::throws(["error" => "Item Number is required"], JSON_PRETTY_PRINT);
			return;
		}

		// 2. Siapkan Struktur Output
		$response = [
			"header" => [
				"batches" => [],
				"batchIds" => [],
				"unitCosts" => [],
				"totalCosts" => []
			],
			"details" => []
		];

		// ==========================================================================
		// STEP 1: Dapatkan Daftar Batch Unik berdasarkan Item Number
		// Kita asumsikan matprodtrans menyimpan history produksi item tersebut.
		// ==========================================================================
		
		// Menggunakan fetchAll untuk query agregat yang efisien (Header Data)
		// Kita cari batchnum unik, tanggal produksi terakhir, dan total output quantity
		$sqlBatch = "SELECT batchnum, MAX(prodenddate) as prod_date, SUM(ABS(quantity)) as qty_produced 
					FROM matprodtrans 
					WHERE itemnum = ? AND batchnum IS NOT NULL 
					GROUP BY batchnum 
					ORDER BY prod_date ASC";
		
		$rawBatches = Set::fetchAll($sqlBatch, [$targetItemNum]);
		
		if (empty($rawBatches)) {
			Set::throws(["error" => "No production batches found for item: " . $targetItemNum]);
			return;
		}

		// Array pembantu untuk memetakan WO ke Batch (Penting untuk Labor)
		$woToBatchMap = []; 
		$batchList = [];

		foreach ($rawBatches as $b) {
			$batchId = $b['batchnum'];
			$batchList[] = $batchId;
			
			// Format Label untuk X-Axis Chart
			$dateLabel = $b['prod_date'] ? date('d M Y', strtotime($b['prod_date'])) : 'N/A';
			$response['header']['batches'][] = $batchId . "\n(" . $dateLabel . ")";
			$response['header']['batchIds'][] = $batchId;
			
			// Inisialisasi struktur detail per batch
			$response['details'][$batchId] = [
				'qty_produced' => (float)$b['qty_produced'], // Simpan qty untuk hitung unit cost nanti
				'kpi' => ['material' => 0, 'service' => 0, 'labor' => 0, 'tool' => 0],
				'mat' => ['total' => 0, 'data' => []],
				'svc' => ['total' => 0, 'data' => []],
				'lab' => ['total' => 0, 'data' => []],
				'tool' => ['total' => 0, 'data' => []]
			];
		}

		// ==========================================================================
		// STEP 2: Ambil Detail Material (matprodtrans)
		// Gunakan ABS() untuk mengkonversi nilai negatif menjadi positif
		// ==========================================================================
		$setMat = new Set();
		$mats = $setMat->MboSetRemote("matprodtrans")
					->whereIn("batchnum", $batchList) // Library feature
					->MboRemote("batchnum, wonum, materialnum, description, linecost, quantity, unitcost, warehouse")
					->fetchData();
	
		foreach ($mats as $row) {
			$bid = $row['batchnum'];
			// Konversi ke nilai absolut (positif) untuk biaya produksi
			$cost = abs((float)$row['linecost']);
			
			// Mapping WO ke Batch (Untuk Labor nanti)
			if (!empty($row['wonum'])) {
				$woToBatchMap[$row['wonum']] = $bid;
			}

			// Jangan masukkan item utama (output) sebagai biaya material, 
			// asumsikan materialnum yang berbeda dari targetItemNum adalah bahan baku
			// ATAU jika logic Anda semua di matprodtrans adalah cost, hapus if ini.
			// if ($row['materialnum'] == $targetItemNum) continue; 

			$response['details'][$bid]['mat']['total'] += $cost;
			$response['details'][$bid]['kpi']['material'] += $cost;
			
			$response['details'][$bid]['mat']['data'][] = [
				'name' => $row['description'] ?: $row['materialnum'],
				'value' => $cost,
				'qty' => abs((float)$row['quantity']), // Konversi qty juga ke positif
				'unitcost' => abs((float)$row['unitcost']), // Konversi unitcost ke positif
				'warehouse' => $row['warehouse']
			];
		}

		// ==========================================================================
		// STEP 3: Ambil Detail Services (servprodtrans)
		// Gunakan ABS() untuk mengkonversi nilai negatif menjadi positif
		// ==========================================================================
		$setSvc = new Set();
		$svcs = $setSvc->MboSetRemote("servprodtrans")
					->whereIn("batchnum", $batchList)
					->MboRemote("batchnum, wonum, description, linecost, ponum, serviceitemnum")
					->fetchData();

		foreach ($svcs as $row) {
			$bid = $row['batchnum'];
			// Konversi ke nilai absolut (positif)
			$cost = abs((float)$row['linecost']);

			if (!empty($row['wonum'])) {
				$woToBatchMap[$row['wonum']] = $bid;
			}

			$response['details'][$bid]['svc']['total'] += $cost;
			$response['details'][$bid]['kpi']['service'] += $cost;

			$response['details'][$bid]['svc']['data'][] = [
				'name' => $row['description'],
				'value' => $cost,
				'ponum' => $row['ponum']
			];
		}

		// ==========================================================================
		// STEP 4: Ambil Detail Tools (toolprodtrans)
		// Gunakan ABS() untuk mengkonversi nilai negatif menjadi positif
		// ==========================================================================
		$setTool = new Set();
		$tools = $setTool->MboSetRemote("toolprodtrans")
						->whereIn("batchnum", $batchList)
						->MboRemote("batchnum, wonum, toolnum, description, linecost, quantity, assetnum")
						->fetchData();

		foreach ($tools as $row) {
			$bid = $row['batchnum'];
			// Konversi ke nilai absolut (positif)
			$cost = abs((float)$row['linecost']);

			if (!empty($row['wonum'])) {
				$woToBatchMap[$row['wonum']] = $bid;
			}

			$response['details'][$bid]['tool']['total'] += $cost;
			$response['details'][$bid]['kpi']['tool'] += $cost;

			$response['details'][$bid]['tool']['data'][] = [
				'name' => $row['description'] ?: $row['toolnum'],
				'value' => $cost,
				'qty' => abs((float)$row['quantity']), // Konversi qty ke positif
				'asset' => $row['assetnum']
			];
		}

		// ==========================================================================
		// STEP 5: Ambil Detail Labor (labortransline)
		// Isu: Labor tidak punya batchnum. Kita pakai $woToBatchMap
		// Gunakan ABS() untuk mengkonversi nilai negatif menjadi positif
		// ==========================================================================
		$uniqueWos = array_unique(array_keys($woToBatchMap));
		
		if (!empty($uniqueWos)) {
			$setLab = new Set();
			$labs = $setLab->MboSetRemote("labortransline")
						->whereIn("wonum", $uniqueWos)
						->MboRemote("wonum, personid, linecost, reghours, extendedhours, totalovertimecost, startdate")
						->fetchData();

			foreach ($labs as $row) {
				$wo = $row['wonum'];
				
				// Cek WO ini milik batch mana
				if (!isset($woToBatchMap[$wo])) continue; 
				$bid = $woToBatchMap[$wo];

				// Konversi ke nilai absolut (positif)
				$totalLine = abs((float)$row['linecost']);
				$otCost = abs((float)$row['totalovertimecost']);
				$regCost = $totalLine - $otCost;
				
				// Pastikan regCost tidak negatif
				if ($regCost < 0) {
					$regCost = 0;
					$otCost = $totalLine;
				}

				$response['details'][$bid]['lab']['total'] += $totalLine;
				$response['details'][$bid]['kpi']['labor'] += $totalLine;

				$response['details'][$bid]['lab']['data'][] = [
					'name' => $row['personid'], // Sebaiknya join ke PERSON table untuk nama lengkap jika ada
					'reg' => $regCost,
					'ot' => $otCost,
					'hours_reg' => abs((float)$row['reghours']),
					'hours_ot' => abs((float)$row['extendedhours']),
					'date' => $row['startdate']
				];
			}
		}

		// ==========================================================================
		// STEP 6: Finalisasi Header Calculations
		// ==========================================================================
		foreach ($response['header']['batchIds'] as $bid) {
			$d = $response['details'][$bid];
			
			$grandTotal = $d['mat']['total'] + $d['svc']['total'] + $d['lab']['total'] + $d['tool']['total'];
			$qty = $d['qty_produced'] > 0 ? $d['qty_produced'] : 1; // Prevent div by zero
			$unitCost = $grandTotal / $qty;

			$response['header']['totalCosts'][] = $grandTotal;
			$response['header']['unitCosts'][] = round($unitCost, 2);
			
			// Hapus field qty_produced dari details agar JSON lebih bersih (opsional)
			unset($response['details'][$bid]['qty_produced']);
		}

		// 3. Return Response ke JS
		Set::throws($response);
	}
	public static function generateDescription(){
		$itemnum = Super::getParam("gendescription");
	
		$itemclassSet = (new Set())->useTransactionManager()->MboSetRemote("itemclassification")->where("itemnum = ?", $itemnum)
		->MboRemote("attributename, description, value, unit")->getMboSet();
		

		$specs = [];

		while($itemclass = $itemclassSet->fetch()){
			$specs [] = [
				"attributename" =>  $itemclass["attributename"],
				"description" => $itemclass["description"],
				"value" => $itemclass["value"],
				"unit" => $itemclass["unit"]
			];
		}
		
		$specTexts = array_map(function($spec){
			return $spec['attributename'] . ': ' . $spec['value'] . ' ' . $spec['unit'];
		}, $specs);

		$finalText = implode("; ", $specTexts);

		(new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->setValue("spectext", $finalText)->change();
		Super::sendJsonResponse("success", $finalText);
	}	
	public static function addAttributes(){
		$payload = Super::getParam("addAttributes");
		$itemnum = $payload["itemnum"];
		$data = $payload["data"];

		$attrList = [];

		$attributes = [];
		foreach($data as $row){
			$attributeid = $row["attributeid"];
			$attrSet = (new Set())->useTransactionManager()->MboSetRemote("attribute")->where("attributeid = ?", $attributeid)->MboRemote("attributename, description, type")->getMboSet()->fetch();
			$attributes [] = [
				"attributename" => $attrSet["attributename"],
				"description" => $attrSet["description"],
				"type" => $attrSet["type"]
			];
		}

		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();

		foreach($attributes as &$row){
			$row["itemnum"] = $itemnum;
			$row["enterby"] = $enterby;
			$row["enterdate"] = $enterdate;
		}

		
		(new Set())->MboSetRemote("itemclassification")->addStream($attributes);
		Super::sendJsonResponse("success", "Attributes has been added to Item : $itemnum");

	}
	public static function requestitemnumber(){
   		$requestitemnumber = Super::getParam("requestitemnumber");

		if (is_array($requestitemnumber)) {
			foreach ($requestitemnumber as $item) {
				$object = $item['object'];
				$objectkey = $item['objectkey'];
				$itemnum = $item['itemnum'];
				$rowindex = $item['rowindex'];
				$objectSet = (new Set())->useTransactionManager()->MboSetRemote("itemrequest")->setWhere("object='$object' and objectkey='$objectkey'")->setValue("itemnum", $itemnum)->change();				
				(new Set())->useTransactionManager()->MboSetRemote($object)->setWhere($object."id = '$objectkey'")->setValue("itemnum", $itemnum)->change();
			}
				$msg = "success";
				$remarks="";
			} else {
				$msg="error";
				$remarks= "Request Item is not an array.";
			}
		
		Set::throws(["msg"=>$msg, "remarks"=>$remarks]);
	}
	public static function addHazards(){
		$hazards = $_POST["addHazards"];
		$itemnum = $_POST["itemnum"];
		$set = new Set();
		$tb = "itemhazard";
		$len = count($hazards);
		for($i=0; $i<$len; $i++){
			$hazardnum = $hazards[$i]["hazardnum"];
			$hazarddesc = $hazards[$i]["hazarddesc"];
			$itemHazardSet = $set->MboSetRemote($tb)->setWhere("itemnum='$itemnum' and hazardnum = '$hazardnum'")
			->getMboSet();
			$cnt = $itemHazardSet->counts();
			if($cnt == 0){
				$set =  new Set();
				$set->MboSetRemote($tb)->setValue("itemnum", $itemnum)->setValue("hazardnum", $hazardnum)->setValue("description", $hazarddesc)->add();
				$set2 = new Set();
				$hazPrecSet = $set2->MboSetRemote("hazardprecaution")->setRelationship("precautions", "precautions.precaution=hazardprecaution.precaution")
				->setWhere("hazardprecaution.hazardnum='$hazardnum'")
				->MboRemote("hazardprecaution.precaution")->MboRemote("precautions.description")->getMboSet();
				$cntPrecs = $hazPrecSet->counts();
				for($j=0; $j<$cntPrecs; $j++){
					$hazPrec = $hazPrecSet->getMbo();
					$precautionlinenum = Xbo::getLineNumber("itemprecaution", "precautionlinenum", "itemnum='$itemnum'");
					$precaution = $hazPrec->getString("precaution");
					$precdesc = $hazPrec->getString("description");
					$set3 = new Set();
					$itemPrecSet = $set3->MboSetRemote("itemprecaution")->setWhere("precaution='$precaution' and itemnum = '$itemnum'")->getMboSet();
					$cntItemPrecSet = $itemPrecSet->counts();
					if($cntItemPrecSet == 0){
						$set4 = new Set();
						$set4->MboSetRemote("itemprecaution")->setValue("itemnum", $itemnum)
						->setValue("precaution", $precaution)
						->setValue("precautionlinenum", $precautionlinenum)
						->setValue("description", $precdesc)
						->add();
					}
				}
				$msg = "added";
			}else{
				$msg = "exist";
			}
		}
		
		Set::throws($msg);
	}
	public static function getBatchNum(){
		$bb = new stdClass();
		$bb->batchnum = Xbo::GetBatchNum();
		Set::throws($bb);
	}
	
	public static function RFIDTag (){
		$sn = $_POST["sn"];
		$rfidid = $_POST["rfidid"];
		$antenna = $_POST["antenna"];
		$enterdate = Xbo::setEnterDate();
		$set = new Set();
		$rfidtagSet = $set->MboSetRemote("rfidtag")->setWhere("sn='$sn'")->MboRemote("sn")->MboRemote("enterdate")->getMboSet();
		$countrf = $rfidtagSet->counts();
		$set2 = new Set();
		if($countrf > 0){
			$set->MboSetRemote("rfidtag")->setWhere("sn='$sn'")->setValue("antenna", $antenna)->setValue("enterdate", $enterdate)->setValue("rfidid", $rfidid)->change();
			echo "CHANGED";
		}else{
			$set->MboSetRemote("rfidtag")->setValue("sn", $sn)->setValue("antenna", $antenna)->setValue("enterdate", $enterdate)->setValue("rfidid", $rfidid)->add();
			echo "ADD";
		}
		
	}
	
	public static function mapAddToInventory(){
		//$_POST["AddToInventory"] = $_POST["mapAddToInventory"];
		$itemnum = $_POST["mapAddToInventory"];
		$conditiontype = $_POST["conditiontype"];
		$sitecode = $_POST["sitecode"];
		$orgcode = $_POST["orgcode"];
		$orderunit = $_POST["orderunit"];
		$issueunit = $_POST["issueunit"];
		$brand = $_POST["brand"];
		$description = $_POST["description"];
		$spectext = $_POST["spectext"];
		$wc = "itemnum='$itemnum' and orgcode='$orgcode'";
		$set = new Set();
		$checkItemSet = $set->MboSetRemote("item")->setWhere($wc)->getMboSet();
		$cnt = $checkItemSet->counts();
		if($cnt == 0){
			$set = new Set();
			$set->MboSetRemote("item")
			->setValue("itemnum", $itemnum)
			->setValue("description", $description)
			->setValue("sitecode", $sitecode)
			->setValue("orgcode", $orgcode)
			->setValue("orderunit", $orderunit)
			->setValue("issueunit", $issueunit)
			->setValue("brand", $brand)
			->setValue("spectext", $spectext)
			->add();
		}else{
			$set = new Set();
			$set->MboSetRemote("item")->setWhere($wc)
			->setValue("description", $description)
			->setValue("orderunit", $orderunit)
			->setValue("issueunit", $issueunit)
			->setValue("brand", $brand)
			->setValue("spectext", $spectext)
			->change();
		}
		
		
		//Add Measure Unit
		$set =  new Set();
		$measureUnitSet = $set->MboSetRemote("measureunit")->setWhere("measureunitcode ='$issueunit' and orgcode='$orgcode'")->getMboSet();
		$countMu = $measureUnitSet->counts();
		if($countMu == 0){
			$set = new Set();
			$set->MboSetRemote("measureunit")
			->setValue("measureunitcode", $issueunit)
			->setValue("description", $issueunit)
			->setValue("orgcode", $orgcode)
			->add();
		}
		
		
		
		$msg = XboSet::AddToInventory($_POST, null, null, true);
	}
	public static function AddToInventory($param = null, $jsonarr = null)
	{
		
		XboSet::AddToInventory($param, $jsonarr);
		  
	}
	
	
	
	public static function AddClassification()

	{
		$classcode = $_POST["AddClassification"];
		$itemnum = $_POST["itemnum"];
		
		$set = new Set();
		$classAttrSet = $set->MboSetRemote("classattribute")->setWhere("classificationcode='$classcode'")->MboRemote("attributename")->MboRemote("type")->getMboSet();
		$numrows = $classAttrSet->counts();
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		for ($i = 0; $i < $numrows; $i++) {
			$classAttr = $classAttrSet->getMbo();
			$attributename = $classAttr->getString("attributename");
			$type = $classAttr->getString("type");
			$set2 = new Set();
			$itemClassSet = $set2->MboSetRemote("itemclassification")->setWhere("itemnum='$itemnum' and attributename='$attributename'")->MboRemote("attributename")->getMboSet();
			$numrowsAtt = $itemClassSet->counts();
			if ($numrowsAtt == 0) {
				$set2->MboSetRemote("itemclassification")->setValue("itemnum", $itemnum)->setValue("classificationcode", $classcode)->setValue("attributename", $attributename)->setValue("type", $type)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->add();
				
			}
		}
		Set::throws(["msg"=>"added"]);
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

		if($childTable == "invbalance" 
			|| $childTable == "v_invbalance" 
			|| $childTable == "invvendor" 
			|| $childTable == "itemcondition"
			|| $childTable == "matprodtrans" 
			|| $childTable == "servprodtrans" 
			|| $childTable == "toolprodtrans" 
		){
			Super::sendJsonResponse("error", "You're not allowed to delete the $childTable records");
		}elseif($childTable == "itempartnum" || 
			$childTable == "itemclassification" ||
			$childTable == "itemhazard" || $childTable == "itembarcode" || 	$childTable == "itemalternate" || $childTable == "itemstruct" || $childTable == "itemprecaution" ){
				$_POST["delChildPackage"] = $dataPackage;
				Super::deleteChildren();
		}

	}
}
	

?>