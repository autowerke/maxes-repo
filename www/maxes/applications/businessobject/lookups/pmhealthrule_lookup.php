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
		Xbo::select("Operator", "operator");
		Xbo::select("Trigger Point", "triggerpoint");
		Xbo::select("Lower Reset", "lowerreset");
		Xbo::select("Upper Reset", "upperreset");
		Xbo::select("Auto Reserve Inventory?", "autoreserve");
		XboSet::getXboSet("pmhealthrule", $where);
		XboSet::displayData();
	}
	public static function init(){
		PmHealthRule::displayData();
	}
	
}
PmHealthRule::init();
?>