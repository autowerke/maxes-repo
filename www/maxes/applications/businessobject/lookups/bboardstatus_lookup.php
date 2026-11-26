<?php 
include ("../eat.php");

class BBoardStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='bboard'");
		XboSet::displayData();
	}
	public static function init(){
		BBoardStatus::displayData();
	}
	
}
BBoardStatus::init();
?>