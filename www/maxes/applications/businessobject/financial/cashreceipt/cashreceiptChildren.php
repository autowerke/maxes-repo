<?php 
require_once ("../../eat.php");

class CashReceiptChildren {

	public static function init(){
		XboSet::validateChildren();	
		CashReceiptChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_cashreceiptline_child"){
			
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("Tipe Baris", "linetype");
			Xbo::select("Line Num.", "Invoicelinenum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Satuan Jual", "saleunit");	
			Xbo::select("Total  Price", "lineprice", "decimal");			
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Tgl. Dibayar", "statusdate", "date");
			XboSet::getXboSet("cashreceiptline", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_cashoutline_child"){
			
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("Tipe Baris", "linetype");
			Xbo::select("Line Num.", "Invoicelinenum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Order Unit", "orderunit");	
			Xbo::select("Line Cost", "linecost", "decimal");			
			Xbo::select("Financial Period", "financialperiod");
			XboSet::getXboSet("cashoutline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_taxreceipt_child"){
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("E-Faktur", "taxreceiptnum");
			Xbo::select("Description", "description");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Quantity", "quantity");
			Xbo::select("Line Cost", "lineprice", "decimal");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Transfered Date", "statusdate2", "date");	
			Xbo::select("Jadwal Pembayaran", "scheddate", "date");
			Xbo::select("GL Debit", "gldebitacct");			
			Xbo::select("GL Credit", "glcreditacct");
			Xbo::select("Status Pembayaran", "status");
			Xbo::select("Tgl. Dibayar", "paiddate", "date");
			XboSet::getXboSet("taxreceipt", $getOwner. " and taxtype = 'out'");
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_taxreceipt2_child"){
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("E-Faktur", "taxreceiptnum");
			Xbo::select("Description", "description");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Quantity", "quantity");
			Xbo::select("Line Cost", "lineprice", "decimal");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Transfered Date", "statusdate2", "date");	
			Xbo::select("Jadwal Pembayaran", "scheddate", "date");
			Xbo::select("GL Debit", "gldebitacct");			
			Xbo::select("GL Credit", "glcreditacct");
			Xbo::select("Status Pembayaran", "status");
			XboSet::getXboSet("taxreceipt", $getOwner. " and taxtype = 'in'");
			XboSet::displayData();
		}
		
		
		if($tableid=="mxtable_cashinbank_child"){
			Xbo::select("Line Num.", "cashinbanklinenum");
			Xbo::select("customer", "customercode");
			Xbo::select("Description", "description");
			Xbo::select("Catatan Bank", "banknotes");
			Xbo::select("Jumlah Total", "totaltransfer", "decimal");
			Xbo::select("Transfered Date", "transferdate", "date");	
			Xbo::select("Termasuk Pajak?", "includetax");			
			Xbo::select("Financial Period", "financialperiod");
			XboSet::getXboSet("cashinbank", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_cashrecaddcost_child"){
			Xbo::select("Line Num.", "cashrecaddcostlinenum");
			Xbo::select("Line Num.", "cashinbanklinenum");
			Xbo::select("customer", "description");
			Xbo::select("Notes", "notes");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("Transaction Date", "transdate", "date");
			Xbo::select("Quantity", "quantity", "decimal");	
			Xbo::select("Order Unit", "measureunit");	
			Xbo::select("Unit Cost", "unitcost", "decimal");	
			Xbo::select("Line Cost", "linecost", "decimal");				
			Xbo::select("Cost Type", "costtype");			
			XboSet::getXboSet("cashrecaddcost", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_cashrecbalance_child"){
			Xbo::select("Line Num.", "pettycashnum");
			Xbo::select("Invoice", "invoicenum");
			
			Xbo::select("Invoice Line", "invoicelinenum");
			Xbo::select("Invoice Description", "pettycashline.description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "pettycashline.orderunit");
			Xbo::select("Invoice Date", "invoice.invoicedate", "date");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("GL Debit", "gldebitacct");
			Xbo::select("GL Credit", "glcreditacct");
			XboSet::getXboSet("cashrecbalance", $getOwner);
			XboSet::displayData(["pettycashline.invoicenum = cashrecbalance.invoicenum and cashrecbalance.invoicelinenum = pettycashline.invoicelinenum", "cashrecbalance.invoicenum = invoice.invoicenum"]);
		
		}
		
		
	}
}

CashReceiptChildren::init();

?>