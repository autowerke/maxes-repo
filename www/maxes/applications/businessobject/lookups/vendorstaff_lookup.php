<?php
include ("../eat.php");

class SriVendor extends Xbo {

	public function __destruct()
    {
        return null;
    }

	public static function displayData(){
		$srinum = Super::getParam("srinum");
		$where = null;

		if (!empty($srinum)) {
			$where = "srivendor.srinum = '$srinum'";
		}
		
		Xbo::select("Line", "srivendorlinenum");
		Xbo::select("Staff Name", "staffname"); 
		Xbo::select("Position", "staffposition"); 
		Xbo::select("Job Desc", "jobdesc"); 
		Xbo::select("Phone", "phonenum"); 
		XboSet::getXboSet("srivendor", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		SriVendor::displayData();
	}
	
}

SriVendor::init();