<?php 
include ("../eat.php");

class MXSQuery extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$ownertable = Super::getParam("ownertable");
		Xbo::select("Nama Tabel", "ownertable");
		Xbo::select("Query Name", "queryname");
		Xbo::select("Select", "selectstatement");
		Xbo::select("Where", "whereclause");
		Xbo::select("Group By", "groupby");
		Xbo::select("Tipe Query", "querytype");
		XboSet::getXboSet("mxskpi", "ownertable='$ownertable' ");
		XboSet::displayData();
	}
	public static function init(){
		MXSQuery::displayData();
	}
	
}
MXSQuery::init();
?>