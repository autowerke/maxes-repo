<?php 
require_once ("../../eat.php");

class ItemChildren {

	public static function init(){
		
		$childPackage = Super::getParam("childPackage");
	
		if(!empty($childPackage)) {
				
			$ownership = Super::getParam("ownership");
			$object = $ownership["xboset"];
			$objectid =  $ownership["xbosetid"];
			$itemnum =  $ownership["owner"][0]["itemnum"];
			$childrentable =  Super::getParam("childrentable");
			
			if($childrentable=="itemcondition"){
				
				$conditiontype = $childPackage["conditiontype"];
				$conditionrate = $childPackage["conditionrate"];
				
				$itemconditionSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditionrate>=100")->MboRemote("conditiontype")->getMboSet();
				
				if($itemconditionSet->isEmpty() && $conditionrate < 100 ){
					Super::sendJsonResponse("error", "You must enter 100% Condition Rate first!");
				}
				$checkRate = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditionrate='$conditionrate'")->MboRemote("conditionrate")->getMboSet();
				if(!$checkRate->isEmpty()){
					Super::sendJsonResponse("error", "Condition Rate : $conditionrate [$conditiontype] has already been exist!");
				}
				if($conditionrate == 100){
					(new Set())->useTransactionManager()->MboSetRemote("invprice")->where("itemnum=?", $itemnum)
					->setValue("conditiontype", $conditiontype)->change();

					(new Set())->useTransactionManager()->MboSetRemote("invcost")->where("itemnum=?", $itemnum)
					->setValue("conditiontype", $conditiontype)
					->setValue("conditionrate", $conditionrate)
					->change();
					
					(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->where("itemnum=?", $itemnum)
					->setValue("conditiontype", $conditiontype)
					->setValue("conditionrate", $conditionrate)
					->change();
				}
			}
			if($childrentable=="itemalternate"){
				
				$relatedItem = $_POST["childPackage"]["relateditem"];
				if($relatedItem==$itemnum){
					Super::sendJsonResponse("error", "Cannot assign same item!");
				}else{
					
					$enterby = Xbo::getLoginUser();
					$enterdate = Xbo::setEnterDate();
					(new Set())->useTransactionManager()->MboSetRemote("itemalternate")
					->setValue("itemnum", $relatedItem)
					->setValue("relateditem", $itemnum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
					
				}
			}
			if($childrentable == "itemstruct"){
				
				$subitemnum = $childPackage["subitemnum"];
				if($subitemnum==$itemnum){
					Super::sendJsonResponse("error", "Cannot assign same item!");
				}
			}
			
		}
		
		XboSet::validateChildren();
		ItemChildren::displayData();
	}

	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_itemcondition_child"){
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Description", "conditiontype.description");
			Xbo::select("Condition Rate(%)", "conditionrate", "decimal");
			XboSet::getXboSet("itemcondition", $getOwner);
			XboSet::displayData(["conditiontype.conditiontype = itemcondition.conditiontype"]);
		}
		if($tableid=="mxtable_itembarcode_child"){
			Xbo::select("Barcode", "barcode");
			Xbo::select("Type", "barcodeformat");
			Xbo::select("Karakter Terakhir", "barcodelastchar");
			XboSet::getXboSet("itembarcode", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_itemalternate_child"){
			Xbo::select("Alternate Item", "relateditem");
			Xbo::select("Description", "item.description");
			Xbo::select("Manufacturer", "item.manufacturer");
			Xbo::select("Specification", "item.spectext");
			XboSet::getXboSet("itemalternate", $getOwner);
			XboSet::displayData("item.itemnum = itemalternate.relateditem");
		}
		if($tableid=="mxtable_inventory_child"){
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rak", "racknum");
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Quantity", "curbal", "decimal");
			XboSet::getXboSet("invbalance", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invvendor_child"){
			Xbo::select("Vendor", "vendorcode");
			Xbo::select("Vendor/Manufacturer", "vendor.name");
			Xbo::select("Last Price", "lastprice", "decimal");
			Xbo::select("Last Order Date", "lastorderdate", "date");
			Xbo::select("Warehouse Receipt", "warehouse");
			Xbo::select("Receipt Date", "enterdate", "date");
			XboSet::getXboSet("invvendor", $getOwner);
			XboSet::displayData("invvendor.vendorcode = vendor.vendorcode");
		}
		if($tableid=="mxtable_itempartnum_child"){
			Xbo::select("Part Number", "partnum");
			Xbo::select("Vendor Code", "vendor");
			Xbo::select("Vendor Name", "vendor.name");
			Xbo::select("Country", "country");
			Xbo::select("Manufacturer Code", "manufacturer");
			XboSet::getXboSet("itempartnum", $getOwner);
			XboSet::displayData("itempartnum.vendor = vendor.vendorcode");
		}
		if($tableid=="mxtable_itemsclassifications_child"){
			Xbo::select("Attribute Name", "attributename");
			Xbo::select("Description", "attribute.description");
			Xbo::select("Value", "value");
			Xbo::select("Unit", "unit");
			Xbo::select("Notice", "description");
			XboSet::getXboSet("itemclassification", $getOwner);
			XboSet::displayData(["itemclassification.attributename = attribute.attributename"]);
		}
		if($tableid=="mxtable_itemstructure_child"){
			Xbo::select("Item", "subitemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("itemstruct", $getOwner);
			XboSet::displayData(["itemstruct.subitemnum = item.itemnum"]);
		}
		if($tableid=="mxtable_itemhazard_child"){
			
			Xbo::select("Hazard", "hazardnum");
			Xbo::select("Description", "hazard.description");
			Xbo::select("Qualification", "qualification");
			Xbo::select("Classification", "classification");
			XboSet::getXboSet("itemhazard", $getOwner);
			XboSet::displayData(["itemhazard.hazardnum = hazard.hazardnum"]);
		}
		if($tableid=="mxtable_itemprecautions_child"){
			Xbo::select("No.", "precautionlinenum");
			Xbo::select("Precaution", "precaution");
			Xbo::select("Description", "description");
			Xbo::select("Notice", "remarks");
			XboSet::getXboSet("itemprecaution", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_matprodtrans_child"){
			Xbo::select("WO.", "wonum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Batch", "batchnum");
			Xbo::select("Prod. Start Date", "prodstartdate", "date");
			Xbo::select("Prod. End Date", "prodenddate", "date");
			XboSet::getXboSet("matprodtrans", $getOwner);
			XboSet::displayData();
		}

		if($tableid=="mxtable_servprodtrans_child"){
			Xbo::select("WO.", "wonum");
			Xbo::select("Item", "serviceitemnum");
			Xbo::select("Description", "description");
			Xbo::select("PR", "prnum");
			Xbo::select("PR Line", "prlinenum");
			Xbo::select("PO", "ponum");
			Xbo::select("PO Line", "polinenum");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Batch", "batchnum");
			Xbo::select("Prod. Start Date", "prodstartdate", "date");
			Xbo::select("Prod. End Date", "prodenddate", "date");
			XboSet::getXboSet("servprodtrans", $getOwner);
			XboSet::displayData();
		}


		if($tableid=="mxtable_toolprodtrans_child"){
			Xbo::select("WO.", "wonum");
			Xbo::select("Item", "toolnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Prod. Start Date", "prodstartdate", "date");
			Xbo::select("Prod. End Date", "prodenddate", "date");
			Xbo::select("Batch", "batchnum");
			XboSet::getXboSet("toolprodtrans", $getOwner);
			XboSet::displayData();
		}
	}
}

ItemChildren::init();

?>