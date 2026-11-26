<?php 
require_once ("../../eat.php");

class Item extends Xbo {

	
	public $item;	
	public function __construct(){
		$this->item = Xbo::getString("itemnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Manufacturer", "manufacturer");
		Xbo::select("Brand", "brand");
		Xbo::select("Item Group", "itemgroup");
		Xbo::select("Item Sub Group", "itemsubgroup");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Issue Unit", "issueunit");			
		Xbo::select("Status", "status");
		Xbo::select("Spec.", "spectext");
		Xbo::select("Usage Ranking.", "usageranking");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "item");
			$jointable = $b->join;
			$where = $b->where;
			Item::selectData();
			XboSet::getXboSet("item", $where);
			renderData(null, $jointable);	
		}else{
			Item::selectData();
			$whereclause = Super::IDWhereClause("itemid");
			XboSet::getXboSet("item", $whereclause);
			renderData();	
		}	
	}
	
	public static function init(){
		$get = new item();		
		$item = $get->item;
	
		if(isset($item)) {
			XboSet::getXboSet("item", "itemnum = '$item'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				
				$exec = Item::changes();
				}else{
				$exec = Item::add();
				
			}
		}
		Item::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("item");
		XboSet::add();
	}

	public static function changes(){
		$get = new Item();		
		$item =	$get->item;		
		XboSet::getXboSet("item", "itemnum='$item'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Item::init();




?>