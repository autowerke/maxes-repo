<?php 
require_once ("../../eat.php");

class addCustomerCode extends SOChildren{
	
}
class soChildren {

	public static function init(){
		XboSet::validateChildren();	
		soChildren::displayData();
		//addCustomerCode::updateCustomerCode();
		
		if (isset($_POST['childPackage'])) {
			/*
			$childPackage = urldecode($_POST['childPackage']);
			$packageParts = explode("|", $childPackage);
			$ownerTable = $packageParts[0];
			$ownerCols = $packageParts[1];
			$childTable = $packageParts[2];
			$childCols = explode("^", $packageParts[3]);
			*/
		}
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
	
		
		if($tableid=="mxtable_salesquotationline_child"){
			Xbo::select("Line Num.", "salesquotationlinenum");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "sellqty", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Line  Price", "lineprice", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Margin(%)", "margin", "decimal");
			XboSet::getXboSet("salesquotationline", $getOwner);
			XboSet::displayData();
		}


		
	
		if($tableid=="mxtable_salesquotationterm_child"){
			Xbo::select("Line Num.", "termlinenum");
			Xbo::select("Term", "termcode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("salesquotationterm", $getOwner);
			XboSet::displayData();
		}
		

		// COST ESTIMATION 
		if($tableid=="mxtable_sqestmaterial_child"){
			Xbo::select("Line Num.", "salesquotationlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack", "racknum");
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Quantity", "quantity");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("sqestmaterial", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_sqestservice_child"){
			Xbo::select("Line Num.", "salesquotationlinenum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Vendor", "vendorcode");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("sqestservice", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_sqesttool_child"){
			Xbo::select("Line Num.", "salesquotationlinenum");
			Xbo::select("Tool", "toolnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("sqesttool", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_sqestlabor_child"){
			Xbo::select("Line Num.", "salesquotationlinenum");
			Xbo::select("Person ID", "personid");
			Xbo::select("Team", "teamnum");
			Xbo::select("Start Date", "startdate", "date");
			Xbo::select("End Date", "enddate", "date");
			Xbo::select("Regular Hours", "reghours", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Total  Cost", "linecost", "decimal");
			XboSet::getXboSet("sqestlabor", $getOwner);
			XboSet::displayData();
		}

	}
}

soChildren::init();

?>