<?php 
include ("../eat.php");

class PmMeterRule extends Xbo {

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
		Xbo::select("Operator", "operator");
		Xbo::select("Trigger Point", "triggerpoint", "decimal");
		Xbo::select("Ext'd Trigger Point", "triggerpoint2", "decimal");
		Xbo::select("Auto Reserve Inventory?", "autoreserve");
		XboSet::getXboSet("pmmeterrule", $where);
		XboSet::displayData();
	}
	public static function init(){
		PmMeterRule::displayData();
	}
	
}
PmMeterRule::init();
?>