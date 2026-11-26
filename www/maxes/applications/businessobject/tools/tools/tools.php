<?php 
require_once ("../../eat.php");

class Tools extends Xbo {
	
	protected $toolnum;
	private $tablename = "tools";
	public function __construct(){
		$this->toolnum = Xbo::getString("toolnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Tool Num.", "toolnum");
		Xbo::select("Tool Type", "tooltype");
		Xbo::select("Name", "name");
		Xbo::select("Description", "description");
		Xbo::select("Serial Number", "serialnumber");
		Xbo::select("Brand", "brand");
		Xbo::select("Location", "locnum");
		Xbo::select("Holder", "personid");
		Xbo::select("WO", "wonum");
		Xbo::select("Status", "status");
		Xbo::select("Purchase Date", "purchasedate", "date");
		Xbo::select("Unit Cost", "unitcost", "decimal");
	}
	public static function displayData(){
		$get = new Tools();	
		Tools::selectData();
		XboSet::getXboSet($get->tablename);
		renderData();
	}
	public static function init(){
		$get = new Tools();		
		$toolnum = $get->toolnum;
		if(isset($toolnum)) {
			XboSet::getXboSet($get->tablename, "toolnum = '$toolnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Tools::changes();
				}else{
				$exec = Tools::add();
			}
		}
		
		Tools::displayData();
	}
	
	
	public static function add(){
		$get = new Tools();
		XboSet::getXboSet($get->tablename);
		XboSet::add();
	}

	public static function changes(){
		$get = new Tools();		
		$toolnum =	$get->toolnum;		
		XboSet::getXboSet($get->tablename, "toolnum='$toolnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Tools::init();




?>