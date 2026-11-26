<?php 
include ("../eat.php");

class WorkTemplates extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		if(isset($_GET['assetnum'])){
			$assetnum = $_GET['assetnum'];
			$where  = " and assetnum = '$assetnum' ";
		}else{
			$where = "";
		}
		
		Xbo::select("Job Plan", "wtnum");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Description", "description");
		Xbo::select("Work Type", "worktype");
		Xbo::select("Work Classification", "workclass");
		XboSet::getXboSet("worktemplates", "status in('active') $where");
		XboSet::displayData();
	}
	public static function init(){
		WorkTemplates::displayData();
	}
	
}
WorkTemplates::init();
?>