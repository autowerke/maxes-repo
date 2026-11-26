<?php 
include ("../eat.php");

class MeterReadingHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$assetnum = Super::getParam("assetnum");
		Xbo::select("Meteran", "meternum");
		Xbo::select("Description", "meter.description");
		Xbo::select("Type", "metertype");
		Xbo::select("Value", "value");
		Xbo::select("Tgl. Pembacaan", "readingdate","date");
		Xbo::select("Entered By", "enterby");
		XboSet::getXboSet("assetmeterhistory", "assetnum='$assetnum'");
		XboSet::displayData(["meter.meternum = assetmeterhistory.meternum"]);
	}
	public static function init(){
		MeterReadingHistory::displayData();
	}
	
}
MeterReadingHistory::init();
?>