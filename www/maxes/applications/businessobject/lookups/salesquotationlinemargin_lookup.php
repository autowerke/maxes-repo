<?php 
include ("../eat.php");

class SQLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("SQ Line Num.", "salesquotationlinenum");
		Xbo::select("Description", "description");
		Xbo::select("Item", "itemnum");
		Xbo::select("Service", "serviceitemnum");
        Xbo::select("Sell Qty", "sellqty", "decimal");
        Xbo::select("Curr. Unit Price", "unitprice", "decimal");
        Xbo::select("Curr. Line Price", "lineprice", "decimal");
        Xbo::select("Curr. Margin(%)", "margin", "decimal");
        Xbo::select("New Margin(%)", "_input_text_newmargin", "decimal");

        if(Super::hasParam("salesquotenum")){
            $salesquotenum = Super::getParam("salesquotenum");
            $where =  "salesquotationline.salesquotenum='$salesquotenum'";
            XboSet::getXboSet("salesquotationline", $where);
        }else{
             XboSet::getXboSet("salesquotationline");
        }
		XboSet::displayData();
	
	}
	
	public static function init(){
		SQLineLookup::displayData();
	}
	
}

SQLineLookup::init();




?>