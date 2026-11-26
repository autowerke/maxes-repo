<?php 
require_once ("../../eat.php");

class Procedures extends Xbo {
	public $proceduresnum;
	public function __construct(){
		$this->proceduresnum = Xbo::getString("proceduresnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		
		Xbo::select("Procedure Num.", "proceduresnum");
		Xbo::select("Doc. Identifier", "docidentifier");
		Xbo::select("Doc. Name", "docname");
		Xbo::select("Description", "description");
		Xbo::select("Doc. Type", "doctype");
		Xbo::select("Category", "category");
		Xbo::select("Revision", "revision");
		Xbo::select("Effective Date", "effectivedate");
		Xbo::select("Expiry Date", "expirydate");
		Xbo::select("Status", "status");	
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "procedures");
			$jointable = $b->join;
			$where = $b->where;
			Procedures::selectData();		
			XboSet::getXboSet("procedures", $where);
			renderData(null, $jointable);
		}else{
			Procedures::selectData();
			XboSet::getXboSet("procedures");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new procedures();		
		$proceduresnum = $get->proceduresnum;
		
		if(isset($proceduresnum)) {
			XboSet::getXboSet("procedures", "proceduresnum = '$proceduresnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Procedures::changes();
				}else{
				$exec = Procedures::add();
				
			}
		}
		Procedures::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("procedures");
		XboSet::add();
	}

	public static function changes(){
		$get = new Procedures();		
		$proceduresnum = $get->proceduresnum;		
		XboSet::getXboSet("procedures", "proceduresnum='$proceduresnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Procedures::init();




?>