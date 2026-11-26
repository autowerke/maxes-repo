<?php 
include ("../eat.php");

class ContractLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){

        if (Super::hasParam("contractnum")) {
            $contractnum = Super::getParam("contractnum");
            $where = "contractnum = '$contractnum'";
        }else{
            $where = null;
        }
		Xbo::select("Contract", "contractnum");
		Xbo::select("Description", "description");
        Xbo::select("Line TYpe", "linetype");
		Xbo::select("Item", "itemnum");
        Xbo::select("Service Item", "serviceitemnum");
		Xbo::select("Unit Cost", "unitcost");
		XboSet::getXboSet("contractline", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		ContractLineLookup::displayData();
	}
	
}

ContractLineLookup::init();




?>