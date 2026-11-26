<?php 
include ("../eat.php");

class PmHealthRule extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		if(Super::hasParam("pmnum")){
			$pmnum = Super::getParam("pmnum");
			$where  = "pmnum = '$pmnum'";
		}else{
			$where = "";
		}
		Xbo::select("Meter", "meternum");
		Xbo::select("Frequency", "Frequency");
		Xbo::select("Alert Lead( (days)", "alertlead");
		Xbo::select("Generate WO Ahead (days)", "generatewoahead");
		Xbo::select("Auto Reserve Inventory?", "autoreserve");
		XboSet::getXboSet("pmmeterfreq", $where);
		XboSet::displayData();
	}
	public static function init(){
		PmHealthRule::displayData();
	}
	
}
PmHealthRule::init();
?>