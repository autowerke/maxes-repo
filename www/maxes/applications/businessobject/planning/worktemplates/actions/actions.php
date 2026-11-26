<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");

Class ActionSet {
	public static function getLaborCost(){
		$payload = Super::getParam("getLaborCost");
		$teamnum = $payload["teamnum"];
		$sumUnitCost = (new Set())->useTransactionManager()->MboSetRemote("empteam")->where("teamnum=?", $teamnum)->MboRemote("sum(unitcost) as sumUnitCost")->getMboValue("sumUnitCost");

		Super::sendJsonResponse("success", "TEAMNUM: $teamnum", ["sumUnitCost"=>$sumUnitCost]);
	}
	
	public static function getTeamLeader(){
		$teamnum = Super::getParam("getTeamLeader");
		
		$set = new Set();
		$empTeamSet = $set->MboSetRemote("empteam")->setWhere("teamnum='$teamnum' and leader = 'Y'")->MboRemote("personid")->getMboSet();
		$empTeam = $empTeamSet->getMbo();
		$personid = $empTeam->getString("personid");
		
		$set = new Set();
		$employeesSet = $set->MboSetRemote("employees")->setWhere("personid = '$personid'")->MboRemote("fullname")->getMboSet();
		$emp = $employeesSet->getMbo();
		$fullname = $emp->getString("fullname");
		
		$response = new stdClass();
		$response->status = "success";
		$response->fullname = $fullname;
		$response->personid = $personid;
		
		echo json_encode($response);
	}
	
	
	public static function ValidateIssueItem()
	{
		$x = explode("|", $_GET['validateIssueItem']);
		$xid = $x[0];
		$wonum = $x[1];
		$set = new Set();
		$invbalanceSet = $set->MboSetRemote("v_invbalance vb")->setRelationship("item", "item.itemnum=vb.itemnum ")->setWhere("vb.v_invbalanceid='$xid'")->MboRemote("vb.itemnum")->MboRemote("vb.warehouse")->MboRemote("vb.racknum")->MboRemote("vb.conditiontype")->MboRemote("vb.curbal")->MboRemote("vb.orgcode")->MboRemote("vb.sitecode")->MboRemote("item.stocktype")->getMboSet("remove_duplicate")->getMbo();
		
		
		$itemnum = $invbalanceSet->getString("itemnum");
		$warehouse = $invbalanceSet->getString("warehouse");
		$racknum = $invbalanceSet->getString("racknum");
		$conditiontype = $invbalanceSet->getString("conditiontype");
		$sitecode = $invbalanceSet->getString("sitecode");
		$orgcode = $invbalanceSet->getString("orgcode");

		$whereclause = "itemnum='$itemnum' AND warehouse='$warehouse'";

		// Kondisi untuk conditiontype
		if (!empty($conditiontype)) {
			$whereclause .= " AND conditiontype='$conditiontype'";
		} else {
			$whereclause .= " AND (conditiontype='' OR conditiontype IS NULL)";
		}

		// Kondisi untuk racknum
		if (!empty($racknum)) {
			$whereclause .= " AND racknum='$racknum'";
		}

		// Kondisi untuk orgcode
		if (!empty($orgcode)) {
			$whereclause .= " AND orgcode='$orgcode'";
		}

		// Kondisi untuk sitecode
		if (!empty($sitecode)) {
			$whereclause .= " AND sitecode='$sitecode'";
		}
		
		$set = new Set();
		$unitcost = $set->MboSetRemote("invfifolifocost")->setWhere($whereclause)->MboRemote("avg(unitcost) avgcost")->getMboSet()->getMbo()->getString("avgcost");
		if ($unitcost == null) $unitcost = 0;
		$glcreditacct = Xbo::getGLAccount($itemnum);
		$gldebitacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode);
		
			$enterby = Xbo::getLoginUser();
			$enterdate = date("Y-m-d h:i:s");
			echo json_encode(["enterby" => $enterby, "enterdate" => $enterdate, "warehouse" => $warehouse, "racknum" => $racknum, "conditiontype" => $conditiontype, "curbal" => $curbal, "unitcost" => $unitcost, "gldebitacct" => $gldebitacct, "glcreditacct" => $glcreditacct]);
		
	}
	public static function ValidateMaterialPlanQty()
	{
		$pts = explode("|", $_GET["validatematplanqty"]);
		$itemnum = $pts[0];
		$warehouse = $pts[1];
		$racknum = $pts[2];
		$conditiontype = $pts[3];
		$quantity = $pts[4];

		$whereClause = "itemnum='$itemnum' AND warehouse='$warehouse'";

		// Kondisi untuk conditiontype
		if (!empty($conditiontype)) {
			$whereClause .= " AND conditiontype='$conditiontype'";
		} else {
			$whereClause .= " AND (conditiontype IS NULL OR conditiontype='')";
		}

		// Kondisi untuk racknum
		if (!empty($racknum)) {
			$whereClause .= " AND racknum='$racknum'";
		} else {
			$whereClause .= " AND (racknum IS NULL OR racknum='')";
		}

		$set = new Set();
		$curbal = $set->MboSetRemote("v_invbalance vb")
					 ->setWhere($whereClause)
					 ->MboRemote("curbal")
					 ->getMboSet()
					 ->getMbo()
					 ->getString("curbal");

		if ($quantity > 0) {
			if ($quantity > $curbal) {
				Set::throws(["msg" => "greaterplannedqty", "plannedqty" => $curbal]);
			} else {
				Set::throws(["msg" => "ok"]);
			}
		} else {
			Set::throws(["msg" => "lesserthanzero", "plannedqty" => $curbal]);
		}
	} 

}
Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		
	}
}
	

?>