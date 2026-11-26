<?php 
require_once ("../../eat.php");

class pm extends Xbo {
	
	protected $pmnum;
		
	public function __construct(){
		$this->pmnum = Xbo::getString("pmnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("PM", "pmnum");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Description", "description");
		Xbo::select("Start Date", "startdate");
		Xbo::select("Interval", "intervalvalue");
		Xbo::select("Interval Unit", "intervalunit");
		Xbo::select("Owner", "owner");
		Xbo::select("Leader", "leader");
	}
	public static function displayData(){
		pm::selectData();
		XboSet::getXboSet("pm");
		renderData();
	}
	public static function init(){
		$get = new pm();		
		$pmnum = $get->pmnum;
		if(isset($pmnum)) {
			XboSet::getXboSet("pm", "pmnum = '$pmnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = pm::changes();
				}else{
				$exec = pm::add();
			}
		}
		
		pm::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("pm");
		XboSet::add();
	}

	public static function changes(){
		$get = new pm();		
		$pmnum =	$get->pmnum;		
		XboSet::getXboSet("pm", "pmnum='$pmnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

pm::init();




?>