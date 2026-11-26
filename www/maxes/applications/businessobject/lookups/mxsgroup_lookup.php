<?php 
include ("../eat.php");

class MxsGroupLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Grup", "groupnum");
		Xbo::select("Description", "mxsgroups.description");
		
		XboSet::getXboSet("mxsgroups");
		XboSet::displayData();
	}
	public static function init(){
		MxsGroupLookup::displayData();
	}
	
}
MxsGroupLookup::init();
?>