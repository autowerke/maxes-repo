<?php 
include ("../eat.php");

class AllowancesLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Kode Tunjangan", "allowancecode");
		Xbo::select("Deksripsi", "description");
		Xbo::select("Biaya", "costprice", "decimal");
		Xbo::select("Biaya (%)", "costpercent", "decimal");
		Xbo::select("Nilai Max", "maximumvalue", "decimal");
		Xbo::select("dari Gaji Pokok", "frombasesalary");
		Xbo::select("dari Pend. Kotor", "fromgrossincome");
		Xbo::select("Pengurangan", "deduction");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
		XboSet::getXboSet("allowance");
		XboSet::displayData();
	}
	public static function init(){
		AllowancesLookup::displayData();
	}
	
}
AllowancesLookup::init();
?>