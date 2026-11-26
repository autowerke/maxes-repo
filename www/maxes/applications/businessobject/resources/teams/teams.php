<?php 
require_once ("../../eat.php");

class Teams extends Xbo {
	
	
	protected $teamnum;
		
	public function __construct(){
		$this->teamnum = Xbo::getString("teamnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Team", "teamnum");
		Xbo::select("Description", "description");
	}
	public static function displayData(){

		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "teams");
			$jointable = $b->join;
			$where = $b->where;
			Teams::selectData();
			XboSet::getXboSet("teams", $where);
			renderData(null, $jointable);
		}else{
			Teams::selectData();
			$whereclause = Super::IDWhereClause("teamsid");
			XboSet::getXboSet("teams", $whereclause);
			renderData();	
		}	
	}
	public static function init(){
	
		$get = new Teams();		
		$teamnum = $get->teamnum;
		if(isset($teamnum)) {
			XboSet::getXboSet("teams", "teamnum = '$teamnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Teams::changes();
				}else{
					
				$exec = Teams::add();
			}
		}
		
		Teams::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("teams");
		XboSet::add();
	}

	public static function changes(){
		$get = new Teams();		
		$teamnum = $get->teamnum;		
		XboSet::getXboSet("teams", "teamnum='$teamnum'");		
		XboSet::changes();
	}
	
}

Teams::init();




?>