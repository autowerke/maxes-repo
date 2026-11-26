<?php 
include ("../eat.php");

class ChartofAccountGL extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$glaccount = Super::getParam("maingl");
		Xbo::select("Account Code", "glaccount");
		Xbo::select("Description", "description");
		
		XboSet::getXboSet("chartofaccount", "chartofaccount.glaccount != '$glaccount'");
		XboSet::displayData();
	}
	public static function init(){
		ChartofAccountGL::displayData();
	}
	
}
ChartofAccountGL::init();
?>