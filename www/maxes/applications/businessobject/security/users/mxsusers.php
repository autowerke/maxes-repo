<?php 
/*author : Tommy Nitidiryo Sumarsono (c) 2015
*/
require_once ("../../eat.php");

class MxsUsers extends Xbo {
	
	protected $userid;
		
	public function __construct(){
		$this->userid = Xbo::getString("userid");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("User ID", "userid");
		Xbo::select("Employee ID", "personid");
		Xbo::select("First Name", "employees.firstname");
		Xbo::select("Last Name", "employees.lastname");
		Xbo::select("Full Name", "employees.fullname");
		Xbo::select("Department", "employees.department");
		Xbo::select("Title", "employees.title");
		Xbo::select("Status", "status");
	}
	public static function displayData(){
		$defaultjoin =["mxsusers.personid = employees.personid"];
		MxsUsers::selectData();
		$whereclause = Super::IDWhereClause("mxsusersid", "status!='DELETED'");
		XboSet::getXboSet("mxsusers",$whereclause);
		renderData($defaultjoin);
	}
	
	
	public static function init(){
		$get = new MxsUsers();		
		$userid = $get->userid;
		if(isset($userid)) {
			XboSet::getXboSet("mxsusers", "userid = '$userid'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = MxsUsers::changes();
				}else{
				$exec = MxsUsers::add();
			}
		}
		
		MxsUsers::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mxsusers");
		XboSet::add();
	}

	public static function changes(){
		$get = new MxsUsers();		
		$userid =	$get->userid;		
		XboSet::getXboSet("mxsusers", "userid='$userid'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

MxsUsers::init();




?>