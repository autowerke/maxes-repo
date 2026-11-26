<?php 
include ("../eat.php");

class RFSMatrectrans extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$rfsnum = Super::getParam("rfsnum");
		if(!empty($rfsnum)){
			XboSet::RFSMatrectrans();
		}
		
	}
	
	public static function init(){
		RFSMatrectrans::displayData();
	}
	
}

RFSMatrectrans::init();




?>