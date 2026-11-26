<?php
//require_once ("../../../eat.php");

$dir =  __DIR__ . '/../../../eat.php';
require_once ($dir);

Class ActionSet {
	
	public static function sendJsonResponse($status, $message) {
		echo json_encode(['status' => $status, 'message' => $message]);
		exit();
	}
	public static function addMeterFreq(){
		$payload = Super::getParam("addMeterFreq");
		$pmnum = $payload["pmnum"];
		$meters = $payload["meters"];
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		if(empty($pmnum)) self::sendJsonResponse("error", "Please define the PM Num.");
		if(empty($meters)) self::sendJsonResponse("error", "Meter is not yet selected");
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		$assetnum = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum=?", $pmnum)->MboRemote("assetnum")->getMboSet()->getMbo()->getString("assetnum");
		try{
			foreach($meters as $data){
				$meterid = $data["value"];
				$meternum = (new Set())->useTransactionManager()->MboSetRemote("meter")->where("meterid=?", $meterid)->MboRemote("meternum")->getMboSet()->getMbo()->getString("meternum");
				$pmmeterfreqSet = (new Set())->useTransactionManager()->MboSetRemote("pmmeterfreq")->where("meternum=? and pmnum=?", $meternum, $pmnum)->getMboSet();
				if($pmmeterfreqSet->isEmpty()){
					(new Set())->MboSetRemote("pmmeterfreq")
					->setValue("pmnum", $pmnum)
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->add();
				}
				
				$assetMeterSet = (new Set())->useTransactionManager()->MboSetRemote("assetmeter")->where("meternum=? and assetnum=?", $meternum, $assetnum)->getMboSet();
				if($assetMeterSet->isEmpty()){
					(new Set())->MboSetRemote("assetmeter")
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				}
			}
			$tm->commit();
			self::sendJsonResponse("success", "Meter has been added for meter frequency");
		}catch(Exception $err){
			$tm->rollback();
			throw $err;
		}
	}
	
	public static function addPMMeterFreqJP(){
		$payload = Super::getParam("addPMMeterFreqJP");
		$pmnum = $payload["pmnum"];
		$pmmeterfreqid = $payload["pmmeterfreqid"];
		$wtnums = $payload["wtnum"];
		
		if(empty($pmnum)) self::sendJsonResponse("error", "Please define the PM Num.");
		if(empty($pmmeterfreqid)) self::sendJsonResponse("error", "Meter Frequency is not yet selected");
		if(empty($wtnums)) self::sendJsonResponse("error", "Selected Job Plans Not Found");
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try{
			$sequence = 0;	
			foreach($wtnums as $data){
				$sequence = $sequence+10;
				$wtid = $data["value"];
				$wtnum = (new Set())->useTransactionManager()->MboSetRemote("worktemplates")->where("worktemplatesid=?", $wtid)->getMboSet()->getMbo()->getString("wtnum");
				$pmmeterfreqjpSet = (new Set())->useTransactionManager()->MboSetRemote("pmmeterfreqjp")->where("pmnum=? and pmmeterfreqid=? and wtnum=?", $pmnum, $pmmeterfreqid, $wtnum)->getMboSet();
				
				
				if($pmmeterfreqjpSet->isEmpty()){
					$pmmeterfreqjp = $pmmeterfreqjpSet->getMbo();
					(new Set())->MboSetRemote("pmmeterfreqjp")->useTransactionManager()
					->setValue("pmnum", $pmnum)
					->setValue("pmmeterfreqid", $pmmeterfreqid)
					->setValue("wtnum", $wtnum)
					->setValue("sequence", $sequence)
					->add();
				}
			}
			$tm->commit();
			self::sendJsonResponse("success", "Job Plan has been added for meter frequency");
		}catch(Exception $err){
			$tm->rollback();
			throw $err;
		}
		
	}
	public static function addAdditionalJP(){
		$payload = Super::getParam("addAdditionalJP");
		$pmnum = $payload["pmnum"];
		$pmwolineid = $payload["pmwolineid"];
		$wtnums = $payload["wtnum"];
		
		if(empty($pmnum)) self::sendJsonResponse("error", "Please define the PM Num.");
		if(empty($pmwolineid)) self::sendJsonResponse("error", "Forecasted WO is not yet selected");
		if(empty($wtnums)) self::sendJsonResponse("error", "Selected Job Plans Not Found");
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try{
			foreach($wtnums as $data){
				$wtid = $data["value"];
				$wtnum = (new Set())->useTransactionManager()->MboSetRemote("worktemplates")->where("worktemplatesid=?", $wtid)->getMboSet()->getMbo()->getString("wtnum");
				$wonum = (new Set())->useTransactionManager()->MboSetRemote("pmwoline")->where("pmwolineid=?", $pmwolineid)->getMboSet()->getMbo()->getString("wonum");
				$pmjpLineSet = (new Set())->useTransactionManager()->MboSetRemote("pmjpline")->where("pmnum=? and pmwolineid=? and wtnum=?", $pmnum, $pmwolineid, $wtnum)->getMboSet();
				$sequence = (new Set())->useTransactionManager()->MboSetRemote("pmjpline")->where("pmnum=? and pmwolineid=? ", $pmnum, $pmwolineid)->MboRemote("max(sequence)+1 sequence")->getMboSet()->getMbo()->getString("sequence");
				if($pmjpLineSet->isEmpty()){
					$pmjpLine = $pmjpLineSet->getMbo();
					(new Set())->MboSetRemote("pmjpline")->useTransactionManager()
					->setValue("pmnum", $pmnum)
					->setValue("pmwolineid", $pmwolineid)
					->setValue("wtnum", $wtnum)
					->setValue("wonum", $wonum)
					->setValue("sequence", $sequence)
					->add();
				}
			}
			$tm->commit();
			self::sendJsonResponse("success", "Additional Job Plan has been added");
		}catch(Exception $err){
			$tm->rollback();
			throw $err;
		}
	}
	public static function addMeterRuleJP(){
		$payload = Super::getParam("addMeterRuleJP");
		$pmnum = $payload["pmnum"];
		$pmmeterruleid = $payload["pmmeterruleid"];
		$data = $payload["wtnum"];
		if (empty($payload["pmnum"]) || empty($payload["pmmeterruleid"])) {
			self::sendJsonResponse("error", "PMNUM or PM Meter Rule ID is incomplete");
			return;
		}
			
		$sequence = 0;	
		foreach($data as $row){
			$wtnumid = $row["value"];
			$sequence = $sequence+10;
			$wtnum = (new Set())->MboSetRemote("worktemplates")->where("worktemplatesid=?", $wtnumid)->MboRemote("wtnum")->getMboSet()->getMbo()->getString("wtnum");
			$objectSet = (new Set())->MboSetRemote("pmmeterrulejp")->where("pmnum=? and pmmeterruleid=? and wtnum=? ", $pmnum, $pmmeterruleid, $wtnum)->getMboSet();
			if($objectSet->isEmpty()){
				(new Set())->MboSetRemote("pmmeterrulejp")
				->setValue("pmnum", $pmnum)
				->setValue("pmmeterruleid", $pmmeterruleid)
				->setValue("wtnum", $wtnum)
				->setValue("sequence", $sequence)
				->add();
			}
			
		}
		
		self::sendJsonResponse("success", "Job Plan(s) for Meter Rules has been added");
	}
	public static function addHealthRuleJP(){
		$payload = Super::getParam("addHealthRuleJP");
		$pmnum = $payload["pmnum"];
		$pmhealthruleid = $payload["pmhealthruleid"];
		$data = $payload["wtnum"];
		if (empty($payload["pmnum"]) || empty($payload["pmhealthruleid"])) {
			self::sendJsonResponse("error", "PMNUM or PM Health Rule ID is incomplete");
			return;
		}
			
		$sequence = 0;	
		foreach($data as $row){
			$wtnumid = $row["value"];
			$sequence = $sequence+10;
			$wtnum = (new Set())->MboSetRemote("worktemplates")->where("worktemplatesid=?", $wtnumid)->MboRemote("wtnum")->getMboSet()->getMbo()->getString("wtnum");
			$objectSet = (new Set())->MboSetRemote("pmhealthrulejp")->where("pmnum=? and pmhealthruleid=? and wtnum=? ", $pmnum, $pmhealthruleid, $wtnum)->getMboSet();
			if($objectSet->isEmpty()){
				(new Set())->MboSetRemote("pmhealthrulejp")
				->setValue("pmnum", $pmnum)
				->setValue("pmhealthruleid", $pmhealthruleid)
				->setValue("wtnum", $wtnum)
				->setValue("sequence", $sequence)
				->add();
			}
			
		}
		
		self::sendJsonResponse("success", "Job Plan(s) for Meter Rules has been added");
	}
	public static function addJobPlans()
	{
		try {
			
			$payload = Super::getParam();
			if (empty($payload["pmnum"]) || empty($payload["addJobPlans"]["wtnum"])) {
				self::sendJsonResponse("error", "PMNUM or WTNUM is incomplete");
				return;
			}

			$pmnum = $payload["pmnum"];
			$incomingWtData = $payload["addJobPlans"]["wtnum"];

			$incomingWtnums = array_column($incomingWtData, 'wtnum');

			if (empty($incomingWtnums)) {
				self::sendJsonResponse("success", "No Selected Job PLans to be added ");
				return;
			}

			$set = new Set();
			$placeholders = rtrim(str_repeat('?,', count($incomingWtnums)), ',');
			
			$existingWtSet = $set->MboSetRemote("pmjprule")
								 ->where("pmnum = ? AND wtnum IN ($placeholders)", $pmnum, ...$incomingWtnums)
								 ->MboRemote("wtnum") 
								 ->getMboSet();
			
			$existingWtnums = [];
			while ($row = $existingWtSet->fetch()) {
				$existingWtnums[] = $row['wtnum'];
			}

			// membandingkan array pertama dengan yang kedua dan mengembalikan perbedaannya
			$wtnumsToInsert = array_diff($incomingWtnums, $existingWtnums);
			
			// Jika ngga ada wtnum baru yang perlu ditambahkan, selesai.
			if (empty($wtnumsToInsert)) {
				self::sendJsonResponse("success", "The selected Job Plan(s) has already been exist");
				return;
			}

			// Siapin data untuk addStream HANYA untuk wtnum yang baru
			$dataForStream = [];
			foreach ($wtnumsToInsert as $wtnum) {
				$dataForStream[] = [
					'pmnum' => $pmnum,
					'wtnum' => $wtnum,
					'ruletype' => 'FREQUENCY',
					'frequencyvalue' => '1',
					'sequence' => '1',
					'behaviour' => 'STACKED'
				];
			}

			(new Set())->MboSetRemote("pmjprule")
					   ->addStream($dataForStream);

			self::sendJsonResponse("success", "Job Plan(s) has been added");

		} catch (Exception $e) {
			self::sendJsonResponse("error", "Something wrong, man... " . $e->getMessage());
		}
	}
	
	
	public static function addMeters(){
		$payload = Super::getParam();
		$pmnum = $payload["pmnum"];
		$meternums = $payload["addMeters"]["meternum"];
		
		$set = new Set();
		$assetnum = $set->MboSetRemote("pm")->setWhere("pmnum='$pmnum'")->MboRemote("assetnum")->getMboSet()->getMbo()->getString("assetnum");
		if(empty($assetnum)){
			self::sendJsonResponse("error", "Please define the assets and SAVE the record.");
		}
		for($i=0; $i<count($meternums); $i++){
			$data = $meternums[$i];
			$meternum = $data["meternum"];
			$set = new Set();
			$meterSet = $set->MboSetRemote("pmmeterrule")->setWhere("meternum = '$meternum' and pmnum = '$pmnum'" )->MboRemote("pmmeterruleid")->getMboSet();
			if($meterSet->isEmpty()){
				$set2 = new Set();
				$set2->MboSetRemote("pmmeterrule")
				->setValue("meternum", $meternum)
				->setValue("pmnum", $pmnum)
				->setValue("assetnum", $assetnum)
				->setValue("enterdate", $enterdate)
				->setValue("enterby", $enterby)
				->add();
			}
			$assetMeterSet = (new Set())->MboSetRemote("assetmeter")->where("assetnum=? and meternum=?", $assetnum, $meternum)->getMboSet();
			if($assetMeterSet->isEmpty()){
				(new Set())->MboSetRemote("assetmeter")
				->setValue("assetnum", $assetnum)
				->setValue("meternum", $meternum)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->add();
			}
		}
		self::sendJsonResponse("success", "Meter(s) has been added");
	}
	
	
	public static function getAssetMeters()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("getAssetMeters");
			if (empty($payload["pmnum"]) || empty($payload["assetnum"])) {
				throw new InvalidArgumentException("PMNUM or ASSETNUM is incomplete.");
			}
			$pmnum = $payload["pmnum"];
			$assetnum = $payload["assetnum"];
			$glaccount = null;
			$teamnum = null;
			$leader = null;
			$locnum = null; //locnum
			
			$assetSet = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=?", $assetnum)->limit(1)
			->MboRemote("glaccount")->MboRemote("teamnum")->MboRemote("locnum")->getMboSet();
			
			if(!$assetSet->isEmpty()){
				$assetMbo = $assetSet->getMbo();
				$glaccount = $assetMbo->getString("glaccount");
				$teamnum = $assetMbo->getString("teamnum");
				$locnum = $assetMbo->getString("locnum");
					if(empty($glaccount)){
						$glaccount = (new Set())->useTransactionManager()->MboSetRemote("locations")->where("locnum=?", $locnum)->getMboSet()->getMbo()->getString("locnum");
					}
					
					
				
				$leader = Xbo::getTeamLeader($teamnum)->personid;
				
			}
			
			$assetMeterSet = (new Set())->useTransactionManager()
				->MboSetRemote("assetmeter")
				->where("assetnum = ?", $assetnum)
				->MboRemote("meternum")
				->getMboSet();

			$allAssetMeters = [];
			while ($row = $assetMeterSet->fetch()) {
				$allAssetMeters[] = $row['meternum'];
			}

			if (empty($allAssetMeters)) {
				self::sendJsonResponse("error", "This asset has no meter installed.");
				$tm->commit(); // Commit transaksi kosong
				return;
			}

			// Langkah 3: Cek SEKALI SAJA meter mana yang SUDAH ADA di pmmeterrule & pmmeterfreq
			$existingRules = self::getExistingMeters("pmmeterrule", $pmnum, $allAssetMeters);
			$existingFreqs = self::getExistingMeters("pmmeterfreq", $pmnum, $allAssetMeters);

			// Langkah 4: Hitung di PHP meter mana yang PERLU DITAMBAHKAN
			$rulesToInsert = array_diff($allAssetMeters, $existingRules);
			$freqsToInsert = array_diff($allAssetMeters, $existingFreqs);

			// Langkah 5: Gunakan addStream untuk INSERT massal HANYA JIKA ada data baru
			if (!empty($rulesToInsert)) {
				$ruleDataForStream = [];
				foreach ($rulesToInsert as $meternum) {
					$ruleDataForStream[] = [
						'pmnum' => $pmnum,
						'assetnum' => $assetnum,
						'meternum' => $meternum
					];
				}
				(new Set())->useTransactionManager()->MboSetRemote("pmmeterrule")->addStream($ruleDataForStream);
			}
			
			if (!empty($freqsToInsert)) {
				$freqDataForStream = [];
				foreach ($freqsToInsert as $meternum) {
					$freqDataForStream[] = [
						'pmnum' => $pmnum,
						'assetnum' => $assetnum,
						'meternum' => $meternum
					];
				}
				(new Set())->useTransactionManager()->MboSetRemote("pmmeterfreq")->addStream($freqDataForStream);
			}
			
			(new Set())->MboSetRemote("pm")->useTransactionManager()
			->where("pmnum = ?", $pmnum)
			->setValue("leader", $leader)
			->setValue("glaccount", $glaccount)
			->setValue("location", $locnum)
			->setValue("teamnum", $teamnum)
			->setValue("assetnum", $assetnum)
			->change();
			
			$tm->commit();
			Super::sendJsonResponse("success", "The meter(s) is available for monitoring.", ["glaccount" => $glaccount, "leader"=>$leader, "location"=>$locnum, "teamnum" => $teamnum]);

		} catch (Exception $e) {
			$tm->rollback();
			self::sendJsonResponse("error", "Something Wrong :" . $e->getMessage());
		}
	}

	private static function getExistingMeters(string $tableName, string $pmnum, array $allAssetMeters): array
	{
		if (empty($allAssetMeters)) {
			return [];
		}

		$set = (new Set())->useTransactionManager();
		$placeholders = rtrim(str_repeat('?,', count($allAssetMeters)), ',');
		
		// Gabungkan pmnum ke depan array parameter
		$params = array_merge([$pmnum], $allAssetMeters);

		$resultSet = $set->MboSetRemote($tableName)
						  ->where("pmnum = ? AND meternum IN ($placeholders)", ...$params)
						  ->MboRemote("meternum")
						  ->getMboSet();

		$existingMeters = [];
		while ($row = $resultSet->fetch()) {
			$existingMeters[] = $row['meternum'];
		}
		return $existingMeters;
	}
	public static function addToCart(){
		$payload = Super::getParam("addToCart");
		$pmnum = $payload["pmnum"];
		$quantity = abs($payload["orderqty"]);
		$itemnum = $payload["itemnum"];
		
		$whereClause = "itemnum='$itemnum'";
		$set = new Set();
		$conditiontype = $set->MboSetRemote("itemcondition")->setWhere("$whereClause and conditionrate='100'")->MboRemote("conditiontype")->getMboSet()->getMbo()->getString("conditiontype");
		$set = new Set();
		$unitcost = $set->MboSetRemote("invfifolifocost")->setWhere($whereClause)->MboRemote("max(unitcost) as unitcost")->getMboSet()->getMbo()->getString("unitcost");
		$linecost = floatval($quantity)*floatval($unitcost);
		$set = new Set();
		$orderunit = $set->MboSetRemote("item")->setWhere($whereClause)->MboRemote("orderunit")->getMboSet()->getMbo()->getString("orderunit");
		
		
		$set = new Set();
		$pmSet = $set->MboSetRemote("pm")->setWhere("pmnum='$pmnum'")->MboRemote("description")->MboRemote("owner")->MboRemote("leader")->getMboSet();
		$pm = $pmSet->getMbo();
		$description = $pm->getString("description");
		$owner = $pm->getString("owner");
		$leader = $pm->getString("leader");
		$remarks = "PM Material Forecasting [$pmnum - $description]. Responsible Owner: $owner, Team Leader: $leader.";
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		$whereClause = "object='pm' and objectnum='$pmnum' and itemnum='$itemnum' ";
		(new Set())->MboSetRemote("invcart")->setWhere($whereClause)->remove();
		$cartSet = (new Set())->MboSetRemote("invcart")->setWhere($whereClause)->getMboSet();
		if($cartSet->isEmpty()){
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
			->setValue("replenishfrom", "PM Time Based")
			->setValue("objectnum", $pmnum)
			->setValue("object", "pm")
			->add();
			self::sendJsonResponse("success", "PM Forecasting Material has been added to Inventory Cart");
		}else{
			self::sendJsonResponse("error", "PM Forecasting has already been exist");
		}
		
	}
	
	
	public static function getJobPlanResources(){
		$pmnum = Super::getParam("getJobPlanResources");
		
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try {
			
			$pmwolineSet=  (new Set())->useTransactionManager()->MboSetRemote("pmjpline pmjp")->where("pmjp.pmnum=?", $pmnum)
			->setRelationship("pmwoline pmwo", "pmjp.pmnum = pmwo.pmnum and pmjp.wonum = pmwo.wonum")
			->order("pmwo.targstartdate")
			->MboRemote("pmjp.wtnum")
			->MboRemote("pmwo.targstartdate")
			->getMboSet();
			
			if($pmwolineSet->isEmpty()){
				self::sendJsonResponse("info", "PM does not have Job Plans(s)");
			}
			
			$countJobPlans = $pmwolineSet->counts(); //tbf #1
			$required_jobplan_materials = [];
			while($pmjpline = $pmwolineSet->fetch()){
				$workTemplate = new stdClass;
				$wtnum = $pmjpline["wtnum"];
				$targstartdate = $pmjpline["targstartdate"];
				$workTemplate->targstartdate = $targstartdate;
				
				// 1. Mengambil Header Job Plan
				
				$workTemplateSet = (new Set())->useTransactionManager()->MboSetRemote("worktemplates")->where("wtnum=?", $wtnum)->getMboSet();
				
				if($workTemplateSet->isEmpty()){
					continue;
				}
				
				$wt = $workTemplateSet->getMbo();
				$workTemplate->wtnum = $wt->getString("wtnum");
				$workTemplate->assetnum = $wt->getString("assetnum");
				
				$workTemplate->asset_description = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=?", $workTemplate->assetnum)->MboRemote("description")->getMboSet()->getMbo()->getString("description");
				$workTemplate->description = $wt->getString("description");
				$workTemplate->worktype = $wt->getString("worktype");
				$workTemplate->status = $wt->getString("status");
					
				// 3. Mengambil material Job Plan
				
				$wtMaterialSet = (new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")->where("wtnum=?", $wtnum)
				->MboRemote("itemnum")
				->MboRemote("planlinenum")
				->MboRemote("quantity")
				->MboRemote("unitcost")
				->MboRemote("conditiontype")
				->MboRemote("warehouse")
				->MboRemote("racknum")
				->getMboSet();
		
				//4. Apakah Job Plan Memiliki Material ? 
				if($wtMaterialSet->isEmpty()){
					continue;
				}
				$jp_materials = [];
				// 5. Mengambil Planning Material Job Plan
				
				while($wtm = $wtMaterialSet->fetch()){
					//$wtm = $wtMaterialSet->getMbo();
					$itemnum = $wtm["itemnum"];
					
					$item_description = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->MboRemote("description")->getMboSet()->getMbo()->getString("description");
					$material = new stdClass;
					$material->itemnum = $itemnum;
					$material->item_description = $item_description;
					$material->jobplan_qty = $wtm["quantity"];
					
					// 6. Mendapatkan Current Qty
					
					$invfifolifoSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost ivl")
					->setRelationship("inventory iv", "ivl.itemnum = iv.itemnum and ivl.warehouse = iv.warehouse")
					->where("ivl.itemnum=?", $itemnum)->group("ivl.itemnum, ivl.warehouse")
					->MboRemote("ivl.warehouse")
					->MboRemote("iv.safetystock")
					->MboRemote("iv.leadtime")
					->MboRemote("sum(ivl.quantity) as total_qty")
					->getMboSet();
					
					$material->currentStock = [];
					if($invfifolifoSet->isEmpty()){
					}else{
						//7. save to Material Current Stock
						$stockData = [];
						while($ifl = $invfifolifoSet->fetch()){
							$stock = new stdClass;
							$stock->warehouse = $ifl["warehouse"]; 
							$stock->total_qty = floatval($ifl["total_qty"]); 
							$stock->safetystock = floatval($ifl["safetystock"]);
							$stock->leadtime = floatval($ifl["leadtime"]);
							$stockData[] = $stock;
						}
						$material->warehouse_stock = $stockData;
					}
					
					// 8. Mendapatkan PO Line dan jumlah penerimaan
					
					$polineSet = (new Set())->useTransactionManager()->MboSetRemote("poline pl")
						->where("pl.itemnum=? AND pl.receiptstatus IN ('NONE', 'PARTIAL') and po.status in ('sent', 'approved')", $itemnum)
						->setRelationship("po", "po.ponum = pl.ponum")
						->MboRemote("pl.orderqty")
						->MboRemote("pl.ponum")
						->MboRemote("pl.polinenum")
						->MboRemote("COALESCE((SELECT SUM(quantity) FROM matrectrans mt WHERE mt.ponum = pl.ponum AND mt.polinenum = pl.polinenum), 0) AS receivedqty")
						->getMboSet();
						
					$material->stock_on_the_way = 0;
				
					if($polineSet->isEmpty()){
					}else{
						$stockontheway = 0;
						
						while($pl = $polineSet->fetch()){
							$orderqty = floatval($pl["orderqty"]);
							$receivedqty = floatval($pl["receivedqty"]);
							$stockontheway += ($orderqty - $receivedqty);
						}
						
						$material->stock_on_the_way = $stockontheway;
					}
					
					$material->kit = [];
					
					$parentItemSet = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->where("subitemnum=?", $itemnum)->MboRemote("itemnum")->MboRemote("quantity")->getMboSet()->getMbo();
					
					if($parentItemSet->isEmpty()){
						$material->kit = [];
					}else{
						$parentItemnum = $parentItemSet->getString("itemnum");
						$requiredQty = floatval($parentItemSet->getString("quantity"));
						
						//10. Mendapatkan total Qty Parent Item di Inventory
					
						$invBalanceSet = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance")->where("itemnum=?", $parentItemnum)
						->MboRemote("curbal")
						->MboRemote("warehouse")
						->MboRemote("racknum")
						->MboRemote("conditiontype")
						->MboRemote("itemnum")
						->getMboSet();

						if($invBalanceSet->isEmpty()){
							continue;
						}
							
						while($ib = $invBalanceSet->fetch()){
							$subItemKit = new stdClass;
							$subItemKit->itemnum = $ib["itemnum"];
							$subItemKit->quantity = $ib["curbal"];
							$subItemKit->yieldsQty = $subItemKit->quantity*$requiredQty;
							$subItemKit->warehouse = $ib["warehouse"];
							$subItemKit->racknum = $ib["racknum"];
							$subItemKit->conditiontype = $ib["conditiontype"];
							$material->kit[] = $subItemKit;
						}
							
						
						//Simpan ke object $material
						//$material->kit [] = $kit;
					}
				
					$material->cartQty = 0;
					//11. mendapatkan daftar yang sudah di masukkan ke "cart" oleh Inventory Staff, untuk diproses menjadi PR, hasil dari "Recommended Order" dan "3D ABC Analysis"
					$invCartSet =(new Set())->useTransactionManager()->MboSetRemote("invcart")->where("itemnum=?", $itemnum)->MboRemote("sum(quantity) as cartQty")->group("itemnum")->getMboSet();
					
					if($invCartSet->isEmpty()){
						$material->cartQty = 0;
					}else{
						$invcart = $invCartSet->getMbo();
						$cartQty = floatval($invcart->getString("cartQty"));
						$material->cartQty = $cartQty;
					}
					
					//12. Mendapatkan Alternates Item 
					$material->alternates = [];
				
					$itemAlternateSet = (new Set())->useTransactionManager()->MboSetRemote("itemalternate")->where("itemnum=?", $itemnum)->MboRemote("relateditem")->getMboSet();
					if($itemAlternateSet->isEmpty()){
					}else{
						
						while($alt = $itemAlternateSet->fetch()){
							$itemAlt = new stdClass;
							$itemAlt->relateditem = $alt["relateditem"];
							$invBalSet = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance")->where("itemnum = ?", $itemAlt->relateditem)
							->MboRemote("itemnum")->MboRemote("curbal")->MboRemote("warehouse")->MboRemote("racknum")->getMboSet();
							
							$itemAlt->current_stock = [];
							
							while ($invbal = $invBalSet->fetch()){
								$ias = new stdClass;
								$ias->curbal = floatval($invbal["curbal"]); 
								$ias->warehouse = $invbal["warehouse"]; 
								$ias->racknum = $invbal["racknum"];
								$itemAlt->current_stock[] = $ias;
							}
							
							$material->alternates[] = $itemAlt;
						}
						
					}
					$jp_materials [] = $material;
				}
				//materialWorkBench
				$workTemplate->jobplan_materials = $jp_materials;
				$required_jobplan_materials [] = $workTemplate;
			}
		
			echo json_encode(['status' => "success", 'message' => "List of Job Plan Materials", "material_workbench"=>$required_jobplan_materials]);
			$tm->commit();
		} catch (Exception $e) {
			$tm->rollback();
			throw $e;
		}
	}
	
	public static function FindAvailableResourceInTeam($teamnum, $targetdate) {
			
			$teamMemberSet = (new Set())->useTransactionManager()->MboSetRemote("empteam")
								 ->setWhere("teamnum = '$teamnum'") 
								 ->order("priority ASC")
								 ->getMboSet(); 

		
			if ($teamMemberSet->isEmpty()) {
				return "team_empty"; 
			}
			
			
			while ($result = $teamMemberSet->fetch()){
				$personid = $result["personid"];
				$is_on_leave_Set = (new Set())->useTransactionManager()->MboSetRemote("resourceleave")
									   ->where("personid = ? AND ? BETWEEN startdate AND enddate", $personid, $targetdate )
									   ->getMboSet();
				
				if ($is_on_leave_Set->isEmpty()) {
					return $personid;
				}
			}
			return null;
	}
	
	
	
	public static function getTeamLeader(){
		$teamnum = Super::getParam("getTeamLeader");
		
		$set = new Set();
		$empTeamSet = $set->useTransactionManager()->MboSetRemote("empteam")->setWhere("teamnum='$teamnum' and leader = 'Y'")->MboRemote("personid")->getMboSet();
		$empTeam = $empTeamSet->getMbo();
		$personid = $empTeam->getString("personid");
		
		$set = new Set();
		$employeesSet = $set->useTransactionManager()->MboSetRemote("employees")->setWhere("personid = '$personid'")->MboRemote("fullname")->getMboSet();
		$emp = $employeesSet->getMbo();
		$fullname = $emp->getString("fullname");
		
		$response = new stdClass();
		$response->status = "success";
		$response->fullname = $fullname;
		$response->personid = $personid;
		
		echo json_encode($response);
	}
	/**
	 * Helper function baru untuk mencari WO berbasis Meter yang "menang balapan".
	 */
	 
	 private static function convertIntervalToDays($value, $unit) {
        $val = (int)$value;
        switch(strtolower($unit)) {
            case 'day': return $val;
            case 'days': return $val;
            case 'week': return $val * 7;
            case 'weeks': return $val * 7;
            case 'month': return $val * 30.44; // Rata-rata hari dalam sebulan
            case 'months': return $val * 30.44;
            case 'year': return $val * 365.25; // Mempertimbangkan tahun kabisat
            case 'years': return $val * 365.25;
            default: return 0;
        }
    }
	 /**
     * Mengecek apakah sebuah WO berbasis Waktu (dengan siklus tertentu) 
     * telah "disalip" oleh WO berbasis Meter DENGAN SIKLUS YANG SAMA.
     *
     * @param string $pmnum             Nomor PM Master.
     * @param int    $pmtimecycle       Nomor siklus Waktu yang sedang diproses.
     * @return array|null               Mengembalikan data WO meter jika ditemukan, atau null jika tidak.
     */
    private static function findSupersedingMeterWO($pmnum,$assetnum, $pmtimecycle) {
        
       
        $woSet = (new Set())->useTransactionManager()
                           ->MboSetRemote("workorder")
                           ->where("pmnum = ? AND worktype = 'PM-FREQ' AND pmmetercycle = ? AND status NOT IN ('complete', 'close', 'cancel')", 
                                  $pmnum, $pmtimecycle) 
                           ->limit(1)
                           ->getMboSet(); 
        
        if (!$woSet->isEmpty()) {
           //echo "RESULT: Found a superseding Meter WO.\n";
            return $woSet->fetch();
        }
        
       // echo "RESULT: No superseding Meter WO found for this cycle.\n";
        return null;
    }
	
	public static function generateWO(){
		$pmnum = Super::getParam("generatewo");
		$untildate = Super::getParam("untildate"); // tanggal pmwoline.targstartdate pada forecast
		// UntilDate ini akan mengendalikan Race Condition antara Frequency meter dan time supaya "ADIL"
		// Jika until date dihapus, maka logika "OR" Percuma, karena Frequency Time akan selalu menang ketika melawan Frekuensi Meter. 
		
		if(empty($pmnum) || empty($untildate)){
			slef::sendJsonResponse("error", "PM Number and Until Date is Required");
		}
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$notice_message = "Work Orders have been successfully generated from the forecast.";
		$pmSet = (new Set())->useTransactionManager()->MboSetRemote("pm")->setRelationship("assets", "pm.assetnum = assets.assetnum")
						   ->where("pm.pmnum=?", $pmnum)->getMboSet();
		
		if($pmSet->isEmpty()){
			self::sendJsonResponse("error", "PM Asset Not Found");
		}
		$pm = $pmSet->getMbo();
		$assetnum = $pm->getString("assetnum");
		$orgcode = $pm->getString("orgcode");
		$sitecode = $pm->getString("sitecode");
		$glaccount = $pm->getString("glaccount");
		$generation_logic = $pm->getString("generationlogic");
		$status = "WAITSCHED";
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try {
			$pmWoLineSet = (new Set())->useTransactionManager()
                         ->MboSetRemote("pmwoline")
                         ->where("pmnum=? AND targstartdate <= ?", $pmnum, $untildate)
                         ->getMboSet();
			
			if($pmWoLineSet->isEmpty()){
				$tm->rollback(); 
				self::sendJsonResponse("error", "No Forecasted Work Order to be generated");
				return;
			}
			
			while($forecast_row = $pmWoLineSet->fetch()){
				$wonum = $forecast_row["wonum"];
				$pmwolineid = $forecast_row["pmwolineid"];
				$forecastdate = $forecast_row["targstartdate"];
				$pmtimecycle = $forecast_row["pmtimecycle"];
				$specific_glaccount = $forecast_row["glaccount"];
				$issuperseeded = $forecast_row["issuperseeded"];
				
				if($issuperseeded == "Y") continue;
				
				if(!empty($specific_glaccount)) $glaccount = $specific_glaccount;
				
				// Pengecekan duplikasi di tabel workorder
				$woCheckSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet();
				if(!$woCheckSet->isEmpty()){
					continue; 
				}
				//echo "DEBUG STARTED:: <hr>" ;
				//echo "GENERATION LOGIC: $generation_logic <hr>";
				if ($generation_logic === 'OR') {
					
					// Cek apakah METER sudah memicu WO lebih dulu untuk siklus yang relevan.
					//echo "DEBUG generateWO () : forecastdate/targ. start date passing parameter: $forecastdate <hr>";
					$superseding_wo = self::findSupersedingMeterWO($pmnum, $assetnum, $pmtimecycle);
					
					//echo "superseding_wo: $superseding_wo <hr>";
					//echo json_encode($$superseding_wo );
					if ($superseding_wo) {
						
						$notice_message = "[SUPPRESSED] Superseded by Meter-Based " . $superseding_wo['wonum'];
						$issuperseeded = "Y";
						// Update remarks di pmwoline (forecast)
						(new Set())->useTransactionManager()
								   ->MboSetRemote("pmwoline")
								   ->where("pmwolineid = ?", $pmwolineid)
								   ->setValue("raceremarks", $notice_message)
								   ->setValue("issuperseeded", $issuperseeded)
								   ->change();
						
					continue; // Lanjut ke forecast berikutnya, jangan proses yang ini.
					}
					
				
				}
				
				//echo "WONUM: $wonum , issuperseeded: $issuperseeded <hr>";
				
				$worktype = "PM";
				// Buat "header" Work Order.
				
				(new Set())->useTransactionManager()->MboSetRemote("workorder")
					->setValue("wonum", $wonum)
					->setValue("description", $forecast_row["description"])
					->setValue("targstartdate", $forecast_row["targstartdate"])
					->setValue("targcompdate", $forecast_row["targcompdate"])
					->setValue("teamnum", $forecast_row["teamnum"])
					->setValue("owner", $forecast_row["owner"])
					->setValue("pmnum", $pmnum)
					->setValue("glaccount", $glaccount)
					->setValue("leader", $forecast_row["leader"])
					->setValue("statusdate", $enterdate)
					->setValue("enterdate", $enterdate)
					->setValue("enterby", $enterby)
					->setValue("status", $status)
					->setValue("assetnum", $assetnum)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("progress", 0)
					->setValue("worktype", $worktype)
					->setValue("forecastdate", $forecastdate)
					->setValue("pmtimecycle", $pmtimecycle)
					->add();
				
				
				//update isGenerated
				(new Set())->MboSetRemote("pmwoline")->where("pmwolineid=?", $pmwolineid)
				->setValue("isgenerated", "Y")
				->setValue("raceremarks", null)
				->change();
				
				$pmjpLineSet = (new Set())->useTransactionManager()->MboSetRemote("pmjpline")
										 ->where("pmnum=? AND pmwolineid=?", $pmnum, $pmwolineid)
										 ->order("sequence ASC")
										 ->getMboSet();

				if($pmjpLineSet->isEmpty()){
					continue; // Tidak ada JP untuk forecast ini, lanjut.
				}

				// Loop melalui setiap Job Plan yang ada di pmjpline
				while($jp_row = $pmjpLineSet->fetch()){
					$wtnum = $jp_row["wtnum"];
					
					// --- Loop untuk SEMUA task ---
					$wtTaskSet = (new Set())->useTransactionManager()->MboSetRemote("wttask")->where("wtnum = ?", $wtnum)->getMboSet();
					while($task_row = $wtTaskSet->fetch()){
						$tasklinenum = Xbo::getLinenum("wotask", "tasklinenum", ["wonum"=>$wonum]);
						(new Set())->useTransactionManager()->MboSetRemote("wotask")
							->setValue("wonum", $wonum)->setValue("wtnum", $wtnum)
							->setValue("tasklinenum", $tasklinenum)
							->setValue("description", $task_row["description"])
							->setValue("targstartdate", $targstartdate)->setValue("status", $status)
							->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
							->setValue("progress", 0)->add();
					}
					
					// --- Loop untuk SEMUA material ---
					$wtplanMaterialSet = (new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")->where("wtnum=?", $wtnum)->getMboSet();
					while($material_row = $wtplanMaterialSet->fetch()){
						$tasklinenum = Xbo::getLinenum("woplanmaterial", "planlinenum", ["wonum"=>$wonum]);
						$linecost = floatval($material_row["quantity"]) * floatval($material_row["unitcost"]);
						(new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")
							->setValue("wonum", $wonum)->setValue("itemnum", $material_row["itemnum"])
							->setValue("planlinenum", $tasklinenum)
							->setValue("quantity", $material_row["quantity"])
							->setValue("unitcost", $material_row["unitcost"])->setValue("linecost", $linecost)
							->setValue("conditiontype", $material_row["conditiontype"])
							->setValue("warehouse", $material_row["warehouse"])->setValue("racknum", $material_row["racknum"])
							->setValue("gldebitacct", $glaccount)->setValue("glcreditacct", $material_row["glcreditacct"])
							->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
							->add();
					}
				}
				
				$costData = Xbo::calculateWOCost($wonum);
				if ($costData) {
					$updateSet = (new Set())->useTransactionManager();
					$updateSet->MboSetRemote("workorder")
							  ->where("wonum = ?", $wonum)
							  ->setValue("actualcost", $costData->actualcost)
							  ->setValue("plannedcost", $costData->plannedcost)
							   ->setValue("variancecost", $costData->variancecost)
							  ->change();
				}
				
				
			}
			
			$tm->commit();
			self::sendJsonResponse("success",$notice_message );

		} catch (Exception $e) {
			$tm->rollback();
			throw $e;
		}
	}
	
	public static function generateJPFrequency($pmnum, $wonum, $woindex, $targstartdate){
        $set = new Set();
		$pmwolineid = $set->useTransactionManager()->MboSetRemote("pmwoline")->setWhere("pmnum='$pmnum' and wonum='$wonum'")->MboRemote("pmwolineid")->getMboSet()->getMbo()->getString("pmwolineid");
		
		
		$set = new Set();
		$rulesSet = $set->useTransactionManager()->MboSetRemote("pmjprule") 
						->where("pmnum = ? ", $pmnum)
						->order("frequencyvalue ASC") 
						->getMboSet(); 

		if ($rulesSet->isEmpty()) {
			return; 
		}

		//  Kumpulkan semua Job Plan kandidat berdasarkan aturan ---
		$candidates = [];
		$wo_date = new DateTime($targstartdate);
		while($rule = $rulesSet->fetch()){
			
			$ruleType = $rule["ruletype"]; 
			$isMatch = false;

			if ($ruleType == 'FREQUENCY') {
				$frequency = (int)$rule["frequencyvalue"];
				// Cek jika frekuensi valid dan woindex adalah kelipatannya
				if ($frequency > 0 && $woindex % $frequency == 0) {
					$isMatch = true;
				}
			} elseif ($ruleType == 'SPECIFIC_DATE') {
				$specificDateStr = $rule["specificdate"]; 
				// Cek jika tanggal WO sama dengan tanggal spesifik di aturan
				if (self::isDateMatch($wo_date, $specificDateStr)) {
					$isMatch = true;
				}
			}

			if ($isMatch) {
				// Jika aturan cocok, tambahkan ke daftar kandidat
				$candidates[] = [
					'jobplannum' => $rule["wtnum"], 
					'behavior'   => $rule["behaviour"],  // STACKED || OVERRIDE
					'frequency'  => (int)$rule["frequencyvalue"], 
					'sequence'   => $rule["sequence"]
				];
			}
		}

		// --- Langkah 3: Logika "Stacked" vs. "Override" ---
		$final_job_plans = [];
		
		// Cari apakah ada aturan 'OVERRIDE' di antara para kandidat
		$override_rules = array_filter($candidates, function($c) { return $c['behavior'] == 'OVERRIDE'; });

		if (!empty($override_rules)) {
			// Jika ada, cari aturan OVERRIDE dengan frekuensi TERTINGGI
			usort($override_rules, function($a, $b) {
				return $b['frequency'] <=> $a['frequency']; // Urutkan descending
			});
			$final_job_plans[] = $override_rules[0]; // Ambil hanya yang paling atas
		} else {
			// Jika tidak ada aturan OVERRIDE, maka semua kandidat adalah STACKED
			$final_job_plans = $candidates;
		}

		// Urutkan hasil akhir berdasarkan sequence
		usort($final_job_plans, function($a, $b) {
			return $a['sequence'] <=> $b['sequence'];
		});

		// --- Langkah 4: Simpan hasil akhir ke tabel pmjpline ---
		foreach ($final_job_plans as $jp_to_add) {
			
			$set = new Set();
			$set->useTransactionManager()->MboSetRemote("pmjpline") // Tabel tujuanmu
				->setValue("pmnum", $pmnum)
				->setValue("wonum", $wonum)
				->setValue("wtnum", $jp_to_add['jobplannum'])
				->setValue("sequence", $jp_to_add['sequence'])
				->setValue("pmwolineid", $pmwolineid)
				->add();
		}
	}

	/**
	 * Helper function: Membandingkan tanggal.
	 */
	private static function isDateMatch($wo_date, $specific_date_str) {
		if (empty($specific_date_str)) return false;
		try {
			$specific_date = new DateTime($specific_date_str);
			return $wo_date->format('Y-m-d') === $specific_date->format('Y-m-d');
		} catch (Exception $e) {
			return false;
		}
	}
	
    public static function generatePM() { //atau generateForecast
        
        // --- Bagian 1: Pengambilan & Validasi Input (Tidak banyak berubah) ---
        $pmnum = $_POST['generatepm'];
        $pmdesc = $_POST['pmdesc'];
        $start_date = $_POST['start_date'];
        $interval_unit = strtolower($_POST['interval_unit']);
        $interval_value = $_POST['interval_value'];
        $duration_unit = strtolower($_POST['duration_unit']);
        $duration_value = $_POST['duration_value'];
        
        $ss = new stdClass;
  
		if(empty($start_date) || empty($interval_unit) || empty($interval_value) || empty($duration_unit) || empty($duration_value)){
			$ss->msg = "input_incomplete"; 
			Set::throws($ss);
			return null;
		}

        // --- Bagian 2: Pengambilan Aturan Cerdas dari Master PM (Perubahan Kunci) ---
        $set = new Set();
        $pmSet = $set->useTransactionManager()->MboSetRemote("pm")->setWhere("pmnum='$pmnum'")->getMboSet();
        
        if ($pmSet->isEmpty()) {
            self::sendJsonResponse("error", "PM is not found");
        }

        $pm = $pmSet->getMbo();
        // Ambil Rules
        $pm_rules = [
            'holidayaction' => $pm->getString("holidayaction"), // 'BEFORE', 'AFTER', 'IGNORE'
            'allowsaturday' => $pm->getString("allowsaturday"), // Y atau N
            'allowsunday' => $pm->getString("allowsunday"),   // Y atau N
			'pmtype' => $pm->getString("pmtype"),  
			'original_leader' => $pm->getString("leader")
        ];
		
		
        $wtnum = $pm->getString("wtnum");
        $owner = $pm->getString("owner");
        $leader = $pm->getString("leader");
        $siteid = $pm->getString("sitecode");
		$teamnum = $pm->getString("teamnum");
		
		if(empty($teamnum)){
			self::sendJsonResponse("error", "Team cannot be empty");
		}
		if(empty($leader)){
			self::sendJsonResponse("error", "Leader cannot be empty");
		}
        // --- Bagian 3: Panggil Smart Engine PM
        $wo_list_objects = ActionSet::generatePMWorkOrders($start_date, $interval_unit, $interval_value, $duration_unit, $duration_value, $pmnum, $pm_rules, $siteid, $teamnum);

        // --- Bagian 4: Simpan Hasil ke Database (Sedikit Modifikasi) ---
        $count_wo_list = count($wo_list_objects);
		
        // Hapus PM yang lama dulu
        $set = new Set();
        $set->useTransactionManager()->MboSetRemote("pmwoline")->setWhere("pmnum='$pmnum'")->remove();
        // Hapus PM JP Line 
		$set = new Set();
		 $set->useTransactionManager()->MboSetRemote("pmjpline")->setWhere("pmnum='$pmnum'")->remove();
		$pmtimecycle = 1;
        foreach ($wo_list_objects as $index => $wo_obj) {
            $woindex = ($index + 1);
            $totalwo = $count_wo_list;
            $getAutoNum = new Super();
            
            $targstartdate = $wo_obj->date->format('Y-m-d H:i:s');
            $remarks = $wo_obj->remarks;
			$new_leader = $wo_obj->leader;
			$remarks2 = $wo_obj->remarks2;
			
            $wonum = $getAutoNum->getAutoNumber("WORKORDER", "update_next_value")->autonumber;
			$pmwolinenum = Xbo::getLinenum("pmwoline", "pmwolinenum", ["pmnum"=>$pmnum]);
            $set = new Set();		
            $wodesc  = "WO PM ($woindex of $totalwo): $pmnum - $pmdesc ";
            
            $set->useTransactionManager()->MboSetRemote("pmwoline")
                ->setValue("pmnum", $pmnum)
                ->setValue("wonum", $wonum)
                ->setValue("pmwolinenum", $pmwolinenum)
                ->setValue("description", $wodesc)
                ->setValue("targstartdate", $targstartdate)
                ->setValue("targenddate", $targstartdate) 
                ->setValue("wtnum", $wtnum)
                ->setValue("owner", $owner)
                ->setValue("leader", $new_leader)
                ->setValue("remarks", $remarks)
				->setValue("remarks2", $remarks2)
				->setValue("teamnum", $teamnum)
				->setValue("pmtimecycle", $pmtimecycle)
                ->add();
			$pmtimecycle = $pmtimecycle+1;
			 ActionSet::generateJPFrequency($pmnum, $wonum, $woindex, $targstartdate); 
				
        }
        $ss->msg = "generated_successfully";
        self::sendJsonResponse("success", "WO forecasting has been generated");
    }


    
    public static function generatePMWorkOrders($start_date, $interval_unit, $interval_value, $duration_unit, $duration_value, $pmnum, $pm_rules, $siteid, $teamnum) {
        
       
        $candidate_dates = [];
        $current_date = new DateTime($start_date);
        $end_date = clone $current_date;
        $end_date->modify("+$duration_value $duration_unit");

        // Masukkan tanggal mulai itu sendiri sebagai kandidat pertama
        $candidate_dates[] = clone $current_date;

        while (true) {
            $current_date->modify("+$interval_value $interval_unit");
            if ($current_date >= $end_date) {
                break;
            }
            $candidate_dates[] = clone $current_date;
        }

        // --- TAHAP 3 (PASS 2): RESOLVE SECARA KRONOLOGIS ---
        $final_wo_list = [];
        $scheduled_dates_for_collision_check = [];
        $last_resolved_date_for_floating = new DateTime($start_date); // Untuk PM Floating

        foreach ($candidate_dates as $candidate) {
            
            $date_to_resolve = clone $candidate;

            // Untuk PM FLOATING, kandidat dihitung dari tanggal SELESAI sebelumnya.
            if ($pm_rules['pmtype'] == 'FLOATING' && $candidate != $candidate_dates[0]) {
                 // Ambil tanggal selesai sebelumnya, tambahkan interval
                 $date_to_resolve = clone $last_resolved_date_for_floating;
                 $date_to_resolve->modify("+$interval_value $interval_unit");
            }

            $resolved_schedule = ActionSet::resolveScheduleConflict(
                $date_to_resolve,
                $pm_rules,
                $scheduled_dates_for_collision_check,
				$teamnum
            );
            
            $final_wo_list[] = $resolved_schedule;
            $scheduled_dates_for_collision_check[] = $resolved_schedule->date->format('Y-m-d');
            
            // Update tanggal terakhir untuk PM Floating berikutnya
            if ($pm_rules['pmtype'] == 'FLOATING') {
                $last_resolved_date_for_floating = clone $resolved_schedule->date;
            }
        }
        
        return $final_wo_list;
    }
	
	
	private static function resolveScheduleConflict($candidate_date, $rules, $other_scheduled_dates, $teamnum) {
        $final_date = clone $candidate_date;
        $remarks = "";
		$remarks_resource = "";
		$final_leader = null;
        $original_date_str = $candidate_date->format('Y-m-d');
		$original_leader = $rules['original_leader']; 
        $is_rescheduled = false;
		
        while (true) {
            $current_date_str = $final_date->format('Y-m-d');
            $conflict_reason = null;

            // 1. Cek WO Collision 
            if (in_array($current_date_str, $other_scheduled_dates)) {
                $conflict_reason = "Date already scheduled (Collision)";
            }

            // 2. Cek Hari Libur
            if (!$conflict_reason) {
				$holidaySet = new Set();
				$holidays = $holidaySet->useTransactionManager()->MboSetRemote("pmholiday")->getMboSet();

				
				
				while($holy = $holidays->fetch()){
					$holidaycode = $holiday["holidaycode"];
					$holiday_desription = $holiday["description"];
					$startdate = $holiday["startdate"];
					$enddate = $holiday["enddate"];
					
					$holiday_start = new DateTime($startdate);
					$holiday_end = new DateTime($enddate);
					
					if ($final_date >= $holiday_start && $final_date <= $holiday_end) {
                        // Apakah aturannya BUKAN 'IGNORE'?
                        if ($rules['holidayaction'] != 'IGNORE') {
                            // Jika aturannya 'BEFORE' atau 'AFTER', maka hari libur ini harus digeser ke tanggal before atau after
                            $conflict_reason = "Holiday: " . $holiday->getString('description');
                            break; // conflict, exiting from loop
                        }
                        // kalau aturannya IGNORE, do nothing
                    }
				}
				
				
            }
			
            // 3. Cek Akhir Pekan yang Dilarang
            if (!$conflict_reason) {
				
                $day_of_week = (int)$final_date->format('N'); // format Number, 6 untuk Sabtu, 7 untuk Minggu
                if ($day_of_week == 6 && $rules['allowsaturday'] != 'Y') {
                    $conflict_reason = "Saturday is not allowed";
                } elseif ($day_of_week == 7 && $rules['allowsunday'] != 'Y') {
                    $conflict_reason = "Sunday is not allowed";
                }
            }
			
			 if (!$conflict_reason) {
                // Setelah tanggal valid, cek apa ada orang yang bisa bekerja.
                $available_person = self::FindAvailableResourceInTeam($teamnum, $current_date_str);
                if ($available_person === "team_empty") {
                    $conflict_reason = "Team {$teamnum} has no active members.";
                } elseif ($available_person === null) {
                    $conflict_reason = "No available resources in team {$teamnum} on this date.";
                } else {
                    $final_leader = $available_person;
                }
            }
			
            // Jika tidak ada konflik, tanggal ini valid. Keluar dari loop.
            if (!$conflict_reason) {
                break;
            }

            // --- Jika ada konflik, lakukan penjadwalan ulang ---
            $is_rescheduled = true;

            // Buat remarks hanya pada iterasi pertama
            if (empty($remarks)) {
                 $remarks = "Rescheduled from " . $original_date_str . ". Reason: " . $conflict_reason . ".";
            }
            // Geser tanggal berdasarkan aturan 'holidayaction'
            $direction = ($rules['holidayaction'] == 'BEFORE') ? '-1 day' : '+1 day';
            $final_date->modify($direction);
        }
		
		 // --- Remarks untuk Resource ---
		// loop end, bandingkan leader asli dengan leader yang ditemukan.
		if ($final_leader != $original_leader) {
			$remarks_resource = "Leader changed from {$original_leader}. Auto-assigned to {$final_leader} based on availability and priority.";
		}
	
		
		
		 // Buat objek hasil. Jika tidak ada reschedule, remarks akan kosong.
        $result = new stdClass();
        $result->date = $final_date;
        $result->remarks = $remarks;
		$result->leader = $final_leader;
		$result->remarks2 = $remarks_resource;
        return $result;
		
    }
} 

Class ValidateTables {
	
}
	

?>