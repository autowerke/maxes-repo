<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function getLaborCost(){
		$payload = Super::getParam("getLaborCost");
		$teamnum = $payload["teamnum"];
		$sumUnitCost = (new Set())->useTransactionManager()->MboSetRemote("empteam")->where("teamnum=?", $teamnum)->MboRemote("sum(unitcost) as sumUnitCost")->getMboValue("sumUnitCost");

		Super::sendJsonResponse("success", "TEAMNUM: $teamnum", ["sumUnitCost"=>$sumUnitCost]);
	}
	public static function adjustMargin()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 1. Ambil & Validasi Parameter
			$payload = Super::getParam("adjustMargin");
			$salesQuoteNum = $payload["salesquotenum"] ?? null;
			$margins = $payload["newmargins"] ?? [];

			if (empty($salesQuoteNum) || empty($margins)) {
				throw new InvalidArgumentException("Invalid Payload: Sales Quote Number and Margins are required.");
			}

			$lineIds = array_column($margins, 'rowindex');
			
			$marginMap = [];
			foreach ($margins as $m) {
				$marginMap[$m['rowindex']] = $m;
			}

			// 2. Ambil Data Sales Quotation Line dari DB
			$linesSet = (new Set())->useTransactionManager()
				->MboSetRemote("salesquotationline")
				->whereIn("salesquotationlineid", $lineIds)
				->where("salesquotenum = ?", $salesQuoteNum)
				->MboRemote("salesquotationlineid, linecost, sellqty, taxcode")
				->getMboSet();
			
			$linesData = $linesSet->fetchData();

			if (empty($linesData)) {
				$tm->commit();
				Super::sendJsonResponse("error", "No valid lines found.");
				return;
			}

			// 3. Lakukan Perhitungan & Siapkan Data Update
			$paramsUnitPrice = [];
			$paramsLinePrice = [];
			$paramsMargin = [];
			$paramsMarginType = [];
			$paramsTaxPrice = [];

			$caseUnitPrice = "";
			$caseLinePrice = "";
			$caseMargin = "";
			$caseMarginType = "";
			$caseTaxPrice = "";

			foreach ($linesData as $row) {
				$id = $row['salesquotationlineid'];
				$input = $marginMap[$id];
				
				$targetPercent = (float)$input['newmargin'];
				$calcType = $input['margintype'];
				
				$lineCost = (float)$row['linecost'];
				$qty = (float)$row['sellqty'];
				if ($qty <= 0) $qty = 1;

				$newLinePrice = 0;
				if ($calcType == "markuponcost") {
					$newLinePrice = $lineCost * (1 + ($targetPercent / 100));
				} else {
					$safeMargin = ($targetPercent >= 99.9) ? 99.9 : $targetPercent;
					$newLinePrice = $lineCost / (1 - ($safeMargin / 100));
				}
				$newUnitPrice = $newLinePrice / $qty;

				$taxCode = $row['taxcode'];
				$newTaxPrice = 0;
				if (!empty($taxCode)) {
					$taxRate = (float)XboSet::getTaxRate($taxCode); 
					$newTaxPrice = $newLinePrice * ($taxRate / 100);
				}

				// Bangun Query CASE WHEN
				$caseUnitPrice .= "WHEN ? THEN ? ";
				array_push($paramsUnitPrice, $id, $newUnitPrice);

				$caseLinePrice .= "WHEN ? THEN ? ";
				array_push($paramsLinePrice, $id, $newLinePrice);

				$caseMargin .= "WHEN ? THEN ? ";
				array_push($paramsMargin, $id, $targetPercent);

				$caseMarginType .= "WHEN ? THEN ? ";
				array_push($paramsMarginType, $id, $calcType);
				
				$caseTaxPrice .= "WHEN ? THEN ? ";
				array_push($paramsTaxPrice, $id, $newTaxPrice);
			}

			// 4. Eksekusi Kueri UPDATE
			if (!empty($lineIds)) {
				$idsPlaceholders = rtrim(str_repeat('?,', count($lineIds)), ',');
				
				$sql = "UPDATE salesquotationline SET 
							unitprice = CASE salesquotationlineid $caseUnitPrice END,
							lineprice = CASE salesquotationlineid $caseLinePrice END,
							margin = CASE salesquotationlineid $caseMargin END,
							margintype = CASE salesquotationlineid $caseMarginType END,
							taxprice = CASE salesquotationlineid $caseTaxPrice END
						WHERE salesquotationlineid IN ($idsPlaceholders)";
				
				// [FIX] Gabungkan semua parameter dalam urutan yang BENAR
				$allParams = array_merge(
					$paramsUnitPrice,
					$paramsLinePrice,
					$paramsMargin,
					$paramsMarginType,
					$paramsTaxPrice,
					$lineIds // Parameter untuk IN clause ditaruh paling akhir
				);
				
				DB::executePrepared($sql, $allParams);
			}

			// 5. Hitung Ulang & Update Header
			$totalsSet = (new Set())->useTransactionManager()
				->MboSetRemote("salesquotationline")
				->where("salesquotenum = ?", $salesQuoteNum)
				->MboRemote("SUM(lineprice) as pretaxtotal, SUM(taxprice) as totaltax")
				->getMboSet()
				->fetch();

			$newPreTaxTotal = (float)($totalsSet['pretaxtotal'] ?? 0);
			$newTotalTax = (float)($totalsSet['totaltax'] ?? 0);
			$newTotalAmount = $newPreTaxTotal + $newTotalTax;

			(new Set())->useTransactionManager()
				->MboSetRemote("salesquotation")
				->where("salesquotenum = ?", $salesQuoteNum)
				->setValue("pretaxtotal", $newPreTaxTotal)
				->setValue("totaltax", $newTotalTax)
				->setValue("totalprice", $newTotalAmount) // Sesuaikan nama kolom jika beda
				->change();

			$tm->commit();
			Super::sendJsonResponse("success", "Margins updated and totals recalculated successfully.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to adjust margin: " . $e->getMessage());
		}
	}
	public static function getItemPriceInfo(){
		$itemnum = Super::getParam("getItemPriceInfo");
		$id = Super::getParam("id");

		
		if(!$itemnum || !$id){
			Super::sendJsonResponse("error", "Itemnum is null");
		}

		$vibSet = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance")->where("v_invbalanceid=?", $id)->getMboSet()->fetch();
		$warehouse = $vibSet["warehouse"];
		$racknum = $vibSet["racknum"];
		$conditiontype = $vibSet["conditiontype"];


		$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item i")
		->setRelationship("v_invbalance vib", "i.itemnum = vib.itemnum")
		->setRelationship("invprice ip", "i.itemnum = ip.itemnum")
		->limit(1)
		->where("i.itemnum=? ", $itemnum)
		->MboRemote("i.description, vib.avgcost, i.issueunit, vib.warehouse, vib.racknum")
		->getMboSet();

		$item = $itemSet->getMbo();

		$description = $item->getString("description");
		$unitcost = $item->getString("avgcost");

		$unitprice= 0;
		//==== GET UNIT PRICE ====//
		$unitprice = (new Set())->useTransactionManager()->MboSetRemote("invprice")->where("defaultprice=? and itemnum=?", "Y", $itemnum)->getMboValue("unitprice");
		
		
		if($unitprice == 0 ){
			$pricecode = (new Set())->useTransactionManager()->MboSetRemote("pricegroup")->where("pricerate=100")->getMboValue("pricecode");
			$unitprice = (new Set())->useTransactionManager()->MboSetRemote("invprice")->where("pricecode=? AND itemnum=?", $pricecode, $itemnum)->getMboValue("unitprice");
		}
		$issueunit = $item->getString("issueunit");
		$unitcost = floatval($unitcost);
		$unitprice = floatval($unitprice);
		Set::throws(["description"=>$description, "unitcost"=>$unitcost, "unitprice"=>$unitprice, "issueunit"=>$issueunit, "warehouse"=>$warehouse, "racknum"=>$racknum, "conditiontype"=>$conditiontype]);
	}
	public static function getServicePriceInfo(){

		$serviceitemnum = Super::getParam("getServicePriceInfo");
		$set = new Set();
		$serviceitemSet = $set->MboSetRemote("serviceitems")->setWhere("serviceitemnum='$serviceitemnum'")
		->MboRemote("description, costvolatilityrisk, leadtimerisk, complexityrisk, serviceunit, stdcost")->getMboSet();
		$serviceitem = $serviceitemSet->getMbo();
		$description = $serviceitem->getString("description");
		$stdcost = $serviceitem->getString("stdcost");
		$costvaolatilityrisk = $serviceitem->getString("costvolatilityrisk");
		$leadtimerisk = $serviceitem->getString("leadtimerisk");
		$complexityrisk = $serviceitem->getString("complexityrisk");
		$serviceunit = $serviceitem->getString("serviceunit");
		$riskfactor = (1 + ($costvaolatilityrisk + $leadtimerisk + $complexityrisk) / 100);
		$unitcost = $stdcost * $riskfactor;
		$unitcost = number_format((float)$unitcost, 2, '.', '');
		Set::throws(["description"=>$description, "unitcost"=>$unitcost, "unitprice"=>$stdcost, "serviceunit"=>$serviceunit]);
	}
	public static function SumSalesQuotationCost()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$salesquotenum = Super::getParam("sumsalesquotationcost");
			if (empty($salesquotenum)) {
				throw new InvalidArgumentException("Sales Quotation Number is required.");
			}

			// 1. Recalculate all lines first!
			// This ensures the header totals are based on the latest estimation data.
			self::RecalculateSQLinesCost($salesquotenum);

			// 2. Sum up the Header Totals (Your existing logic, optimized)
			$totalsSet = (new Set())->useTransactionManager()
				->MboSetRemote("salesquotationline")
				->where("salesquotenum = ?", $salesquotenum)
				->MboRemote("SUM(taxprice) as totaltax, SUM(lineprice) as pretaxtotal")
				->getMboSet()
				->fetch(); // Fetch once

			$totaltax = (float)($totalsSet['totaltax'] ?? 0);
			$pretaxtotal = (float)($totalsSet['pretaxtotal'] ?? 0);
			$totalprice = $totaltax + $pretaxtotal;

			// 3. Update the Header Table (salesquotation) with the new totals
			// It's good practice to save these calculated totals to the DB
			(new Set())->useTransactionManager()
				->MboSetRemote("salesquotation")
				->where("salesquotenum = ?", $salesquotenum)
				->setValue("totaltax", $totaltax)
				->setValue("pretaxtotal", $pretaxtotal)
				->setValue("totalprice", $totalprice) // Assuming you have this column
				->change();

			$tm->commit();

			// 4. Return the result
			Set::throws([
				"totalprice" => $totalprice, 
				"totaltax" => $totaltax, 
				"pretaxtotal" => $pretaxtotal
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}

	public static function RecalculateSQLinesCost($salesquotenum)
	{

		$sumMaterial = "(SELECT COALESCE(SUM(linecost), 0) FROM sqestmaterial WHERE salesquotenum = salesquotationline.salesquotenum AND salesquotationlinenum = salesquotationline.salesquotationlinenum)";
		$sumService  = "(SELECT COALESCE(SUM(linecost), 0) FROM sqestservice WHERE salesquotenum = salesquotationline.salesquotenum AND salesquotationlinenum = salesquotationline.salesquotationlinenum)";
		$sumLabor    = "(SELECT COALESCE(SUM(linecost), 0) FROM sqestlabor WHERE salesquotenum = salesquotationline.salesquotenum AND salesquotationlinenum = salesquotationline.salesquotationlinenum)";
		$sumTool     = "(SELECT COALESCE(SUM(linecost), 0) FROM sqesttool WHERE salesquotenum = salesquotationline.salesquotenum AND salesquotationlinenum = salesquotationline.salesquotationlinenum)";

		// Total Line Cost Formula
		$totalLineCost = "($sumMaterial + $sumService + $sumLabor + $sumTool)";

		// Unit Cost Formula (Handle Division by Zero)
		// If sellqty > 0, unitcost = linecost / sellqty. Else, unitcost = linecost.
		$unitCostLogic = "CASE WHEN sellqty > 0 THEN ($totalLineCost / sellqty) ELSE $totalLineCost END";
		// Build the Massive Update Query
		$sql = "UPDATE salesquotationline
				SET 
					linecost = $totalLineCost,
					unitcost = $unitCostLogic
				WHERE salesquotenum = ?";

		// Execute safely
		DB::executePrepared($sql, [$salesquotenum]);
	}

	// Helper Function to automatically approve PR - related PM Campaign once SO has been created 
	public static function approveRelatedPR($sonum, $pmcampaignnum){
		if(empty($sonum) || empty($pmcampaignnum)){
			return false;
		}
		$status =  "SO-CREATED";
		$prnum = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("pmcampaignnum=?", $pmcampaignnum)->getMboValue("prnum");
		
		(new Set())->useTransactionManager()->MboSetRemote("pr")->where("pmcampaignnum=?", $pmcampaignnum)
		->setValue("sonum", $sonum)
		->setValue("status", $status)
		->setValue("statusdate", Xbo::setEnterDate())
		->change();


		(new Set())->useTransactionManager()->MboSetRemote("prstatus")
		->setValue("prnum", $prnum)
		->setValue("status", $status)
		->setValue("changeby", Xbo::getLoginUser())
		->setValue("changedate", Xbo::setEnterDate())
		->setValue("remarks", "SO has been already approved by customer on Campaign : $pmcampaignnum")
		->add();
		
		return true;
		
	}
	public static function createSO()
	{
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			
			$salesquotenum = Super::getParam("salesquotenum");
			$sonum = Super::getParam("sonum");
			$sodesc = Super::getParam("sodesc");

			if (empty($salesquotenum) || empty($sonum)) {
				throw new InvalidArgumentException("Sales Quotation Number and SO Number are required.");
			}
			
			
			$existingSoNum = (new Set())->useTransactionManager()
										->MboSetRemote("so")
										->where("salesquotenum = ?", $salesquotenum)
										->getMboValue("sonum");

			if ($existingSoNum) {

				$tm->commit(); 
				Super::sendJsonResponse("error", "Sales Order has been exist: $existingSoNum");
				return;
			}

			
			$sqData = (new Set())->useTransactionManager()
				->MboSetRemote("salesquotation")
				->where("salesquotenum = ?", $salesquotenum)
				->getMboSet()
				->fetch();

			if (!$sqData) {
				throw new Exception("Sales Quotation '$salesquotenum' not found.");
			}

			$sqLines = (new Set())->useTransactionManager()
				->MboSetRemote("salesquotationline")
				->where("salesquotenum = ?", $salesquotenum)
				->getMboSet()
				->fetchData();

				// Buat record SO (header)
				$enterDate = Xbo::setEnterDate();
				$enterBy = Xbo::getLoginUser();
				
				(new Set())->useTransactionManager()->MboSetRemote("so")
				->setValue("sonum", $sonum)
				->setValue("description", $sodesc)
				->setValue("customercode", $sqData['customercode'])
				->setValue("salesquotenum", $salesquotenum)
				->setValue("preordernum", $sqData['preordernum'])
				->setValue("contactname", $sqData['contactname'])
				->setValue("employeesid", $sqData['employeesid'])
				->setValue("pmcampaignnum", $sqData['pmcampaignnum'])
				->setValue("bookingdate", $sqData['bookingdate'])
				->setValue("status", "NEW")
				->setValue("statusdate", $enterDate)
				->setValue("orderdate", $enterDate)
				->setValue("enterdate", $enterDate)
				->setValue("enterby", $enterBy)
				->setValue("changeby", $enterBy)
				->setValue("changedate", $enterDate)
				->setValue("orgcode", $sqData['orgcode'])
				->setValue("sitecode", $sqData['sitecode'])
				->setValue("pretaxtotal", $sqData['pretaxtotal'])
				->setValue("totaltax", $sqData['totaltax'])
				->setValue("totalprice", 0) 
				->setValue("discountpercentage", 0)
				->setValue("discountprice", 0)
				->setValue("discountedtotaltax", $sqData['totaltax'])
				->setValue("discountedtotalprofit", $sqData['pretaxtotal'])
				->setValue("discountedtotalprice", 0) 
				->setValue("unpaidprice", 0) 
				->setValue("paymenttype", "cashonbank")
				->add();

			// Buat record SO Status
			(new Set())->useTransactionManager()->MboSetRemote("sostatus")
				->setValue("sonum", $sonum)
				->setValue("changeby", $enterBy)
				->setValue("changedate", $enterDate)
				->setValue("remarks", "Created from Sales Quotation: $salesquotenum")
				->setValue("status", "new")
				->add();

			// addStream()
			if (!empty($sqLines)) {
				$soLinesData = [];
				foreach ($sqLines as $line) {
					$itemnum = $line['itemnum'];
					$issuetype = (new Set())->MboSetRemote("inventory")->where("itemnum=? and issuetype is not null", $itemnum)->limit(1)->getMboValue("issuetype"); // default issue type. 
					$soLinesData[] = [
						"sonum" => $sonum,
						"solinenum" => $line['salesquotationlinenum'],
						"salesquotenum" => $line['salesquotenum'],
						"salesquotationlinenum" => $line['salesquotationlinenum'],
						"linetype" => $line['linetype'],
						'itemnum' => $itemnum,
						"description" => $line['description'],
						"remarks" => $line['remarks'],
						"unitprice" => $line['unitprice'],
						"lineprice" => $line['lineprice'],
						"unitpricediscount" => $line['unitprice'],
						"linepricediscount" => $line['lineprice'],
						"taxcode" => $line['taxcode'],
						"taxprice" => $line['taxprice'],
						"taxpricediscount" => $line['taxprice'],
						"enterby" => $enterBy,
						"enterdate" => $enterDate,
						"sellqty" => $line['sellqty'],
						"orderunit" => $line['orderunit'],
						"unitcost" => $line['unitcost'],
						"linecost" => $line['linecost'],
						"serviceitemnum" => $line['serviceitemnum'],
						"customercode" => $sqData['customercode'],
						"wonum" => $line['wonum'],
						"glcreditacct" => $line['glcreditacct'],
						"gldebitacct" => $line['gldebitacct'],
						"requireddate" => $line['requireddate'],
						"margin" => $line['margin']
					];
				}
				(new Set())->useTransactionManager()->MboSetRemote("soline")->addStream($soLinesData);


				//Copy salesquotationterm
				$sqTerms = [];
				$sqTermSet = (new Set())->useTransactionManager()->MboSetRemote("salesquotationterm")->where("salesquotenum=?", $salesquotenum)
				->MboRemote("termlinenum, termcode , remarks, description")->getMboSet();
				if(!$sqTermSet->isEmpty()){
					while($sqTerm = $sqTermSet->fetch()){
						$sqTerms [] = [
							"sonum" => $sonum,
							"termlinenum" => $sqTerm["termlinenum"],
							"termcode" => $sqTerm["termcode"],
							"remarks" => $sqTerm["remarks"],
							"description" => $sqTerm["description"],
							"enterby" => $enterBy,
							"enterdate" => $enterDate,
							"customercode" => $sqData['customercode']
						];
					}
					(new Set())->useTransactionManager()->MboSetRemote("soterm")->addStream($sqTerms);
				}
			}

			


			// Update Sales Quotation Line dengan nomor SO
			(new Set())->useTransactionManager()
				->MboSetRemote("salesquotationline")
				->where("salesquotenum = ?", $salesquotenum)
				->setValue("sonum", $sonum)
				->change();

			// Update Autonumber
			(new Super())->getAutoNumber("so", "update_next_value");
			self::approveRelatedPR($sonum, $sqData['pmcampaignnum']);
			$tm->commit();
			Super::sendJsonResponse("success", "Sales Order $sonum has been created");
		} catch (Exception $e) {
			// Jika ada error di manapun, semua akan di-rollback
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed while creating SO: " . $e->getMessage());
		}
	}

	public static function validateStatusChange(){
		$payload = Super::getParam("validateStatusChange");
		$new_status = strtolower($payload["new_status"]);
		$salesquotenum = $payload["salesquotenum"];

		$sqSet = (new Set())->useTransactionManager()->MboSetRemote("salesquotation")->where("salesquotenum = ? ", $salesquotenum)->getMboSet();
		$sq = $sqSet->getMbo();
		$customercode = $sq->getString("customercode");
		$bookingdate =  $sq->getString("bookingdate");
		$employeesid =  $sq->getString("employeesid");
		if($new_status == "wappr" || $new_status == "cancel"){
			//check apakah ada record sales order 
			$sqlSet = (new Set())->MboSetRemote("salesquotationline")
			->where("salesquotenum=? AND sonum IS NOT NULL", $salesquotenum)
			->getMboSet();
			
			if(!$sqlSet->isEmpty()){
				$sq = $sqlSet->getMbo();
				$sonum = $sq->getString("sonum");
				Super::sendJsonResponse("error", "Cannot Change to $new_status. This SQ has related to Sales Orders: $sonum");
			}
		}

		$missingFields = [];
		if($new_status == "approved"){
			
			if(empty($customercode)) {
				$missingFields[] = "Customer Code cannot be null.";
			}

			if(empty($bookingdate)) {
				$missingFields[] = "Booking Date cannot be null.";
			}

			if(empty($employeesid)) {
				$missingFields[] = "Sales Representative(Employee ID) cannot be null.";
			}

			$salesquotationlineSet = (new Set())->useTransactionManager()->MboSetRemote("salesquotationline")->where("salesquotenum=?", $salesquotenum)->getMboSet();
			if($salesquotationlineSet->isEmpty()){
				Super::sendJsonResponse("error", "No Lines found to be approved!");
			}
		}

		if(!empty($missingFields)) {
			$message = "Please fill in the following mandatory fields: <br>" . implode("<br>", $missingFields);
			Super::sendJsonResponse("error", $message, array_unique($missingFields));
			return;
		}
		Super::sendJsonResponse("success", "Validation passed. Ready to change status.");
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

		if($childTable == "salesquotationline"){
			//Check SO
			foreach($selectedRows as $row){
				$rowid = $row["salesquotationlineid"];
				$salesquotationline = (new Set())->useTransactionManager()->MboSetRemote("salesquotationline")->where("salesquotationlineid=?", $rowid)->getMboSet()->getMbo();
				$sonum = $salesquotationline->getString("sonum");
				$wonum = $salesquotationline->getString("wonum");
				$salesquotationlinenum =  $salesquotationline->getString("salesquotationlinenum");
				if(empty($sonum)){
					if(empty($wonum)){
						$_POST["delChildPackage"] = $dataPackage;
						Super::deleteChildren();
						//DELETE RESOURCES 
						(new Set())->useTransactionManager()->MboSetRemote("sqestmaterial")->where("sonum=? AND salesquotationlinenum=?", $sonum, $salesquotationlinenum)->remove();
						(new Set())->useTransactionManager()->MboSetRemote("sqestlabor")->where("sonum=? AND salesquotationlinenum=?", $sonum, $salesquotationlinenum)->remove();
						(new Set())->useTransactionManager()->MboSetRemote("sqestservice")->where("sonum=? AND salesquotationlinenum=?", $sonum, $salesquotationlinenum)->remove();
						(new Set())->useTransactionManager()->MboSetRemote("sqesttool")->where("sonum=? AND salesquotationlinenum=?", $sonum, $salesquotationlinenum)->remove();
					}else{
						Super::sendJsonResponse("error", "Cannot Delete Sales Quotation Line: $salesquotationlinenum because it is linked to Work Order: $wonum");
					}
				}else{
					Super::sendJsonResponse("error", "Cannot Delete Sales Quotation Line: $salesquotationlinenum because it is linked to Sales Order: $sonum");
				}
			}
		}
	}
}
?>