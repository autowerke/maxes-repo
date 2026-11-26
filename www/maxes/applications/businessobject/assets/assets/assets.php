<?php 
require_once ("../../eat.php");

class Assets extends Xbo {
	
	protected $assetnum;
		
	public function __construct(){
		$this->assetnum = Xbo::getString("assetnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Asset", "assetnum");
		Xbo::select("Description", "description");
		Xbo::select("Health Rate (%)", "healthrate");
		Xbo::select("Type", "assettype");
		Xbo::select("Specification", "spectext");
		Xbo::select("Parent", "parent");
		Xbo::select("Location", "locnum");
		Xbo::select("Status", "status");
		Xbo::select("Purchase Price", "purchaseprice", "decimal");
		Xbo::select("Purchase Date", "purchasedate", "date");
		Xbo::select("Year To Date Cost", "ytdcost", "decimal");
		Xbo::select("Roll Up Cost", "rollupcost", "decimal");
		Xbo::select("Site", "sitecode");
		Xbo::select("Company", "orgcode");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "assets");
			$jointable = $b->join;
			$where = $b->where;
			Assets::selectData();
			XboSet::getXboSet("assets", $where);
			renderData(null, $jointable);	
		}else{
			Assets::selectData();
			XboSet::getXboSet("assets");
			renderData();
		}
	}
	public static function init(){
		$get = new Assets();		
		$assetnum = $get->assetnum;
		if(isset($assetnum)) {
			XboSet::getXboSet("assets", "assetnum = '$assetnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Assets::changes();
				}else{
				$exec = Assets::add();
			}
		}
		Assets::displayData();
	}
	public static function add(){
		XboSet::getXboSet("assets");
		XboSet::add();
	}

	public static function changes(){
		$get = new Assets();		
		$assetnum =	$get->assetnum;		
		XboSet::getXboSet("assets", "assetnum='$assetnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Assets::init();



?>