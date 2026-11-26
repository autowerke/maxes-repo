<?php 
require_once ("../../eat.php");

class MeterGroup extends Xbo {
	
	
	protected $metergroupnum;
	public $metergroup;	
	public function __construct(){
		$this->metergroup = Xbo::getString("metergroupnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Meter", "metergroupnum");
		Xbo::select("Description", "description");
	}
	public static function displayData(){

		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "metergroup");
			$jointable = $b->join;
			$where = $b->where;
			MeterGroup::selectData();
			XboSet::getXboSet("metergroup", $where);
			renderData(null, $jointable);
		}else{
			MeterGroup::selectData();
			XboSet::getXboSet("metergroup");
			renderData();	
		}	
	}
	public static function init(){
	
		$get = new MeterGroup();		
		$metergroupnum = $get->metergroup;
		
		if(isset($metergroupnum)) {
			
			XboSet::getXboSet("metergroup", "metergroupnum = '$metergroupnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
			
				$exec = MeterGroup::changes();
				}else{
				$exec = MeterGroup::add();
			}
		}
		
		MeterGroup::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("metergroup");
		XboSet::add();
	}

	public static function changes(){
		$get = new MeterGroup();		
		$metergroupnum = $get->metergroup;		
		XboSet::getXboSet("metergroup", "metergroupnum='$metergroupnum'");		
		XboSet::changes();
	}
	
}

MeterGroup::init();




?>