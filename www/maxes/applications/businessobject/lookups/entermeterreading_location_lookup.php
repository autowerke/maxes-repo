<?php 
include ("../eat.php");

class EnterMeterReadingLocation extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		$locnum = Super::getParam("locnum");
		$locnum = ($locnum !== null) ? $locnum : '';

		$where = (!empty($locnum))
			? "locnum = '$locnum'"
			: null;
			
		Xbo::select("Meter Name", "meternum");
		Xbo::select("Description", "meter.description");
		Xbo::select("Last Reading", "lastreading");	
		Xbo::select("Last Reading Date", "lastreadingdate", "date");
		Xbo::select("Delta", "_input_checkbox_delta");
		Xbo::select("Enter Date", "_input_text_newreadingdate", "date");
		Xbo::select("New Value", "_input_text_value");
		XboSet::getXboSet("locationmeter", $where);
		XboSet::displayData(["locationmeter.meternum = meter.meternum"]);
	}
	
	public static function init(){
		EnterMeterReadingLocation::displayData();
	}
	
}

EnterMeterReadingLocation::init();




?>