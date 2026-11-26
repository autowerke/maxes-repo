<?php 
require_once ("../../eat.php");

class CustInventory extends Xbo {
	
	public function __construct(){
		$this->itemnum = Xbo::getString("itemnum");
		$this->customercode = Xbo::getString("customercode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Distributor Code.", "customercode");
		Xbo::select("Nama Dist/Reseller", "name");
		Xbo::select("Current Stock", "curbal", "decimal");
		Xbo::select("Safety Stock", "avgstock", "decimal");
		Xbo::select("Address", "shipaddress");
		Xbo::select("City", "shipcity");
		Xbo::select("Specification", "spectext");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "custinventory");
			$jointable = $b->join;
			$where = $b->where;
			CustInventory::selectData();
			XboSet::getXboSet("custinventory", $where);
			renderData(null, $jointable);
		}else{
			CustInventory::selectData();
			XboSet::getXboSet("custinventory");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new CustInventory();		
		$customercode = $get->customercode;
		$itemnum = $get->itemnum;
		if(isset($customercode) && isset($itemnum)) {
		XboSet::getXboSet("custinventory", "customercode = '$customercode' and itemnum='$itemnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = CustInventory::changes();
				}else{
				$exec = CustInventory::add();
			}
		}
		CustInventory::displayData();
	}
	
	
	

	public static function changes(){
		$get = new CustInventory();		
		$customercode =	$get->customercode;
		$itemnum =	$get->itemnum;			
		XboSet::getXboSet("custinventory", "customercode='$customercode' and itemnum = '$itemnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

CustInventory::init();




?>