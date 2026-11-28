<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
class Action  {
	
	public static function Run(){
        
        if(Super::hasParam("getFilteredDocumentTableData")) Dashboard::getFilteredDocumentTableData();
        if(Super::hasParam("getCategoryAndStatusDistribution")) Dashboard::getCategoryAndStatusDistribution();
        if(Super::hasParam("getFilteredDocumentTableData")) Dashboard::getFilteredDocumentTableData();
        if(Super::hasParam("getDocumentTypeRecordsFiltered")) Dashboard::getDocumentTypeRecordsFiltered();
        if(Super::hasParam("getFilteredKnowledgeGraph")) Dashboard::getFilteredKnowledgeGraph();
        if(Super::hasParam("getManualDropdownList")) Dashboard::getManualDropdownList();
        if(Super::hasParam("getKnowledgeGraphData")) Dashboard::getKnowledgeGraphData();
        if(Super::hasParam("getKpiSummary")) Dashboard::getKpiSummary();
        if(Super::hasParam("getDocumentTypeRecords")) Dashboard::getDocumentTypeRecords(); 
    }
}

Action::Run();
?>
