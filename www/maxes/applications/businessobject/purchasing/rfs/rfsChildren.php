<?php 
require_once ("../../eat.php");

class RFSChildren {

	public static function init(){
		XboSet::validateChildren();	
		RFSChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid == "mxtable_matrectrans_child"){
			
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack/Bin", "racknum");
			Xbo::select("Transaction Type", "transtype");
			XboSet::getXboSet("matrectrans", $getOwner);
			XboSet::displayData();
			return;
			
		}
		if($tableid == "mxtable_rfsline_child"){
			Xbo::select("Line", "rfslinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack/Bin", "racknum");
			Xbo::select("Storage Duration", "storagedur", "decimal");
			Xbo::select("Order Qty", "orderqty", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");	
			Xbo::select("Line Cost", "linecost", "decimal");	
			XboSet::getXboSet("rfsline", $getOwner);
			XboSet::displayData(["rfsline.itemnum = item.itemnum"]);
			return;
		}
		if($tableid == "mxtable_rfscourier_child"){
			Xbo::select("Line", "rfscourierlinenum");
			Xbo::select("Courier Name", "couriername");
			Xbo::select("Tracking Code", "trackingcode");
			Xbo::select("Courier Name", "agentname");
			Xbo::select("Shipping Type", "shippingtype");
			Xbo::select("Service Type", "servicetype");
			Xbo::select("Shipping Date", "shipdate", "date");
			Xbo::select("Est. Date Arrival", "estdatearrival", "date");
			Xbo::select("Act. Date Arrival", "actdatearrival", "date");
			XboSet::getXboSet("rfscourier", $getOwner);
			XboSet::displayData();
			return;
		}
		
		if($tableid == "mxtable_rfsinsteam_child"){
			Xbo::select("Line", "rfsinslinenum");
			Xbo::select("User", "personid");
			Xbo::select("Name", "employees.fullname");
			Xbo::select("Title", "employees.title");
			Xbo::select("Department", "employees.department");
			Xbo::select("Acknowledged", "acknowledge");
			Xbo::select("Approval", "approve");
			XboSet::getXboSet("rfsinsteam", $getOwner);
			XboSet::displayData(["employees.personid = rfsinsteam.personid"]);
			return;
		}
		if($tableid == "mxtable_rfsreturn_child"){
			Xbo::select("Line", "rfsreturnlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("description", "description");
			Xbo::select("Return Qty", "returnqty");
			Xbo::select("Return Unit", "returnunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack", "racknum");
			Xbo::select("Return By", "returnby");
			Xbo::select("Name", "employees.fullname");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Return Date", "returndate", "date");
			XboSet::getXboSet("rfsreturn", $getOwner);
			XboSet::displayData(["rfsreturn.returnby = employees.personid"]);
			return;
		}
		
		
		$linktable = Super::getParam("linktable");
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
			if($tableName=="mxtable_rfsinsitem_child") {
				RFSChildren :: displayLinkTable1($where);
				return;
			}
			if($tableName=="mxtable_rfsinsdoc_child") {
				RFSChildren :: displayLinkTable2($where);
				return;
			}
		}else{
			if($tableid == "mxtable_rfsinsitem_child") {
				RFSChildren :: displayLinkTable1($getOwner);
				return;
			}
			if($tableid == "mxtable_rfsinsdoc_child") {
				RFSChildren :: displayLinkTable2($getOwner);
				return;
			}
		
		}
	}
	
	public static function displayLinkTable1($param){
			Xbo::select("Line", "rfsinslinenum");
			Xbo::select("Courier Line", "rfscourierlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Inspect Qty", "inspectqty", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Inspection Date", "inspectdate", "date");
			Xbo::select("Inspection Type", "inspectiontype");
			Xbo::select("Inspected By", "inspectby");
			Xbo::select("Approved?", "isapproved");
			Xbo::select("Posted?", "isprocessed");
			XboSet::getXboSet("rfsinsitem", $param);
			XboSet::displayData();
	}
	public static function displayLinkTable2($param){
			Xbo::select("Line", "rfsinslinenum");
			Xbo::select("Courier Line", "rfscourierlinenum");
			Xbo::select("Doc. Number", "docnum");
			Xbo::select("Doc. Title", "doctitle");
			Xbo::select("Created Date", "createddate", "date");
			Xbo::select("Issued Date", "issueddate", "date");
			XboSet::getXboSet("rfsinsdoc", $param);
			XboSet::displayData();
	}
}

RFSChildren::init();

?>