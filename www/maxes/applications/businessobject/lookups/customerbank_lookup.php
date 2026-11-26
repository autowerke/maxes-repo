<?php 
include ("../eat.php");

class AllowancesLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$customer_code = Super::getParam("customercode");
		if(empty($customer_code)) $customer_code = "";
		else $customer_code = "customercode='$customer_code'";
		
		Xbo::select("Nomor Rekening", "bankaccount");
		Xbo::select("Bank Name", "bankname");
		Xbo::select("Atas Nama", "banktoaccountname");
		Xbo::select("Remarks", "remarks");
		Xbo::select("Default Transaksi?", "isdefault");
		
		XboSet::getXboSet("customerbank", $customer_code);
		XboSet::displayData();
	}
	public static function init(){
		AllowancesLookup::displayData();
	}
	
}
AllowancesLookup::init();
?>