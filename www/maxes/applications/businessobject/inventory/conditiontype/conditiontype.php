<?php 
require_once ("../../eat.php");

class ConditionType extends Xbo {
	
	protected $orgcode;
	public $conditiontype;
	public function __construct(){
		$this->conditiontype = Xbo::getString("conditiontype");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Description", "description");
		Xbo::select("Condition Rate(%)", "conditionrate", "data-type='decimal'");
		
	}
	public static function displayData(){
		ConditionType::selectData();
		$whereclause = Super::IDWhereClause("conditiontypeid");
		XboSet::getXboSet("conditiontype", $whereclause);
		renderData();	
	}
	public static function init(){
		$get = new conditiontype();		
		$conditiontype = $get->conditiontype;
		if(isset($conditiontype)) {
			XboSet::getXboSet("conditiontype", "conditiontype = '$conditiontype'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = ConditionType::changes();
				}else{
				$exec = ConditionType::add();
			}
		}
		
		ConditionType::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("conditiontype");
		XboSet::add();
	}

	public static function changes(){
		$get = new ConditionType();		
		$conditiontype =	$get->conditiontype;		
		XboSet::getXboSet("conditiontype", "conditiontype='$conditiontype'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ConditionType::init();




?>