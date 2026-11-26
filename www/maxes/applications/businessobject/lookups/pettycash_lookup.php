<?php 
include ("../eat.php");

class PettyCashLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		/*
		$notcancel = "and po.status not in('cancel', 'closed')";
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where = "orgcode ='".$orgcode."' $notcancel";	
		
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "sitecode ='".$_POST['sitecode']."' $notcancel";	
		
		if(!empty($sitecode) && !empty($orgcode)) $where  = "orgcode='".$orgcode."' and sitecode ='".$sitecode."' $notcancel";
		
		else{
			$where = " po.status not in ('cancel')";
		}	
		*/
		
		Xbo::select("Petty Cash", "pettycashnum");
		Xbo::select("Description", "description");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
		Xbo::select("Line Cost", "totalcost", "decimal");
		//XboSet::getXboSet("po", $where);
		XboSet::getXboSet("pettycash");
		XboSet::displayData();
	
	}
	
	public static function init(){
		PettyCashLookup::displayData();
	}
	
}

PettyCashLookup::init();




?>