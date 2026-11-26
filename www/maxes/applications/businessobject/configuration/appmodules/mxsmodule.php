<?php 
require_once ("../../eat.php");

class MXSMODULE extends Xbo {
	
	protected $modulename;
		
	public function __construct(){
		$this->modulename = Xbo::getString("modulename");
	}
	public function __destruct()
    {
       return null;
    }
	public static function selectData(){
		Xbo::select("Module Name", "modulename");
		Xbo::select("Description", "description");
		Xbo::select("Sort", "sort");
		Xbo::select("Icon", "icon");
	}
	public static function displayData(){
		MXSMODULE::selectData();
		XboSet::getXboSet("mxsmodule");
		renderData();	
	}
	public static function init(){
		$get = new MXSMODULE();		
		$modulename = $get->modulename;
		if(isset($modulename)) {
			XboSet::getXboSet("mxsmodule", "modulename = '$modulename'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = MXSMODULE::changes();
				}else{
				$exec = MXSMODULE::add();				
			}
		}
		
		MXSMODULE::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mxsmodule");
		XboSet::add();
	}

	public static function changes(){
		$get = new MXSMODULE();		
		$modulename = $get->modulename;		
		XboSet::getXboSet("mxsmodule", "modulename='$modulename'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

MXSMODULE::init();




?>