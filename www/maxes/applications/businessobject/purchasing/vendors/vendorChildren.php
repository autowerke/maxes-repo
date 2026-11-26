<?php 
require_once ("../../eat.php");

class VendorChildren {

	public static function init(){
		XboSet::validateChildren();	
		VendorChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		$x = explode("=", $getOwner);
		$polineowner = "po.vendor=".$x[1];
		$relatedvendorowner = "relatedvendor.vendorcode=".$x[1];
		if($tableid=="mxtable_vendorcontact_child"){
			Xbo::select("Contact", "contact");
			Xbo::select("Posisi", "position");
			Xbo::select("Email", "email");
			Xbo::select("Office Phone", "officephone");
			Xbo::select("HP", "mobilephone");
			Xbo::select("BB PIN", "blackberrypin");
			Xbo::select("WA", "wanum");
			Xbo::select("Line ID", "lineid");
			XboSet::getXboSet("vendorcontact", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_vendorbranches_child"){
			Xbo::select("Cabang Vandor", "relatedvendorcode");
			Xbo::select("Vendor Name", "vendor.name");
			Xbo::select("Address", "vendor.address");
			Xbo::select("City", "vendor.city");
			Xbo::select("Office Phone", "vendor.phone");
			XboSet::getXboSet("relatedvendor", $relatedvendorowner);
			XboSet::displayData(["relatedvendor.vendorcode = vendor.vendorcode"]);
		}
		if($tableid=="mxtable_vendorCategory_child"){
			Xbo::select("Category", "categorycode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("vendorcategory", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_vendormatrectrans_child"){
			Xbo::select("PO", "po.ponum");
			Xbo::select("Line Num.", "polinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Received Qty", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Catatan Penerimaan Barang", "receiptnotes");
			Xbo::select("Transaction Date", "enterdate", "date");
			Xbo::select("Transaction Type", "transtype");
			XboSet::getXboSet("matrectrans", $getOwner." and matrectrans.transtype in ('RECEIPT', 'RETURN')" );
			XboSet::displayData("po.ponum = matrectrans.ponum");
		}
		if($tableid=="mxtable_vendorservrectrans_child"){
			Xbo::select("PO", "po.ponum");
			Xbo::select("Line Num.", "polinenum");
			Xbo::select("Description", "description");
			Xbo::select("Received Qty", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Catatan Penerimaan Barang", "receiptnotes");
			Xbo::select("Transaction Date", "enterdate", "date");
			Xbo::select("Transaction Type", "transtype");
			XboSet::getXboSet("servrectrans", $getOwner." and servrectrans.transtype in ('RECEIPT', 'RETURN')" );
			XboSet::displayData("po.ponum = servrectrans.ponum");
		}
	}
}

VendorChildren::init();

?>