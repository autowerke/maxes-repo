<?php 
require_once ("../../eat.php");

class MXSAIDOMAIN extends Xbo {
	
	protected $domainname;
		
	public function __construct(){
		$this->domainname = Xbo::getString("domainname");
	}
	public function __destruct()
    {
       return null;
    }
	public static function selectData(){
		Xbo::select("Domain Name", "domainname");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		MXSAIDOMAIN::selectData();
		XboSet::getXboSet("mxsaidomain");
		renderData();	
	}
	public static function init(){
		$get = new MXSAIDOMAIN();		
		$domainname = $get->domainname;
		if(isset($domainname)) {
			XboSet::getXboSet("mxsaidomain", "domainname = '$domainname'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = MXSAIDOMAIN::changes();
				}else{
				$exec = MXSAIDOMAIN::add();				
			}
		}
		
		MXSAIDOMAIN::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mxsaidomain");
		XboSet::add();
	}

	public static function changes(){
		$get = new MXSAIDOMAIN();		
		$domainname = $get->domainname;		
		XboSet::getXboSet("mxsaidomain", "domainname='$domainname'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

MXSAIDOMAIN::init();




?>