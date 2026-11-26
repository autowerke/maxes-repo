<?php 
include ("../eat.php");

class DocFormStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='docform'");
		XboSet::displayData();
	}
	public static function init(){
		DocFormStatus::displayData();
	}
	
}
DocFormStatus::init();
?>