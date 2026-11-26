<?php 
include ("../eat.php");

class BillingLine extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$where =null;
		
		$customercode = Super::getParam("customercode");
		$customerhqcode = Super::getParam("customerhqcode");
		$debt = " billing.unpaidprice < 0";
		$notcancel = "and billing.status in('approved') and $debt";	
		//$where2 = " and billinglineid not in (select invoiceline.importkey from invoiceline   WHERE importkey is not null and invoiceline.billingnum is not null $debt)";
		$where2 = "";
		if(!empty($customercode) && empty($customerhqcode)) $where  = "billing.customercode ='".$customercode."' $notcancel ".$where2;	
		else{
			if(empty($customercode) && !empty($customerhqcode)) {
				$where  = "billing.customercode in (select relatedcustomercode from relatedcustomer  WHERE  relatedcustomer.customercode='$customerhqcode') or billing.customercode ='".$customerhqcode."' and $debt ";
				$where  = $where . $where2 . $notcancel;	
			}
			else $where = "$debt";
		}
		
		Xbo::select("customer", "billing.customercode");
		Xbo::select("Tagihan", "billingnum");
		Xbo::select("Line Num.", "billinglinenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Issue Unit", "issueunit");
		Xbo::select("Biaya/Unit", "unitcost", "decimal");
		Xbo::select("Unit Cost", "unitprice", "decimal");
		Xbo::select("Total Price", "lineprice", "decimal");
		Xbo::select("Disc. Unit Cost", "unitpricediscount", "decimal");
		Xbo::select("Disc. Unit Cost", "billing.totaltax", "decimal");
		Xbo::select("Disc. Total Price", "linepricediscount", "decimal");
		Xbo::select("Unpaid", "billing.unpaidprice", "decimal");
		Xbo::select("Tgl. Approved", "billing.statusdate", "date");
		Xbo::select("Di-entry oleh", "billing.enterby");
		XboSet::getXboSet("billingline", $where);
		XboSet::displayData(["billing.billingnum = billingline.billingnum"]);
	
	}
	
	public static function init(){
		BillingLine::displayData();
	}
	
}

BillingLine::init();




?>