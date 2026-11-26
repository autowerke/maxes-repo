<?php 
require_once ("../../eat.php");

class DocForm extends Xbo {
	public $docformnum;
	public function __construct(){
		$this->docformnum = Xbo::getString("docformnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Doc. Form Num.", "docformnum");
		Xbo::select("Doc. Form Identifier", "docformidentifier");
        Xbo::select("Doc. Form Name.", "docformname");
		Xbo::select("Procedure Num.", "proceduresnum");
		Xbo::select("Work Instr. Num", "workinstrnum");
		Xbo::select("Category", "category");
		Xbo::select("Status", "status");
		Xbo::select("Revision", "revision");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "docform");
			$jointable = $b->join;
			$where = $b->where;
			DocForm::selectData();		
			XboSet::getXboSet("docform", $where);
			renderData(null, $jointable);
		}else{
			DocForm::selectData();
			XboSet::getXboSet("docform");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new DocForm();		
		$docformnum = $get->docformnum;
		
		if(isset($docformnum)) {
			XboSet::getXboSet("docform", "docformnum = '$docformnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = DocForm::changes();
				}else{
				$exec = DocForm::add();
				
			}
		}
		DocForm::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("docform");
		XboSet::add();
	}

	public static function changes(){
		$get = new DocForm();		
		$docformnum = $get->docformnum;		
		XboSet::getXboSet("docform", "docformnum='$docformnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

DocForm::init();




?>