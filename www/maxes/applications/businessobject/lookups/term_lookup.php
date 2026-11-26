<?php 
include ("../eat.php");

class TermLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where  = "orgcode ='".$orgcode."'";	
		else $where = null;	
		Xbo::select("Term ID", "termcode");
		Xbo::select("Description", "description");
		XboSet::getXboSet("term", $where);
		XboSet::displayData();
	}
	public static function init(){
		TermLookup::displayData();
	}
	
}
TermLookup::init();
?>