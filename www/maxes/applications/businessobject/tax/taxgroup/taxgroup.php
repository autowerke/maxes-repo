<?php 
require_once ("../../eat.php");

class TaxGroupPayment extends Xbo {
	
	protected $taxgroupnum;
		
	public function __construct(){
		$this->taxgroupnum = Xbo::getString("taxgroupnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Tax Receipt", "taxgroupnum");
		Xbo::select("Description", "description");
		Xbo::select("Payment Through", "paymentvia");
		Xbo::select("Payment ID", "paymentid");
		Xbo::select("TX No", "taxnum");
		Xbo::select("Tax Type", "taxtype");
		Xbo::select("Periode", "financialperiod");
		Xbo::select("ID Bill", "idbilling");
		Xbo::select("Jumlah Total", "totalamount", "decimal");
		Xbo::select("Aktif Hingga", "activedate", "date");
		Xbo::select("Status", "status");
		Xbo::select("Status Date", "statusdate", "date");
	}
	public static function displayData(){
		TaxGroupPayment::selectData();
		XboSet::getXboSet("taxgroup");
		renderData();
	}
	public static function init(){
		$get = new TaxGroupPayment();		
		$taxgroupnum = $get->taxgroupnum;
		if(isset($taxgroupnum)) {
			XboSet::getXboSet("taxgroup", "taxgroupnum = '$taxgroupnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = TaxGroupPayment::changes();
				}else{
				$exec = TaxGroupPayment::add();
			}
		}
		
		TaxGroupPayment::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("taxgroup");
		XboSet::add();
	}

	public static function changes(){
		$get = new TaxGroupPayment();		
		$taxgroupnum = $get->taxgroupnum;		
		XboSet::getXboSet("taxgroup", "taxgroupnum='$taxgroupnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

TaxGroupPayment::init();




?>