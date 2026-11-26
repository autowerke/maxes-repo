<?php 
require_once ("../../eat.php");

class mxsgroups extends Xbo {
	
	protected $groupnum;
		
	public function __construct(){
		$this->groupnum = Xbo::getString("groupnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Group", "groupnum");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		mxsgroups::selectData();
		$whereclause = Super::IDWhereClause("mxsgroupsid");
		XboSet::getXboSet("mxsgroups", $whereclause);
		renderData();	
	}
	public static function init(){
		$get = new mxsgroups();		
		$groupnum = $get->groupnum;
		if(isset($groupnum)) {
			XboSet::getXboSet("mxsgroups", "groupnum = '$groupnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = mxsgroups::changes();
				}else{
				$exec = mxsgroups::add();
			}
		}
		
		mxsgroups::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mxsgroups");
		XboSet::add();
	}

	public static function changes(){
		$get = new mxsgroups();		
		$groupnum =	$get->groupnum;		
		XboSet::getXboSet("mxsgroups", "groupnum='$groupnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

mxsgroups::init();




?>