<?php 
require_once ("../../eat.php");

class Locations extends Xbo {
	
	protected $locnum;
		
	public function __construct(){
		$this->locnum = Xbo::getString("locnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("location Code", "locnum");
		Xbo::select("Description", "description");
		Xbo::select("Type", "type");
		Xbo::select("Map Position", "longlat");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "locations");
			$jointable = $b->join;
			$where = $b->where;
			Locations::selectData();
			XboSet::getXboSet("locations", $where);
			renderData(null, $jointable);	
		}else{
			Locations::selectData();
			XboSet::getXboSet("locations");
			renderData();
		}
	}
	public static function init(){
		$get = new Locations();		
		$locnum = $get->locnum;
		if(isset($locnum)) {
			XboSet::getXboSet("locations", "locnum = '$locnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Locations::changes();
				}else{
				$exec = Locations::add();
			}
		}
		Locations::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("locations");
		XboSet::add();
	}

	public static function changes(){
		$get = new Locations();		
		$locnum =	$get->locnum;		
		XboSet::getXboSet("locations", "locnum='$locnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Locations::init();




?>