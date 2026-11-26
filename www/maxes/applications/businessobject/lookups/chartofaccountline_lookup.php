<?php 
include ("../eat.php");

class ChartofAccountLine extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$glaccount = Super::getParam("gldebitacct");
		Xbo::select("Account Code Kredit", "glaccount2");
		Xbo::select("Description", "chartofaccount.description");
		
		XboSet::getXboSet("chartofaccountline", "chartofaccountline.glaccount = '$glaccount'");
		XboSet::displayData(["chartofaccount.glaccount = chartofaccountline.glaccount2"]);
	}
	public static function init(){
		ChartofAccountLine::displayData();
	}
	
}
ChartofAccountLine::init();
?>