<?php 
include ("../eat.php");

class RFSLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$rfsinput = Super::getParam("rfsinput");
		if(!empty($rfsinput)){
			XboSet::rfsinput();
		}
		
	}
	
	public static function init(){
		RFSLineLookup::displayData();
	}
	
}

RFSLineLookup::init();




?>