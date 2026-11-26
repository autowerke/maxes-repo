<?php 
require_once ("../../eat.php");

class ServiceItems extends Xbo {
	
	protected $serviceitemnum;
		
	public function __construct(){
		$this->serviceitemnum = Xbo::getString("serviceitemnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Service #", "serviceitemnum");
		Xbo::select("Description", "description"); // Deskripsi utama

		Xbo::select("Type", "servicetype");
		Xbo::select("Group", "servicegroup"); 
    
		Xbo::select("Standard Cost", "stdcost", "decimal"); 
		Xbo::select("Unit", "serviceunit"); 
		Xbo::select("Est. Duration", "stdduration", "decimal"); 

		Xbo::select("Status", "status"); 
	}
	public static function displayData(){
		ServiceItems::selectData();
		XboSet::getXboSet("serviceitems");
		renderData();
	}
	public static function init(){
		$get = new ServiceItems();		
		$serviceitemnum = $get->serviceitemnum;
		if(isset($serviceitemnum)) {
			XboSet::getXboSet("serviceitems", "serviceitemnum = '$serviceitemnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = ServiceItems::changes();
				}else{
				$exec = ServiceItems::add();
			}
		}
		ServiceItems::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("serviceitems");
		XboSet::add();
	}

	public static function changes(){
		$get = new ServiceItems();		
		$serviceitemnum =	$get->serviceitemnum;		
		XboSet::getXboSet("serviceitems", "serviceitemnum='$serviceitemnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ServiceItems::init();




?>