<?php 
include ("../eat.php");

class VendorContact extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$wonum = Super::getParam("wonum");
		
		if(!empty($wonum)) $where  = "wonum='".$wonum."' ";		
		else $where = null;
				
		Xbo::select("Line Num.", "laborlinenum");
		Xbo::select("ID", "personid");
		Xbo::select("Full Name", "employees.fullname");
		Xbo::select("Start Date", "_input_text_startdate", "date");
		Xbo::select("Finish Date", "_input_text_enddate", "date");
		Xbo::select("Extended Hours", "_input_text_extendedhours", "decimal");
		Xbo::select("is Work?", "_input_text_hourtype_hourtype");
		XboSet::getXboSet("labortrans", $where);
		XboSet::displayData(["labortrans.personid = employees.personid"]);
	}
	public static function init(){
		VendorContact::displayData();
	}
	
}
VendorContact::init();
?>