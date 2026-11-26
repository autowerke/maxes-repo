<?php 
require_once ("../../eat.php");

class AttributeValue extends Xbo {
	
	protected $id;
		
	public function __construct(){
		$this->id = Xbo::getString("mxsattrvalueid");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Type", "type");
		Xbo::select("Key Attribute", "keyattr");
		Xbo::select("Name", "name");
		Xbo::select("Value", "value");
		Xbo::select("Text", "valuetext");
		Xbo::select("Remarks", "remarks");
	}
	public static function displayData(){
		AttributeValue::selectData();
		XboSet::getXboSet("mxsattrvalue");
		XboSet::displayData();	
	}
	public static function init(){
		$get = new AttributeValue();		
		$id = $get->id;
	
		if(isset($id)) {
			XboSet::getXboSet("mxsattrvalue", "mxsattrvalueid = '$id'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = AttributeValue::changes();
				}else{
				$exec = AttributeValue::add();				
			}
		}
		
		AttributeValue::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mxsattrvalue");
		XboSet::add();
	}

	public static function changes(){
		$get = new AttributeValue();		
		$id =	$get->id;		
		XboSet::getXboSet("mxsattrvalue", "mxsattrvalueid='$id'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

AttributeValue::init();




?>