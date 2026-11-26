<?php 
require_once ("../../eat.php");

class PurchContract extends Xbo {
	
	public $contractnum;	
	public function __construct(){
		$this->contractnum = Xbo::getString("contractnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Contract", "contractnum");
		Xbo::select("Description", "description");
		Xbo::select("RFQ", "rfqnum");
		Xbo::select("Requested Date", "contracttype");	
		Xbo::select("Vendor", "vendorcode");			
		Xbo::select("Status", "status");	
		Xbo::select("Start Date", "startdate", "date");
		Xbo::select("End Date", "enddate", "date");
		Xbo::select("Total Cost", "totalcost", "decimal");
		Xbo::select("Amount Spent", "amountspent", "decimal");
		Xbo::select("Currency Code", "currencycode");
		Xbo::select("Revision", "revisionnum");
		Xbo::select("Status", "status");
	}
	public static function displayData(){
		PurchContract::selectData();
		XboSet::getXboSet("purchcontract");
		renderData();
	}
	public static function init(){
		$get = new PurchContract();		
		$contractnum = $get->contractnum;
		if(isset($contractnum)) {
			XboSet::getXboSet("purchcontract", "contractnum = '$contractnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = PurchContract::changes();
				}else{
				$exec = PurchContract::add();
				
			}
		}
		
		PurchContract::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("purchcontract");
		XboSet::add();
	}

	public static function changes(){
		$get = new PurchContract();		
		$contractnum = $get->contractnum;		
		XboSet::getXboSet("purchcontract", "contractnum='$contractnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

PurchContract::init();




?>