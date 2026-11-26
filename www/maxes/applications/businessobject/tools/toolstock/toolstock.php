<?php 
require_once ("../../eat.php");

class ToolStock extends Xbo {
	
	protected $toolstockid;
	private $tablename = "toolstock";
	public function __construct(){
		$this->toolstockid = Xbo::getString("toolstockid");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Tool Type", "tooltype");
		Xbo::select("Location", "locnum");
		Xbo::select("Current Balance", "curbal", "decimal");
		Xbo::select("Average Cost", "averagecost", "decimal");
		Xbo::select("Qty. Available", "qtyavailable", "decimal");
	}
	public static function displayData(){
		$get = new ToolStock();	
		ToolStock::selectData();
		XboSet::getXboSet($get->tablename);
		renderData();
	}
	public static function init(){
		$get = new ToolStock();		
		$toolstockid = $get->toolstockid;
		if(isset($toolstockid)) {
			XboSet::getXboSet($get->tablename, "toolstockid = '$toolstockid'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = ToolStock::changes();
				}else{
				$exec = ToolStock::add();
			}
		}
		
		ToolStock::displayData();
	}
	
	
	public static function add(){
		$get = new ToolStock();
		XboSet::getXboSet($get->tablename);
		XboSet::add();
	}

	public static function changes(){
		$get = new ToolStock();		
		$toolstockid =	$get->toolstockid;		
		XboSet::getXboSet($get->tablename, "toolstockid='$toolstockid'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ToolStock::init();




?>