<?php 
include ("../eat.php");

class ManageQueryLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$tablename = null;
		if(isset($_GET['tablename'])){
			$tablename = " and appname = '".$_GET['tablename']."'" ;
		}
		$userid = Xbo::getLoginUser();
		Xbo::select("Query Name", "queryname");
		Xbo::select("Join Table", "jointable");
		Xbo::select("Where Clause", "whereclause");
		Xbo::select("Default data?", "isdefault");
		XboSet::getXboSet("mxsquery", "userid = '$userid' $tablename");
		XboSet::displayData();
	}
	public static function init(){
		ManageQueryLookup::displayData();
	}
	
}
ManageQueryLookup::init();
?>