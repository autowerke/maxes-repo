<?php 
require_once ("../../eat.php");


class addVendorCode extends poChildren{
	public static function updateVendorCode(){
		global $ownerCols;
		global $childTable;
		if($childTable=="popayment"){
			$_GET['package'] = $ownerCols;
			$ponum = Xbo::getString("ponum");
			
			$set = new Set();
			$vendorcode = $set->MboSetRemote("po")->setWhere("ponum= '$ponum'")->MboRemote("vendor")->getMboSet()->getMbo()->getString("vendor");
			$set->MboSetRemote($childTable)->setWhere("ponum='$ponum' and vendor is null")->setValue("vendor", "$vendorcode")->change();
		
		}
	}
}

class poChildren {

	public static function init(){
		XboSet::validateChildren();	
		poChildren::displayData();
		addVendorCode::updateVendorCode();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$x = explode("=", $getOwner);
		$legalOwner = "legaldoc.ownertable='po' AND legaldoc.ownernum=".$x[1];

		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_polines_child"){
			Xbo::select("Line Num.", "polinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Work Order", "wonum");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Receipt Status", "receiptstatus");
			Xbo::select("Quantity", "orderqty", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Tax", "tax");
			Xbo::select("Tax Cost", "taxcost", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("poline", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_poterm_child"){
			
			Xbo::select("No.", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Remarks", "description");			
			XboSet::getXboSet("poterm", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_poterm2_child"){
			Xbo::select("Line Num.", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("poterm2", $getOwner);
			XboSet::displayData();
		}
		
		
		if($tableid=="mxtable_popayment_child"){
			XboSet::getXboSet("popayment");
			$getOwner = XboSet::getOwner();
			Xbo::select("Line Num.", "paymentlinenum");
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("Payment Date", "paymentdate","date");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Paid Cost", "paidcost", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			XboSet::getXboSet("popayment", $getOwner);
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

poChildren::init();

?>