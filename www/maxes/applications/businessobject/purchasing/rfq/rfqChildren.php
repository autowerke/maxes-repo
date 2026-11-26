<?php 
require_once ("../../eat.php");

class RFQChildren {

	public static function init(){
		XboSet::validateChildren();	
		RFQChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_rfqlines_child"){
			Xbo::select("Line", "rfqlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Tax", "taxcode");
			Xbo::select("Tax Cost", "taxcost", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Work Order", "wonum");
			Xbo::select("Warehouse", "warehouse");
			XboSet::getXboSet("rfqline", $getOwner);
			XboSet::displayData();
			return;
		}
		if($tableid=="mxtable_rfqterm_child"){
			
			Xbo::select("Line", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Remarks", "description");			
			XboSet::getXboSet("rfqterm", $getOwner);
			XboSet::displayData();
			return;
		}
		if($tableid=="mxtable_rfqvendor_child"){
			
			Xbo::select("Vendor", "vendor");
			Xbo::select("Name", "vendor.name");
			Xbo::select("Keyword", "vendor.keyword");
			Xbo::select("Address", "vendor.address");
			Xbo::select("Remarks", "remarks");		
			XboSet::getXboSet("rfqvendor", $getOwner);
			XboSet::displayData(["rfqvendor.vendor = vendor.vendorcode"]);
			return;
		}
		if($tableid=="mxtable_rfqvendorlist_child"){
			Xbo::select("Vendor", "vendor");
			Xbo::select("Name", "vendor.name");
			Xbo::select("Keyword", "vendor.keyword");
			Xbo::select("Address", "vendor.address");
			Xbo::select("Procurement/User remarks", "remarks2");		
			Xbo::select("Vendor Remarks", "remarks");		
			XboSet::getXboSet("rfqvendor", $getOwner);
			XboSet::displayData(["rfqvendor.vendor = vendor.vendorcode"]);
			return;
		}
		if($tableid=="mxtable_rfqterm2_child"){
			Xbo::select("Line", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("rfqterm2", $getOwner);
			XboSet::displayData();
			return;
		}
		
		$linktable = Super::getParam("linktable");
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
			if($tableName=="mxtable_quotation_child") {
				RFQChildren :: displayLinkTable1($where);
				return;
			}
		}else{
			if($tableid=="mxtable_quotation_child"){
				RFQChildren :: displayLinkTable1($getOwner);
				return;
			}
		}
	}
	
	public static function displayLinkTable1($param){
			Xbo::select("Vendor", "vendor");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Tax", "taxcost", "decimal");
			Xbo::select("Procurement/Remarks", "remarks");
			Xbo::select("Vendor Remarks", "remarks2");
			Xbo::select("Awarded ?", "awarded");
			XboSet::getXboSet("rfqquotation", $param);
			XboSet::displayData();
			return;
	}
	
	
}

RFQChildren::init();

?>