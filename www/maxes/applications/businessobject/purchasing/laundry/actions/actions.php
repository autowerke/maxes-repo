<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function LaundrySatuan(){
		$laundrynum = $_POST["calculatelnsatuan"];
		$unitprice = $_POST["unitprice"];
		$quantity = $_POST["quantity"];
		$ln = new stdClass();
		$ln->lineprice = $unitprice*$quantity;
		Set::throws($ln);
	}
	public static function GetLaundryType(){
		$laundrynum = $_POST["getlaundrytype"];
		$rowid = $_POST["rowid"];
		$set = new Set();
		$laundrytype2Set = $set->MboSetRemote("laundrytype2")->setWhere("laundrytype2id='$rowid'")->MboRemote("laundrytype")->MboRemote("description")->MboRemote("price")->getMboSet();
		$laundrytype2 = $laundrytype2Set->getMbo();
		$ltype = new stdClass();
		$ltype->laundrytype = $laundrytype2->getString("laundrytype");
		$ltype->description = $laundrytype2->getString("description");
		$ltype->price = $laundrytype2->getString("price");
		
		Set::throws($ltype);
	}
	
	public static function CalculatePrice($isupdated=null){
		$laundrynum = $_POST["calculateprice"];
		$distprice = $_POST["distprice"];
		$totalkg = $_POST["totalkg"];
		$discountprice = $_POST["discountprice"];
		if($discountprice == null) $discountprice =0;
		$discountpercentage = $_POST["discountpercentage"];
		if($discountpercentage == null) $discountpercentage =0;
		
		$laundrytype  = $_POST["laundrytype"];
		$set = new Set();
		$laundryTypeSet = $set->MboSetRemote("laundrytype")->setWhere("laundrytype='$laundrytype'")->MboRemote("price")->getMboSet();
		$laundryType = $laundryTypeSet->getMbo();
		$price = $laundryType->getString("price");
		
		$totalkg_price = $totalkg*$price;
		
		$set = new Set();
		$laundrylineSet = $set->MboSetRemote("laundryline")->setWhere("laundrynum = '$laundrynum'")->MboRemote("sum(lineprice) as lineprice")->getMboSet();
		$laundryline = $laundrylineSet->getMbo();
		$laundrylineprice = $laundryline->getString("lineprice");
		if($laundrylineprice == null) $laundrylineprice = 0;
	
		$totalprice = $laundrylineprice+$totalkg_price+$distprice;
	
		if($discountprice !=0 ){
			$discountpercentage = $discountprice*100/$totalprice;
		}else{
			if($discountprice == 0 && $discountpercentage !=0) {
				$discount = ($discountpercentage * $totalprice)/100;
			}else{
				$discountpercentage = 0;
			}
			
		}
		
		if($discountpercentage == 0){
			$discountprice = 0;
		}
		
		$discount = ($discountpercentage * $totalprice)/100;
		$totalprice_afterdiscount = $totalprice-$discount;
		
		//(discountprice2 * 100) / pretaxtotal,
		
		$result = new stdClass();
		$result->totalprice_afterdiscount = $totalprice_afterdiscount;
		$result->discountprice = $discount;
		$result->totalprice = $totalprice;
		$result->laundrysatuan = $laundrylineprice;
		$result->hargaperkg= $price;
		$result->hargatotalkg = $totalkg_price;
		$result->hargalaundrysatuan = $laundrylineprice;
		$result->discountpercentage = $discountpercentage;
		
		Set::throws($result);
		
	}
} 

Class ValidateTables {	
	public static function tbce(){
		$table = $_GET['tbce'];
		if ($table == "mxtable_vendorpolines_child" || $table == "mxtable_vendormatrectrans_child" || $table == "mxtable_vendorservrectrans_child" || $table == "mxtable_receipt_child" || $table == "mxtable_matsaletrans_child" || $table == "mxtable_servsaletrans_child" || $table == "mxtable_soshipment_child" || $table == "mxtable_sotracking_child") 
			echo "histable";
		if ($table == "mxtable_inventory_child" || $table == "mxtable_invvendor_child") echo "relatedtable";
	}
	
}
	

?>