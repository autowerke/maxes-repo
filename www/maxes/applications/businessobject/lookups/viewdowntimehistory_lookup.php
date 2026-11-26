<?php 
include ("../eat.php");

class ViewDowntimeHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		$assetnum = Super::getParam("assetnum");
		$assetnum = ($assetnum !== null) ? $assetnum : '';
		

		$where = (!empty($assetnum))
			? "assetnum = '$assetnum'"
			: null;
			
		
		Xbo::select("Downtime Start", "downtimestart");
		Xbo::select("Downtime End", "downtimeend");
		Xbo::select("Duration Minutes", "durationminutes", "decimal");
		Xbo::select("Failure Code", "failurecode");	
		Xbo::select("Failure Desc,", "assetfailure.description");
		Xbo::select("Category,", "assetfailure.category");	
		Xbo::select("WO", "wonum");
		Xbo::select("Replace WO", "_input_text_replacewo_workorder");
		XboSet::getXboSet("assetdowntimehistory", $where);
		XboSet::displayData(["assetfailure.failurecode = assetdowntimehistory.failurecode"]);
	}
	
	public static function init(){
		ViewDowntimeHistory::displayData();
	}
	
}

ViewDowntimeHistory::init();




?>