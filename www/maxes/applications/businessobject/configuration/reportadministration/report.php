<?php 
require_once ("../../eat.php");

class ReportAdministration extends Xbo {
	
	protected $reportname;
		
	public function __construct(){
		$this->reportname = Xbo::getString("reportname");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Report Name", "reportname");
		Xbo::select("Description", "description");
		Xbo::select("Application", "appname");
		Xbo::select("Imported Date", "importeddate");
		Xbo::select("Imported By", "importedby");
	}
	public static function displayData(){
		ReportAdministration::selectData();
		XboSet::getXboSet("report");
		renderData();	
	}
	public static function init(){
		$get = new ReportAdministration();		
		$reportname = $get->reportname;
	
		if(isset($reportname)) {
			XboSet::getXboSet("report", "reportname = '$reportname'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = ReportAdministration::changes();
				}else{
				$exec = ReportAdministration::add();				
			}
		}
		
		ReportAdministration::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("report");
		XboSet::add();
	}

	public static function changes(){
		$get = new ReportAdministration();		
		$reportname =	$get->reportname;		
		XboSet::getXboSet("report", "reportname='$reportname'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ReportAdministration::init();




?>