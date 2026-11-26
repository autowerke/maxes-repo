<?php 
require_once ("../../eat.php");

class RFIDMap extends Xbo {
	
	protected $rfidmapnum;
		
	public function __construct(){
		$this->rfidmapnum = Xbo::getString("rfidmapnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("RFID Map", "rfidmapnum");
		Xbo::select("Reader", "reader");
		Xbo::select("Antenna", "antenna");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
	}
	public static function displayData(){
		RFIDMap::selectData();
		XboSet::getXboSet("rfidmap");
		renderData();
	}
	public static function init(){
		$get = new RFIDMap();		
		$rfidmapnum = $get->rfidmapnum;
		if(isset($rfidmapnum)) {
			XboSet::getXboSet("rfidmap", "rfidmapnum = '$rfidmapnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = RFIDMap::changes();
				}else{
				$exec = RFIDMap::add();
			}
		}
		
		RFIDMap::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("rfidmap");
		XboSet::add();
	}

	public static function changes(){
		$get = new RFIDMap();		
		$rfidmapnum =	$get->rfidmapnum;		
		XboSet::getXboSet("rfidmap", "rfidmapnum='$rfidmapnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

RFIDMap::init();




?>