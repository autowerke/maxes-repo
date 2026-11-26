<?php 

require_once ("../eat.php");


class CommonChildrenTable {
	public static function init(){
		if(isset($_POST['modChildPackage'])){
			XboSet::updateTableRowDetail();
		}else{
			XboSet::showTableRowDetail();
		}	
	}
	
}
CommonChildrenTable::init();
?>