<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
class Action  {
	
	public static function Run(){
		
		if(Super::hasParam("markNotifRead")) Xbo::markNotifRead();
		if(Super::hasParam("markAllNotifRead")) Xbo::markAllNotifRead();
		if(Super::hasParam("reliability_bot_chat")) ReliabilityBotHandler::reliability_bot_chat();
		if(Super::hasParam("mtbfmttrAI")) Charts:: mtbfmttrAI();
		if(isset($_POST["getMtbfMttr"]) ) Charts::getMtbfMttrAnalysis(); 
		if(Super::hasParam("unplannedDowntimeAI")) Charts:: unplannedDowntimeAI();
		if(isset($_POST["getMtbfMttr"]) ) Charts::getMtbfMttrAnalysis(); 
		if(isset($_POST["getUnplannedDowntime"]) ) Charts::getUnplannedDowntime(); 
		if(isset($_POST["woPerformance"]) ) Charts::woPerformance(); 
		if(isset($_POST["assetPerformanceSummary"]) ) Charts::assetPerformanceSummary();
		if(isset($_POST["loadNotificationData"]) ) Charts::loadNotificationData();
		if(isset($_POST["productivity_chart"]) ) Charts::productivity_chart();
		if(isset($_POST["totaldowntime_detail_chart"]) ) Charts::totaldowntime_detail_chart();
		if(isset($_POST["totaldowntime_chart"]) ) Charts::totaldowntime_chart();
		if(isset($_POST["prpo_analytics_year"]) ) Charts::Spending_category_overtime_year();
		if(isset($_POST["Spending_category_overtime_year"])) Charts::Spending_category_overtime_year();
		if(isset($_POST["budget_allocation_breakdown_year"])) Charts::budget_allocation_breakdown_year();
		if(isset($_POST["budget_actual_spending_year"])) Charts::budget_actual_spending_year();
		if(isset($_POST["chart_lead_time_item"])) Charts::lead_time_item();
		if(isset($_POST["lead_time_year"])) Charts::lead_time_year();
		if(isset($_POST["loadGoodReceiptFunnelData"])) Charts::loadGoodReceiptFunnelData();
		if(isset($_POST["loadRfsPerMonthInspectionTypeData"])) Charts::loadRfsPerMonthInspectionTypeData();
		if(isset($_POST["loadABCItem"])) Charts::loadABCItem();
		if(isset($_POST["ABCAnalysis"])) Charts::ABCAnalysis(); 
		if(isset($_POST["chartType"])) Charts::chartType(); 
		if(isset($_POST["assetYtdCostChart"])) Charts::assetYtdCostChart(); 
		if(isset($_POST["assetByModelOrManufacturerChart"])) Charts::assetByModelOrManufacturerChart(); 
		if(isset($_POST["assetPerLocationChart"])) Charts::assetPerLocationChart(); 
		if(isset($_POST["assetTypeChart"])) Charts::assetTypeChart();
		if(isset($_POST["chart_moving_average_forecasting"])) Charts::chart_moving_average_forecasting();
		if(isset($_POST["chart_forecasting"])) Charts::chart_forecasting();
		if(isset($_POST["chart_inventoryValue"])) Charts::chart_inventoryValue();
		if(isset($_POST["chart_slowMoving"])) Charts::chart_slowMoving(); 
		if(isset($_POST["chart_monthlyaggregate"])) Charts::chart_monthlyaggregate();
		if(isset($_POST["chart_toplevel_safetystock"])) Charts::chart_toplevel_safetystock();
		if(isset($_POST["chart_itemgroup"])) Charts::chart_itemgroup();
		if(isset($_POST["salesmancomm"])) Charts::SalesmanCommission();
		if(isset($_POST["pricecompitem"])) Charts::PriceComparison($_POST["pricecompitem"]);
		if(isset($_POST["bars_pricecomp"])) Charts::PriceComparison();
		if(isset($_POST["cashinout"])) Charts::CashInOut();
		if(isset($_POST["getMoveMarker"])) Charts::GetMovedMarker();
		if(isset($_POST["callmarker"])) Charts::CallSalesman($_POST["callmarker"]);
		if(isset($_POST["bars_customerhpp"])) Charts::CustomerHPP();
		if(isset($_POST["prefereditems"])) Charts::PreferedItems();
		if(isset($_POST["salesman"])) Charts::Salesman();
		if(isset($_POST["picksalesman"])) Charts::Salesman($_POST["picksalesman"]);
		if(isset($_POST["getlonglat"])) Charts::GetLongLat();
		if(isset($_POST["userlonglat"])) Charts::UserLongLat();
		if(isset($_POST["getMBToken"])) Charts::getMBToken();
		
	}	
	
 }

Action::Run();

?>