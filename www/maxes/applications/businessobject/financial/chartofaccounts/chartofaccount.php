<?php 
require_once ("../../eat.php");

class ChartofAccount extends Xbo {
	
	protected $exchangerateid;
	public $glaccount;
	public function __construct(){
		$this->glaccount = Xbo::getString("glaccount");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("GL Account", "glaccount");
		Xbo::select("Description", "description");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
	}
	public static function displayData(){
		ChartofAccount::selectData();
		XboSet::getXboSet("chartofaccount");
		renderData();
	}
	public static function init(){
		$get = new ChartofAccount();		
		$glaccount = $get->glaccount;
		
		if(isset($glaccount) ) {
			XboSet::getXboSet("chartofaccount", "glaccount='$glaccount'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = ChartofAccount::changes();
				}else{
				$exec = ChartofAccount::add();
			}
		}
		
		ChartofAccount::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("chartofaccount");
		XboSet::add();
	}

	public static function changes(){
		$get = new ChartofAccount();		
		$glaccount = $get->glaccount;
		XboSet::getXboSet("chartofaccount", "glaccount='$glaccount'");	
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ChartofAccount::init();




?>