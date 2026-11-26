<?php 
include ("../eat.php");

class Failures extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		Xbo::select("Failure Code", "failurecode");
		Xbo::select("Description", "description");
		Xbo::select("Category", "category");
		Xbo::select("Is Active?", "isactive");
		XboSet::getXboSet("assetfailure", $where);
		XboSet::displayData();
	}
	
	public static function init(){
		Failures::displayData();
	}
	
}

Failures::init();




?>