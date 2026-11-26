<?php 
include ("../eat.php");

class ChartofAccount extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Account Code", "glaccount");
		Xbo::select("Description", "description");
		XboSet::getXboSet("chartofaccount");
		XboSet::displayData();
	}
	public static function init(){
		ChartofAccount::displayData();
	}
	
}
ChartofAccount::init();
?>