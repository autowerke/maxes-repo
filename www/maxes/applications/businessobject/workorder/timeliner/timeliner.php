<?php 
require_once ("../../eat.php");

class Timeline extends Xbo {
	
	protected $timelinernum;
		
	public function __construct(){
		$this->timelinernum = Xbo::getString("timelinernum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Kode Timeline", "timelinernum");
		Xbo::select("Description", "description");
		Xbo::select("Target Dimulai", "targstartdate", "date");
		Xbo::select("Target Finish Date", "targcompdate", "date");
		Xbo::select("Total Planned Cost", "totalplannedcost", "decimal");
		Xbo::select("Total Actual Cost", "totalactualcost", "decimal");
	}
	public static function displayData(){
		Timeline::selectData();
		XboSet::getXboSet("timeliner");
		renderData();
	}
	public static function init(){
		$get = new Timeline();		
		$timelinernum = $get->timelinernum;
		if(isset($timelinernum)) {
			XboSet::getXboSet("timeliner", "timelinernum = '$timelinernum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Timeline::changes();
				}else{
				$exec = Timeline::add();
			}
		}
		
		Timeline::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("timeliner");
		XboSet::add();
	}

	public static function changes(){
		$get = new Timeline();		
		$timelinernum =	$get->timelinernum;		
		XboSet::getXboSet("timeliner", "timelinernum='$timelinernum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Timeline::init();




?>