<?php 
require_once ("../../eat.php");

class ManualIntegrated extends Xbo {
	public $integmanualnum;
	public function __construct(){
		$this->integmanualnum = Xbo::getString("integmanualnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		
		Xbo::select("Integ. Manual Num.", "integmanualnum");
		Xbo::select("Doc. Identifier", "docidentifier");
		Xbo::select("Doc. Title", "manualtitle");
		Xbo::select("Period", "period");
		Xbo::select("Published Edition", "edition");
		Xbo::select("Description", "description");
		Xbo::select("Approval Date", "approvaldate");
		Xbo::select("Effective Date", "effectivedate", "date");
		Xbo::select("Expiry Date", "expirydate", "date");
		Xbo::select("Status", "status");
		Xbo::select("Revised From", "oldintegmanualnum");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "integmanual");
			$jointable = $b->join;
			$where = $b->where;
			ManualIntegrated::selectData();		
			XboSet::getXboSet("integmanual", $where);
			renderData(null, $jointable);
		}else{
			ManualIntegrated::selectData();
			XboSet::getXboSet("integmanual");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new ManualIntegrated();		
		$integmanualnum = $get->integmanualnum;
		
		if(isset($integmanualnum)) {
			XboSet::getXboSet("integmanual", "integmanualnum = '$integmanualnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = ManualIntegrated::changes();
				}else{
				$exec = ManualIntegrated::add();
				
			}
		}
		ManualIntegrated::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("integmanual");
		XboSet::add();
	}

	public static function changes(){
		$get = new ManualIntegrated();		
		$integmanualnum = $get->integmanualnum;		
		XboSet::getXboSet("integmanual", "integmanualnum='$integmanualnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ManualIntegrated::init();




?>