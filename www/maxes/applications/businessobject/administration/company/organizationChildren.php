<?php 
require_once ("../../eat.php");

class OrganizationChildren {

	public static function init(){
		XboSet::validateChildren();	
		OrganizationChildren::displayData();
	}
	public static function displayLinkTable1($param){
		Xbo::select("Address Code", "siteaddresscode");
		Xbo::select("Site Code", "sitecode");
		Xbo::select("Description", "site.description");		
		Xbo::select("Address", "address.address");
		Xbo::select("Office Phone", "address.officephone");
		Xbo::select("Fax", "address.fax");
		Xbo::select("City", "address.city");
		Xbo::select("Remarks", "address.description");
		XboSet::getXboSet("siteaddress", $param);
		XboSet::displayData(["site.sitecode = siteaddress.sitecode","address.addresscode=siteaddress.siteaddresscode"]);
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_address_child"){
			Xbo::select("Address Code", "addresscode");
			Xbo::select("Address", "address");
			Xbo::select("Office Phone", "officephone");
			Xbo::select("Fax", "fax");
			Xbo::select("City", "city");
			Xbo::select("Province", "stateprovince");
			Xbo::select("Postal Code", "zippostalcode");
			Xbo::select("Remarks", "description");
			XboSet::getXboSet("address", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_bankaccount_child"){
			Xbo::select("Bank Account", "bankaccount");
			Xbo::select("Bank", "bankname");
			Xbo::select("Account Holder", "bankholder");
			XboSet::getXboSet("orgbankaccount", $getOwner);
			XboSet::displayData();
		}
		
		
		if($tableid=="mxtable_site_child"){
			Xbo::select("Kode Cabang", "sitecode");
			Xbo::select("Cash on Hand Account", "cashonhandacct");
			Xbo::select("Cash on Bank Account", "cashonbankacct");
			Xbo::select("Description", "description");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Changed By", "changeby");
			Xbo::select("Changed Date", "changedate", "date");
			XboSet::getXboSet("site", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_siteAddress_child"){
			OrganizationChildren :: displayLinkTable1($getOwner);
		}
		
		$linktable = Super::getParam("linktable");
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
			
			if($tableName=="mxtable_siteAddress_child") {		
				OrganizationChildren :: displayLinkTable1($where);
			}
			
		}
	}
}

OrganizationChildren::init();

?>