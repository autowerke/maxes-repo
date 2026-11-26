<?php 
require_once ("../../eat.php");

class EmployeeChildren {

	public static function init(){
		
		XboSet::validateChildren();	
		EmployeeChildren::displayCols();
	}
	public static function displayCols(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		$x = explode("=", $getOwner);
		$legalOwner = "legaldoc.ownertable='employees' AND legaldoc.ownernum=".$x[1];
		if($tableid=="mxtable_employeesqualification_child"){
		
			Xbo::select("Qualification", "qualification");
			Xbo::select("Description", "qualification.description");
			Xbo::select("Skill", "qualification.skill");
			Xbo::select("Certificate", "qualification.certificate");
			Xbo::select("Remarks", "remarks");
			XboSet::getXboSet("empqual", $getOwner);
			XboSet::displayData(["empqual.qualification = qualification.qualification"]);
			
		}
		if($tableid=="mxtable_empallowance_child"){
			Xbo::select("Allowance", "allowancecode");
			Xbo::select("Description", "allowance.description");
			Xbo::select("Cost in Price", "allowance.costprice", "decimal");
			Xbo::select("Cost in Percent(%)", "allowance.costpercent", "decimal");
			Xbo::select("Remarks", "Remarks");
			Xbo::select("Biaya", "cost", "decimal");
			XboSet::getXboSet("empallowance", $getOwner);
			XboSet::displayData(["empallowance.allowancecode = allowance.allowancecode"]);
		}
		if($tableid=="mxtable_empallowance2_child"){
			$varallowance = Xbo::varallowance();
			$getOwner = $getOwner." ".$varallowance;
			Xbo::select("Allowance", "myallowancenum");
			Xbo::select("Description", "description");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Status", "status");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Cost", "totalcost", "decimal");
			XboSet::getXboSet("myallowance", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_pr_child"){			
			$getOwner = str_replace("personid", "requestedby", $getOwner);
			Xbo::select("PR", "prnum");
			Xbo::select("Description", "description");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Requested Date", "requesteddate", "date");		
			Xbo::select("Line Cost Basis", "totalbasecost", "decimal");
			Xbo::select("Status", "status");
			XboSet::getXboSet("pr", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_po_child"){			
			$getOwner = str_replace("personid", "buyer", $getOwner);
			Xbo::select("PO", "ponum");
			Xbo::select("Description", "description");
			Xbo::select("Vendor", "vendor");
			Xbo::select("Ordered Date", "orderdate", "date");		
			Xbo::select("Line Cost Basis", "totalbasecost", "decimal");
			Xbo::select("Status", "status");
			Xbo::select("Diterima", "receipt");
			XboSet::getXboSet("po", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_wo_child"){			
			$getOwner = str_replace("personid", "owner", $getOwner);
			Xbo::select("WO", "wonum");
			Xbo::select("Description", "description");
			Xbo::select("Asset", "assetnum");
			Xbo::select("Location", "location");
			Xbo::select("customer", "customercode");
			Xbo::select("Actual Cost", "actualcost", "decimal");
			Xbo::select("Status", "status");
			XboSet::getXboSet("workorder", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_so_child"){			
			$getOwner = str_replace("personid", "employeesid", $getOwner);
			Xbo::select("SO", "sonum");
			Xbo::select("Description", "description");
			Xbo::select("Cust.Code", "customercode");
			Xbo::select("Cust.Name", "customer.name");
			Xbo::select("Total  Price", "totalprice", "decimal");
			Xbo::select("Paid", "paidprice", "decimal");
			Xbo::select("Unpaid", "unpaidprice", "decimal");
			XboSet::getXboSet("so", $getOwner);
			XboSet::displayData(["so.customercode = customer.customercode"]);
		}
		if($tableid=="mxtable_customer_child"){			
			$getOwner = str_replace("personid", "salesrep", $getOwner);
			Xbo::select("customer", "customercode");
			Xbo::select("Name", "name");
			Xbo::select("Address", "billaddress");
			Xbo::select("City", "billcity");
			Xbo::select("Province", "billstateprovince");
			XboSet::getXboSet("customer", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_legaldoc_child"){
			Xbo::select("Legal Doc. Num.", "legaldocnum");
			Xbo::select("Legal Doc. Type", "legaldoctype");
			Xbo::select("Description", "description");
			Xbo::select("Issued Date", "issuedate");
			Xbo::select("Expired Date", "expirydate");
			Xbo::select("Status", "status");
			Xbo::select("Document Num.", "documentnum");
			XboSet::getXboSet("legaldoc", $legalOwner);
			XboSet::displayData();
		}

		
	}
}

EmployeeChildren::init();

?>