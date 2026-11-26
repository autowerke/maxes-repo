<?php 
require_once ("../../eat.php");

class WorkTemplatesChildren {

	public static function init(){
		XboSet::validateChildren();	
		WorkTemplatesChildren::displayColumns();
	}
	
	
	public static function displayColumns(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_wttask_child"){
			Xbo::select("Line Num.", "tasklinenum");
			Xbo::select("Description", "description");
			XboSet::getXboSet("wttask", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_wtplanmaterial_child"){
			Xbo::select("Line Num.", "planlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Quantity", "quantity");
			Xbo::select("Order Unit", "item.issueunit");				
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");		
			XboSet::getXboSet("wtplanmaterial", $getOwner);
			XboSet::displayData(["item.itemnum = wtplanmaterial.itemnum"]);
		}


		 if($tableid=="mxtable_wtplanservice_child"){

            Xbo::select("Line Num.", "planlinenum");
            Xbo::select("Description", "description");
            Xbo::select("Quantity", "quantity", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            XboSet::getXboSet("wtplanservice", $getOwner);
            XboSet::displayData();

        }

		 if($tableid=="mxtable_wtplanlabor_child"){
			 Xbo::select("Team", "teamnum");
            Xbo::select("Staff", "personid");
            Xbo::select("Start Date", "startdate", "date");
            Xbo::select("Finish Date", "enddate", "date");
            Xbo::select("Work Hours", "workhours", "decimal");
            Xbo::select("Regular Hours", "reghours", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "prelinecost", "decimal");
            Xbo::select("Task", "tasklinenum");
            XboSet::getXboSet("wtplanlabor", $getOwner );
            XboSet::displayData();
		 }

		  if($tableid=="mxtable_wtplantool_child" ){
			 Xbo::select("Line Num.", "planlinenum");
            Xbo::select("Task Line", "tasklinenum");
            Xbo::select("Task Description", "description");
            Xbo::select("Tools", "toolnum");
            Xbo::select("Description", "description");
            Xbo::select("Quantity", "quantity", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Taken From Location", "locnum");
            XboSet::getXboSet("wtplantool", $getOwner);
            XboSet::displayData();
		  }
		
	}
}

WorkTemplatesChildren::init();

?>