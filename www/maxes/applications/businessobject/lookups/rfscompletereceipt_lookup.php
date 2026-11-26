<?php 
include ("../eat.php");

class RFSCompleteReceipt extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$rfsnum = Super::getParam("rfsnum");
		if(!empty($rfsnum)){
			XboSet::RFSCompleteReceipt();
		}
		
	}
	
	public static function init(){
		RFSCompleteReceipt::displayData();
	}
	
}

RFSCompleteReceipt::init();




?>