<?php 
include ("../eat.php");

class PMCampaignCartLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$pmcampaignnum = Super::getParam("pmcampaignnum");
		
		
		
		$where = "pmcampaignnum = '$pmcampaignnum' ";

		
		Xbo::select("WO", "wonum");
		Xbo::select("Cart Type", "carttype");
		Xbo::select("Source Type", "sourcetype");
		Xbo::select("Source Num", "sourcenum");
		Xbo::select("Vendor", "vendorcode");
		Xbo::select("Customer", "customercode");
		Xbo::select("Quantity", "quantity", "decimal");

		if(Super::hasParam("hide")){
		}else{
			Xbo::select("New Quantity", "_input_text_newquantity", "decimal");
		}
		

		
	    Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("SQ Margin(%)", "margin", "decimal");
		Xbo::select("SQ Unit Price", "unitprice", "decimal");
		Xbo::select("Status", "status");
		XboSet::getXboSet("procurementcart", $where);
		XboSet::displayData();
	}
	public static function init(){
		PMCampaignCartLookup::displayData();
	}
	
}
PMCampaignCartLookup::init();
?>