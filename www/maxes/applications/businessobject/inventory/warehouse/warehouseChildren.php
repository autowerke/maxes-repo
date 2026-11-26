<?php 
require_once ("../../eat.php");

class ItemChildren {

	public static function init(){
		global $msg;
		XboSet::validateChildren();	
		ItemChildren::displayData();
	}
	public static function displayData(){
		

		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_warehouseline_child"){
			$getOwner = XboSet::getOwner();
			Xbo::select("Item", "warehouselinenum");
			Xbo::select("Rack Number", "racknum");
			Xbo::select("Gudang", "description");
			Xbo::select("Rak", "racktype");
			Xbo::select("Width", "width", "decimal");
			Xbo::select("Height", "height", "decimal");
			Xbo::select("Length", "length", "decimal");
			Xbo::select("Space Volume", "volume", "decimal");
			XboSet::getXboSet("warehouseline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_storeroomitems_child"){
			XboSet::getXboSet("v_invbalance");
			$getOwner = XboSet::getOwner();	
			Xbo::select("Item", "itemnum");
			Xbo::select("Deskripsi", "item.description");
			Xbo::select("Volume (cm3)", "item.volume", "decimal");
			Xbo::select("Total Item Volume (cm3)", "item.volume*curbal as totalitemvolume", "decimal");
			Xbo::select("Gudang", "warehouse");
			Xbo::select("Rak", "racknum");
			Xbo::select("Current Balance", "curbal", "decimal");
			XboSet::getXboSet("v_invbalance", $getOwner);
			XboSet::displayData(["v_invbalance.itemnum = item.itemnum "]);
		}
		
	}
}

ItemChildren::init();

?>