<?php 
include ("../eat.php");

class EnterMeterReading extends Xbo {

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
			
		
		Xbo::select("Meter Name", "meternum");
		Xbo::select("Description", "meter.description");
		Xbo::select("Last Reading", "lastreading");	
		Xbo::select("Last Reading Date", "lastreadingdate", "date");	
		Xbo::select("Delta", "_input_checkbox_delta");
		Xbo::select("Enter Date", "_input_text_newreadingdate", "date");
		Xbo::select("New Value", "_input_text_value");
		XboSet::getXboSet("assetmeter", $where);
		XboSet::displayData(["assetmeter.meternum = meter.meternum"]);
	}
	
	public static function init(){
		EnterMeterReading::displayData();
	}
	
}

EnterMeterReading::init();




?>