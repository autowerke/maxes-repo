<?php 
include ("../eat.php");

class fileinfoLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$ownertable = Super::getParam("ownertable");
		$ownerID = Super::getParam("ownerid");
		$where = "ownertable='$ownertable' and ownerid='$ownerID'";
		Xbo::select("File Name", "filename");
		Xbo::select("File Type", "type");
		Xbo::select("Description", "description");
		Xbo::select("Upload Date", "uploaddate", "date");
		Xbo::select("File Size (KB)", "filesize");
		Xbo::select("Default ?", "filedefault");
		XboSet::getXboSet("fileinfo", $where);
		XboSet::displayData();
	}
	public static function init(){
		fileinfoLookup::displayData();
	}
	
}
fileinfoLookup::init();
?>