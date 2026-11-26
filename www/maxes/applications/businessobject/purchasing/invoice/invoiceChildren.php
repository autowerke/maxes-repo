<?php 
require_once ("../../eat.php");


class InvoiceChildren {

	public static function init(){
		XboSet::validateChildren();	
		InvoiceChildren::displayData();
	}
	
	public static function displayData(){
		
		$tableid = Xbo::getTableID();
		$getOwner = XboSet::getOwner();
		
		if($tableid=="mxtable_invoiceline_child"){
			
			
			Xbo::select("Line", "invoicelinenum");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Tax", "tax");
			$set = new Set();
			$getOwner = str_replace("invoiceline", "invoice", $getOwner);
			$poSet = $set->MboSetRemote("invoice")->setWhere($getOwner)->MboRemote("ponum")->getMboSet();
			$po = $poSet->getMbo();
			$ponum = $po->getString("ponum");
			if(empty($ponum)){
				Xbo::select("Unit Price Discount", "unitpricediscount", "decimal");
				Xbo::select("Total Discount Price", "linepricediscount", "decimal");
			}else{
				Xbo::select("Unit Cost", "unitcost", "decimal");
				Xbo::select("Line Cost", "linecost", "decimal");
			}
			Xbo::select("Tax Cost", "taxcost", "decimal");
			XboSet::getXboSet("invoiceline");
			$getOwner = XboSet::getOwner();
			XboSet::getXboSet("invoiceline", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invoicelineview_child"){
			Xbo::select("Line", "invoicelinenum");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Tax", "tax");
			
			$getOwner = str_replace("invoicelineview", "invoice", $getOwner);
			$set = new Set();
			$poSet = $set->MboSetRemote("invoice")->setWhere($getOwner)->MboRemote("ponum")->getMboSet();
			$po = $poSet->getMbo();
			$ponum = $po->getString("ponum");
			if(empty($ponum)){
				Xbo::select("Unit Price", "unitpricediscount", "decimal");
				Xbo::select("Total  Price", "linepricediscount", "decimal");
			}else{
				Xbo::select("Unit Cost", "unitcost", "decimal");
				Xbo::select("Line Cost", "linecost", "decimal");
			}
			Xbo::select("Tax Cost", "taxcost", "decimal");
			XboSet::getXboSet("invoicelineview");
			$getOwner = XboSet::getOwner();
			XboSet::getXboSet("invoicelineview", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invoicecharge_child" ){
			XboSet::getXboSet("invoicecharge");
			$getOwner = XboSet::getOwner();
			Xbo::select("Line", "invoicechargelinenum");
			Xbo::select("Tax Receipt ID", "taxreceiptnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("GL Debit", "gldebitacct");
			Xbo::select("GL Credit", "glcreditacct");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			XboSet::getXboSet("invoicecharge", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_invoicecharge2_child"){
			XboSet::getXboSet("invoicecharge2");
			$getOwner = XboSet::getOwner();
			Xbo::select("Line", "invoicechargelinenum");
			Xbo::select("Tax Receipt ID", "taxreceiptnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("GL Debit", "gldebitacct");
			Xbo::select("GL Credit", "glcreditacct");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			XboSet::getXboSet("invoicecharge2", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_invoiceterms_child"){
			XboSet::getXboSet("invoiceterm");
			$getOwner = XboSet::getOwner();
			Xbo::select("Line", "termlinenum");
			Xbo::select("Term ID", "termcode");
			Xbo::select("Remarks", "description");			
			XboSet::getXboSet("invoiceterm", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invpaymentline_child"){
			XboSet::getXboSet("invpaymentline");
			$getOwner = XboSet::getOwner();
			Xbo::select("Line", "invpaymentlinenum");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");	
			Xbo::select("Order Unit", "orderunit");	
			Xbo::select("Unit Cost", "unitcost", "decimal");	
			Xbo::select("Line Cost", "linecost", "decimal");	
			Xbo::select("WO", "wonum");	
			Xbo::select("Is Reimbursable?", "isreimburse");				
			XboSet::getXboSet("invpaymentline", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invoicebilling_child"){
			
			Xbo::select("Line", "billingnum");
			Xbo::select("Customer#", "customercode");
			Xbo::select("Customer Name", "name");	
			Xbo::select("Full Name", "fullname");
			Xbo::select("Status Date", "statusdate", "date");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Sub Total", "linepricediscount", "decimal");
			Xbo::select("Tax Cost", "taxcost", "decimal");
			Xbo::select("Total  Price", "totalprice", "decimal");
			
			XboSet::getXboSet("invoicebillingtax", $getOwner);
			XboSet::displayData();
		}
		
	
	}
}

InvoiceChildren::init();

?>