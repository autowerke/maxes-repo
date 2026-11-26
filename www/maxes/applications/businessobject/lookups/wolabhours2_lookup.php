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
		
		Xbo::select("Person ID", "personid");
		Xbo::select("Team", "teamnum");
		Xbo::select("Start Date", "_input_text_startdate", "date");
		Xbo::select("Finish Date", "_input_text_enddate", "date");
		Xbo::select("Work Hours", "_input_text_workhours", "decimal");
		Xbo::select("Extended Hours", "_input_text_extendedhours", "decimal");
		Xbo::select("Regular Hours", "_input_text_reghours", "decimal");
		Xbo::select("Appy Overtime?", "_input_checkbox_applyovertime");
		XboSet::getXboSet("labortrans", $where);
		XboSet::displayData();
	}
	public static function init(){
		VendorContact::displayData();
	}
	
}
VendorContact::init();
?>