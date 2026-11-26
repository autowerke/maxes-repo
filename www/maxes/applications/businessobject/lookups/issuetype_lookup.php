<?php 
include ("../eat.php");

class IssueTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Issue Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='issuetype'");
		XboSet::displayData();
	}
	public static function init(){
		IssueTypeLookup::displayData();
	}
	
}
IssueTypeLookup::init();
?>