<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function MaximoAddMR(){
		$mrnum = $_POST["maximo_add_mrnum"];
		$description = $_POST["description"];
		$status = $_POST["status"];
		$statusdate = $_POST["statusdate"];
		$wonum = $_POST["wonum"];
		$requestedby = $_POST["requestedby"];
		$requesteddate = $_POST["requesteddate"];
		$enterby = $_POST["enterby"];
		$enterdate = $_POST["enterdate"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$invreservenum = $_POST["invreservenum"];
		$wc = "mrnum='$mrnum' and orgcode='$orgcode' and sitecode ='$sitecode'";
		$maximo_mrid =  $_POST["mrid"];
		$set = new Set();
		$mrSet = $set->MboSetRemote("mr")->setWhere($wc)->getMboSet();
		$counts = $mrSet->counts();
		
		if($counts ==0){
			$set = new Set();
			$set->MboSetRemote("mr")
			->setValue("mrnum", $mrnum)
			->setValue("description", $description)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("wonum", $wonum)
			->setValue("requestedby", $requestedby)
			->setValue("requesteddate", $requesteddate)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("invreservenum", $invreservenum)
			->add();
			$msg = "added";
		}else{
			$set = new Set();
			$set->MboSetRemote("mr")->setWhere($wc)
			->setValue("description", $description)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("wonum", $wonum)
			->setValue("requestedby", $requestedby)
			->setValue("requesteddate", $requesteddate)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("invreservenum", $invreservenum)
			->change();
			$msg = "changed";
		}
	
		$aa = XboSet::createBarcode("mr", $maximo_mrid, $mrnum);
		$ss = new stdClass;
		$ss->msg = $msg;
		$ss->objectname = "mr";
		$ss->barcode_folder = $aa->barcode_folder;
		$ss->filePath = $aa->filePath;
		$ss->barcode_content = $aa->barcode_content;
		Set::throws($ss);
	}
	
	public static function MaximoAddMRMaterial(){
		$mrnum = $_POST["maximo_add_mrmaterial"];
		$wonum = $_POST["wonum"];
		$invreservenum = $_POST["invreservenum"];
		$itemnum = $_POST["itemnum"];
		$quantity = $_POST["quantity"];
		$unitcost = $_POST["unitcost"];
		$linecost = $_POST["linecost"];
		$conditiontype = $_POST["conditiontype"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$enterby = $_POST["enterby"];
		$enterdate = $_POST["enterdate"];
		
		$rackval = Xbo::RackWhereClause($racknum);
		$condval = Xbo::CondTypeWhereClause($conditiontype);
		
		$wc = "itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval";
		$mrlinenum = Xbo::getLineNumber("mrline", "mrlinenum", "mrnum='$mrnum'");
		
		
		$set = new Set();
		$set->MboSetRemote("mrline")->setWhere($wc)->remove();
		
		$set->MboSetRemote("mrline")
		->setValue("mrnum", $mrnum)
		->setValue("mrlinenum", $mrlinenum)
		->setValue("wonum", $wonum)
		->setValue("invreservenum", $invreservenum)
		->setValue("itemnum", $itemnum)
		->setValue("quantity", $quantity)
		->setValue("unitcost", $unitcost)
		->setValue("linecost", $linecost)
		->setValue("conditiontype", $conditiontype)
		->setValue("warehouse", $warehouse)
		->setValue("racknum", $racknum)
		->setValue("enterby", $enterby)
		->setValue("enterdate", $enterdate)
		->add();
		
		
		$set = new Set();
		$wcinv = "mrnum='$mrnum' and wonum='$wonum' and $wc";
		$set->MboSetRemote("invreserve")->setWhere($wcinv)
		->remove();
		
		$set->MboSetRemote("invreserve")
		->setValue("mrnum", $mrnum)
		->setValue("wonum", $wonum)
		->setValue("invreservenum", $invreservenum)
		->setValue("itemnum", $itemnum)
		->setValue("quantity", $quantity)
		->setValue("warehouse", $warehouse)
		->setValue("racknum", $racknum)
		->setValue("conditiontype", $conditiontype)
		->setValue("enterby", $enterby)
		->setValue("enterdate", $enterdate)
		->add();
		
		$set = new Set();
		$wcwh = "warehouse='$warehouse'";
		$whset = $set->MboSetRemote("warehouse")->setWhere("warehouse='$warehouse'")
		->getMboSet();
		
		$countwh = $whset->counts();
		if($countwh == 0){
			$set->MboSetRemote("warehouse")
			->setValue("warehouse", $warehouse)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->add();
		}
		
		$set = new Set();
		$wcwh = "warehouse='$warehouse'";
		$whset = $set->MboSetRemote("warehouseline")->setWhere("warehouse='$warehouse' and racknum='$racknum'")
		->getMboSet();
		
		$countwh = $whset->counts();
		$countwh = $whset->counts();
		if($countwh == 0){
			$warehouselinenum =  Xbo::getLineNumber("warehouseline", "warehouselinenum", "warehouse='$warehouse'");
			$set->MboSetRemote("warehouseline")
			->setValue("warehouse", $warehouse)
			->setValue("warehouselinenum", $warehouselinenum)
			->setValue("racknum", $racknum)
			->setValue("racktype", $racktype)
			->add();
		}
		
		
		
		$msg = "added";
		$ss->msg = $msg;
		$ss->objectname = "mrline";
		Set::throws($ss);
	}
	
	public static function ValidateIssueItem()

	{
		
		$payload = Super::getParam("validateIssueItem");
		$rowid = $payload["rowid"];
		$wonum =  $payload["wonum"];
		$set = new Set();
		$invbalanceSet = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance vib")
		->setRelationship("item", "item.itemnum=vib.itemnum ")->where("vib.v_invbalanceid=?", $rowid)
		->MboRemote("vib.itemnum")->MboRemote("vib.warehouse")->MboRemote("vib.racknum")->MboRemote("vib.avgcost")
		->MboRemote("vib.conditiontype")->MboRemote("vib.curbal")->MboRemote("vib.orgcode")
		->MboRemote("vib.sitecode")->MboRemote("item.stocktype")->getMboSet();

		$invbal = $invbalanceSet->getMbo();
	
		$itemnum = $invbal->getString("itemnum");
		$warehouse = $invbal->getString("warehouse");
		$racknum = $invbal->getString("racknum");
		$conditiontype = $invbal->getString("conditiontype");		
		$unitcost = $invbal->getString("avgcost");	
		$quantity = 1;
		$linecost = $unitcost*$quantity;

		$sitecode = $invbal->getString("sitecode");
		$curbal = $invbal->getString("curbal");
		$orgcode = $invbal->getString("orgcode");

		if ($unitcost == null) $unitcost = 0;
		$glcreditacct = Xbo::getGLAccount($itemnum);
		$gldebitacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode);
		if ($curbal < 1) {
			echo json_encode(["msg" => "ZeroBalance"]);
		}
		if ($curbal >= 1) {
			$enterby = Xbo::getLoginUser();
			$enterdate = date("Y-m-d h:i:s");
			echo json_encode(["enterby" => $enterby, "enterdate" => $enterdate, 
			"warehouse" => $warehouse, "racknum" => $racknum, "conditiontype" => $conditiontype, 
			"curbal" => $curbal, "unitcost" => $unitcost, "gldebitacct" => $gldebitacct, "quantity"=>$quantity,
			"glcreditacct" => $glcreditacct, "unitcost" => $unitcost, "linecost"=>$linecost]);
		}
	}
	public static function RemoveReservedItems()
	{
		$mrnum = $_GET["removereserveditems"];
		$set = new Set();
		$set->MboSetRemote("invreserve")->setWhere("mrnum='$mrnum'")->remove();
	}
	public static function AddItemToInvReserve()
	{
		//start mr
		$payload = Super::getParam("addinvreserve");
		$mrnum = $payload["mrnum"];

		// Get MR Header Info (Requester & Required Date)
		
		$mrHeaderSet = (new Set())->useTransactionManager()->MboSetRemote("mr")->where("mrnum=?", $mrnum)->MboRemote("requestedby")->MboRemote("requireddate")->getMboSet();
		
		$mrHeader = $mrHeaderSet->getMbo();
		
		$reservedfor = $mrHeader->getString("requestedby");
		$targstartdate = $mrHeader->getString("requireddate");

		// Loop Through All Lines in the Material Requisition
		
		$mrlineSet = (new Set())->useTransactionManager()->MboSetRemote("mrline")->setRelationship("mr", "mr.mrnum = mrline.mrnum")
		->setWhere("mrline.mrnum='$mrnum'")->MboRemote("mrline.mrnum")
		->MboRemote("mr.wonum")->MboRemote("mrline.itemnum")
		->MboRemote("mrline.quantity")->MboRemote("mrline.conditiontype")
		->MboRemote("mrline.warehouse")->MboRemote("mrline.racknum")->getMboSet();

		
			
		if ($mrlineSet->counts() > 0) {
			for ($i = 0; $i < $mrlineSet->counts(); $i++) {
				$mrline = $mrlineSet->getMbo($i);
				$mrnum = $mrline->getString("mrnum");
				$wonum = $mrline->getString("wonum"); 
				$itemnum = $mrline->getString("itemnum");
				$quantity_needed = floatval($mrline->getString("quantity"));
				$warehouse = $mrline->getString("warehouse");
				$conditiontype = $mrline->getString("conditiontype");
				$racknum = $mrline->getString("racknum");

				$enterby = Xbo::getLoginUser();
				$enterdate = date("Y-m-d H:i:s");
				$changeby = $enterby;
				// START: BUSINESS LOGIC PENENTUAN STATUS 
				$rackval = Xbo::RackWhereClause($racknum);
				$condval = Xbo::CondTypeWhereClause($conditiontype);
				$whereclauseItem = "itemnum='$itemnum' AND warehouse='$warehouse' $condval";

				// B. Hitung total stok fisik yang ada di gudang
				$setInv = new Set();
				$inventorySet = $setInv->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclauseItem)->MboRemote("SUM(quantity) as total_qty")->getMboSet();
				$inventory = $inventorySet->getMbo();				
				$total_qty_on_hand = floatval($inventory->getString("total_qty"));

				// C. Hitung total stok yang sudah direservasi oleh orang lain
				$setRes = new Set();
				$invreserveSet = $setRes->useTransactionManager()->MboSetRemote("invreserve")->setWhere($whereclauseItem . " AND status='RESERVED'")->MboRemote("SUM(quantity) as total_qty_reserved")->getMboSet();
				$invreserve = $invreserveSet->getMbo();
				$total_qty_reserved = floatval($invreserve->getString("total_qty_reserved"));
				
				// D. Hitung stok yang benar-benar tersedia (Available Stock)
				$available_stock = $total_qty_on_hand - $total_qty_reserved;
				
				// E. Tentukan status berdasarkan ketersediaan stok
				if ($available_stock >= $quantity_needed) {
					$status = "RESERVED";
				} else {
					$status = "WAITING_LIST";
				}
				// END: BUSINESS LOGIC PENENTUAN STATUS
				
				// START: BUSINESS LOGIC PEMBUATAN INVRESERVENUM (BEST PRACTICE)
				
				// Format diadaptasi untuk konteks MR
				$unique_part = substr(uniqid(), -6);
				$invreservenum = "RES-MR-" . $mrnum . "-" . $unique_part;
				// END: BUSINESS LOGIC PEMBUATAN INVRESERVENUM
				// F. Simpan data reservasi ke database dengan logika yang sudah lengkap
				$set2 = new Set();
				$set2->useTransactionManager()->MboSetRemote("invreserve")
					->setValue("mrnum", $mrnum)
					->setValue("wonum", $wonum)
					->setValue("invreservenum", $invreservenum) 
					->setValue("status", $status)
					->setValue("itemnum", $itemnum)
					->setValue("quantity", $quantity_needed)
					->setValue("warehouse", $warehouse)
					->setValue("conditiontype", $conditiontype)
					->setValue("racknum",$racknum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->setValue("changeby", $changeby)
					->setValue("reservedfor", $reservedfor) 
					->setValue("targstartdate", $targstartdate)
					->add();
			}
		}
			
		Super::sendJsonResponse("success", "MR has been sent to the Inventory Department");
		
	}
	public static function validateStatusChange(){
		$payload = Super::getParam("validateStatusChange");
		$new_status = strtolower($payload["new_status"]);
		$mrnum = $payload["mrnum"];

		$mrSet = (new Set())->useTransactionManager()->MboSetRemote("mr")->where("mrnum=?", $mrnum)->getMboSet();
		if ($mrSet->isEmpty()) {
			throw new Exception("MR is not found.");
		}

		$mr = $mrSet->getMbo();
		$current_status= $mr->getString("status");
		$current_status = strtolower($current_status);

		if($new_status == $current_status){
			throw new Exception("You cannot change with the same status: {$current_status}.");
		}

		if($new_status == "cancel" || $new_status == "canceled"){
			Super::sendJsonResponse("success", "Validation passed. Ready to change status.");
		}

		if (in_array($current_status, ["cancel", "sent"])) {
			throw new Exception("You cannot change status once the MR has been {$current_status}.");
		}

		//ambil semua value validasi 
		$missingFields = [];
		$wonum = $mr->getString("wonum");
		$requesteddate =  $mr->getString("requesteddate");
		$requestedby =  $mr->getString("requestedby");
		$description =  $mr->getString("description");
		$requireddate = $mr->getString("requireddate");

		if(empty($requestedby)){
			$missingFields[] = "Requested By cannot be null.";
		}
		if(empty($requesteddate)){
			$missingFields[] = "Requested Date cannot be null.";
		}
		if(empty($description)){
			$missingFields[] = "Description cannot be null.";
		}
		if(empty($requireddate)){
			$missingFields[] = "Required Date cannot be null.";
		}
		//Check apakah MR ini ada lines nya. 
		if (!empty($missingFields)) {
			$message = "Please fill in the following mandatory fields: <br>" . implode("<br>", $missingFields);
			Super::sendJsonResponse("error", $message, array_unique($missingFields));
			return;
		}

		
		$mrlineSet= (new Set())->useTransactionManager()->MboSetRemote("mrline")->where("mrnum=?", $mrnum)->getMboSet();
		if($mrlineSet->isEmpty()){
			Super::sendJsonResponse("error", "This MR: $mrnum does not have additional requisition items. No need to change status.");
		}

		Super::sendJsonResponse("success", "Validation passed. Ready to change status.");

	}
	public static function ValidateMaterialPlanQty()
	{
		$pts = Super::getParam("validatematplanqty");
		$itemnum = $pts["itemnum"];
		$warehouse = $pts["warehouse"];
		$racknum = $pts["racknum"];
		$conditiontype = $pts["conditiontype"];
		$quantity = floatval($pts["quantity"]);
		$conditiontype = $pts["conditiontype"];
		$racknum = $pts["racknum"];

		if(empty($itemnum)) Super::sendJsonResponse("error", "Itemnum is not selected.");
		if(empty($warehouse)) Super::sendJsonResponse("error", "Warehouse is not selected.");
		if(empty($quantity)) Super::sendJsonResponse("error", "Quantity is not defined.");
		if($quantity <=0 ) Super::sendJsonRespone("error", "Quantity cannot be less than zero.");

	
		$invbalSet = (new Set())->useTransactionManager()
		->MboSetRemote("v_invbalance")->where("itemnum=? and warehouse=?", $itemnum, $warehouse);


		if (empty($conditiontype)) {
			$invbalSet->whereNull("conditiontype");
		}
		if (empty($racknum)) {
			$invbalSet->whereNull("racknum");
		}

		$invbalSet->getMboSet();
		$invbal = $invbalSet->getMbo();
		$unitcost = $invbal->getString("avgcost");
		$linecost = $unitcost*$quantity;

		Super::sendJsonResponse("success", "updated", ["unitcost"=>$unitcost, "linecost"=>$linecost, "quantity"=>$quantity]);
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
		
		if($childTable == "mrline"){
			//Check Invreserve
			foreach($selectedRows as $row){
				$mrlineid = $row["mrlineid"];
				$mrnum = (new Set())->useTransactionManager()->MboSetRemote("mrline")->where("mrlineid=?", $mrlineid)->getMboValue("mrnum");
				$invreserveSet = (new Set())->useTransactionManager()->MboSetRemote("invreserve")->where("mrnum=?", $mrnum)->getMboSet();
				if($invreserveSet->isEmpty()){
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();		
				}else{
					$inv = $invreserveSet->getMbo();
					$invreservenum = $inv->getString("invreservenum");
					Super::sendJsonResponse("error", "Cannot Delete Material Requisition Line because it's linked to the reservation : $invreservenum");
				}
			}
		}
	}
}
	

?>