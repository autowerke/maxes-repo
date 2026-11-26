<?php 
include ("../eat.php");

class MXSWFInboxLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$wfname = Super::getParam("wfname");
		$appname = Super::getParam("appname");
		$movewfinbox = Super::getParam("movewfinbox");
		
		if(!empty($movewfinbox)){
			XboSet::MoveWFInbox($wfname, $appname, $movewfinbox);
		}
	}
	public static function init(){
		MXSWFInboxLookup::displayCols();
	}
	
}
MXSWFInboxLookup::init();
?>