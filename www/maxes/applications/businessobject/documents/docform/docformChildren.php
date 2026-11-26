<?php 
require_once ("../../eat.php");

class DocformChildren {

	public static function init(){
		XboSet::validateChildren();	
		DocformChildren::displayData();
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		
	}
}

DocformChildren::init();
?>