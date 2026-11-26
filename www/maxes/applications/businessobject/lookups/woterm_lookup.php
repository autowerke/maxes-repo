<?php 
include ("../eat.php");

class WOTerm extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$wonum = Super::getParam("wonum");
		$wr = [];
		if(!empty($wonum))  array_push($wr, " wonum = '$wonum'");
		
		if(count($wr)>0){
			$where = implode(" and ", $wr);
		}else  $where =null;
		
		Xbo::select("Line Num.", "termlinenum");
		Xbo::select("Description", "description");
		Xbo::select("Condition Type Sesuai?", "meetcondition");
		Xbo::select("Entered By", "enterby");
		Xbo::select("Entered Date", "enterdate", "date");
		Xbo::select("Changed By", "changeby");
		Xbo::select("Changed Date", "changedate", "date");
		
		XboSet::getXboSet("woterm", "woterm.".$where);
		XboSet::displayData();
	}
	
	public static function init(){
		WOTerm::displayData();
	}
	
}

WOTerm::init();




?>