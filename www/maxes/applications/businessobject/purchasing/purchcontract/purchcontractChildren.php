<?php 
require_once ("../../eat.php");

class ContractChildren {

	public static function init(){
		XboSet::validateChildren();	
		ContractChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();

		if($tableid=="mxtable_contractterm_child"){
			
			Xbo::select("No.", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Remarks", "description");			
			XboSet::getXboSet("contractterm", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_contractterm2_child"){
			Xbo::select("Line Num.", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("contractterm2", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_contractline_child"){
			Xbo::select("Line Num.", "contractlinenum");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Service Item", "serviceitemnum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Min. Qty", "minqty", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Tax Code", "taxcode");
			XboSet::getXboSet("contractline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_contractprice_child") ContractChildren :: ContractPrice($getOwner);
		$linktable = Super::getParam("linktable");
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
			if($tableName=="mxtable_contractprice_child")  ContractChildren :: ContractPrice($where);
		}
	}

	public static function ContractPrice($getOwner){
		Xbo::select("Line Num.", "contractpricelinenum");
		Xbo::select("For Line Num.", "contractlinenum");
		Xbo::select("Description.", "description");
		Xbo::select("Min. Qty", "minqty", "decimal");
		Xbo::select("Max. Qty", "maxqty", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Discount(%)", "discountpercent", "decimal");
		XboSet::getXboSet("contractprice", $getOwner);
		XboSet::displayData();
	}
}

ContractChildren::init();

?>