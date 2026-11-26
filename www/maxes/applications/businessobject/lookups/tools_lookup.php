<?php 
include ("../eat.php");

class Tools extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereClause = "";

		if (Super::hasParam("tooltype")) {
			$tooltypes = Super::getParam("tooltype"); // Ini sudah array karena pakai []

			// Pastikan nilainya array (untuk keamanan tambahan)
			if (!is_array($tooltypes)) {
				$tooltypes = [$tooltypes];
			}

			// Filter kosong & escape tiap nilai
			$escapedTooltypes = array_map(function($type) {
				return "'" . addslashes(trim($type)) . "'";
			}, array_filter($tooltypes));

			if (!empty($escapedTooltypes)) {
				$whereClause = "tooltype IN (" . implode(", ", $escapedTooltypes) . ")";
			}
			
			if(Super::hasParam("status")){
				$status = Super::getParam("status");
				$whereClause = $whereClause." AND status = '$status'";
			}
			
			if(Super::hasParam("wonum")){
				$wonum = Super::getParam("wonum");
				$whereClause = $whereClause." AND (wonum != '$wonum' OR wonum is null)";
			}
		}
		
		if(Super::hasParam("transferWOTools")){
			$wonum= Super::getParam("transferWOTools");
			$whereClause="towonum='$wonum' and status='PENDING'";
			
			Xbo::select("Tool Num.", "toolnum");
			Xbo::select("From WO#", "fromwonum");
			Xbo::select("To WO#", "towonum");
			Xbo::select("Requested Date", "requestdate", "date");
			Xbo::select("Status#", "status");
			XboSet::getXboSet("toolrequest", $whereClause);
			XboSet::displayData();
		}else{
			Xbo::select("Tool Num.", "toolnum");
			Xbo::select("Name", "name");
			Xbo::select("Description", "description");
			Xbo::select("Brand", "brand");
			Xbo::select("Location", "locnum");
			Xbo::select("Status", "status");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("WO", "wonum");
			Xbo::select("Next Calibr. Date", "nextcalibrationdate", "date");
			XboSet::getXboSet("tools", $whereClause);
			XboSet::displayData();
		}
					
		
	}
	public static function init(){
		Tools::displayData();
	}
	
}
Tools::init();
?>