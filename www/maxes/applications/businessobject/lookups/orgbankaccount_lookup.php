<?php 
include ("../eat.php");

class addressLookup extends Xbo {

	public function __destruct()
    {
       return null;
    }
	public static function displayData(){
		
		Xbo::select("Bank Account", "bankaccount");
		Xbo::select("Bank Name", "bankname");
		Xbo::select("Account Holder", "bankholder");
		Xbo::select("Default", "isdefault");
		
		XboSet::getXboSet("orgbankaccount");
		XboSet::displayData();
	}
	public static function init(){
		addressLookup::displayData();
	}
	
}
addressLookup::init();
?>