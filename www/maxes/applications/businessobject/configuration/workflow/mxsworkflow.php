<?php 
require_once ("../../eat.php");

class MXSWORKFLOW extends Xbo {
	
	protected $wfname;
		
	public function __construct(){
		$this->wfname = Xbo::getString("wfname");
	}
	public function __destruct()
    {
       return null;
    }
	public static function selectData(){
		Xbo::select("Workflow Name", "wfname");
		Xbo::select("Description", "description");
		Xbo::select("Is Active?", "isactive");
	}
	public static function displayData(){
		MXSWORKFLOW::selectData();
		XboSet::getXboSet("mxsworkflow");
		renderData();	
	}
	public static function init(){
		$get = new MXSWORKFLOW();		
		$wfname = $get->wfname;
		if(isset($wfname)) {
			XboSet::getXboSet("mxsworkflow", "wfname = '$wfname'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = MXSWORKFLOW::changes();
				}else{
				$exec = MXSWORKFLOW::add();				
			}
		}
		
		MXSWORKFLOW::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mxsworkflow");
		XboSet::add();
	}

	public static function changes(){
		$get = new MXSWORKFLOW();		
		$wfname = $get->wfname;		
		XboSet::getXboSet("mxsworkflow", "wfname='$wfname'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

MXSWORKFLOW::init();




?>