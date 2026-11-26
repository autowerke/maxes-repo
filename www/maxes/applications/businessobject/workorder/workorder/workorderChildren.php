<?php
require_once ("../../eat.php");

class WorkorderChildren {

    public static function init(){
        XboSet::validateChildren();
        WorkorderChildren::displayData();
    }


    public static function displayData(){
        $getOwner = XboSet::getOwner();
        $tableid = Xbo::getTableID();
        if($tableid=="mxtable_wotask_child"){

            Xbo::select("Line Num.", "tasklinenum");
            Xbo::select("Description", "description");
            Xbo::select("Status", "status");
            Xbo::select("Targ. Start Date", "targstartdate", "date");
            Xbo::select("Targ. Completion Date", "targcompdate", "date");
            Xbo::select("Progress", "progress", "decimal");
            XboSet::getXboSet("wotask", $getOwner);
            XboSet::displayData();
        }

        if($tableid=="mxtable_wotaskoperation_child"){
            Xbo::select("Line Num.", "tasklinenum");
            Xbo::select("Sequence", "sequencenum");
            Xbo::select("Operation Name", "operationname");
            Xbo::select("Asset", "assetnum");
            Xbo::select("Est. Hours", "estimatedhours", "decimal");
            Xbo::select("Targ. Start Date", "actualstartdate", "date");
            Xbo::select("Targ. Completion Date", "actualfinishdate", "date");
            Xbo::select("Status", "status");
            XboSet::getXboSet("wotaskoperation", $getOwner);
            XboSet::displayData();
        }

        if($tableid=="mxtable_woplanmaterial_child"){
            Xbo::select("Line Num.", "planlinenum");
            Xbo::select("Reserve Num.", "invreservenum");
            Xbo::select("Item", "itemnum");
            Xbo::select("Description", "item.description");
            Xbo::select("Condition Type", "conditiontype");
            Xbo::select("Warehouse", "warehouse");
            Xbo::select("Quantity", "quantity", "decimal");
            Xbo::select("Order Unit", "item.issueunit");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            Xbo::select("PR", "prnum");
            Xbo::select("PR Line", "prlinenum");
            XboSet::getXboSet("woplanmaterial", $getOwner);
            XboSet::displayData(["item.itemnum = woplanmaterial.itemnum"]);
        }
        if($tableid=="mxtable_woplanservice_child"){
            Xbo::select("Line Num.", "planlinenum");
            Xbo::select("Description", "description");
            Xbo::select("Quantity", "quantity", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            XboSet::getXboSet("woplanservice", $getOwner);
            XboSet::displayData();
        }
		
		
		if($tableid=="mxtable_rcfa_child"){
			Xbo::select("Failure Code", "failurecode");
			Xbo::select("Description", "assetfailure.description");
			Xbo::select("Failure Level", "failurelevel");
			Xbo::select("Zone", "zone");
			Xbo::select("Notes", "notes");
			XboSet::getXboSet("worootcause", $getOwner);
			XboSet::displayData("worootcause.failurecode = assetfailure.failurecode "); 

		}
        if($tableid=="mxtable_matusetrans_child"){
            Xbo::select("Item", "itemnum");
            Xbo::select("Description", "item.description");
            Xbo::select("Quantity", "quantity", "decimal");
            Xbo::select("Issue Unit", "item.issueunit");
            Xbo::select("Condition Type", "conditiontype");
            Xbo::select("Warehouse", "warehouse");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            Xbo::select("MR", "mrnum");
            Xbo::select("Cost Date", "costdate", "date");
            Xbo::select("Transaction Type", "transtype");
            Xbo::select("Don't Calculate Resource?", "donotcalculateres");
            XboSet::getXboSet("matusetrans", $getOwner);
            XboSet::displayData("item.itemnum = matusetrans.itemnum");

        }

        if($tableid=="mxtable_matuseship_child"){

            Xbo::select("Item", "itemnum");
            Xbo::select("Description", "item.description");
            Xbo::select("Unit Price", "unitprice", "decimal");
            Xbo::select("Total  Price", "lineprice", "decimal");
            Xbo::select("Unit Price Discount", "unitpricediscount", "decimal");
            Xbo::select("Total Discount Price", "linepricediscount", "decimal");
            Xbo::select("Shipping Date", "shipdate", "date");
            Xbo::select("Estimated Time of Arrival", "estdatearrival", "date");
            Xbo::select("Time of Arrival", "arrivaldate", "date");
            Xbo::select("Shipping Status", "shipstatus");
            Xbo::select("Tracking Num.", "shipmentnum");
            XboSet::getXboSet("matusetrans", $getOwner. " and transtype='issue'");
            XboSet::displayData("item.itemnum = matusetrans.itemnum");

        }

        if($tableid=="mxtable_servrectrans_child"){

            Xbo::select("PO", "ponum");
            Xbo::select("Line Num.", "polinenum");
            Xbo::select("Description", "description");
            Xbo::select("Quantity", "quantity");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            Xbo::select("Entered By", "enterby");
            Xbo::select("Entered Date", "enterdate", "date");
            Xbo::select("Transaction Type", "transtype");
            XboSet::getXboSet("servrectrans", $getOwner);
            XboSet::displayData();
            
        }

        if($tableid=="mxtable_wologs_child"){

            Xbo::select("Line Num.", "loglinenum");
            Xbo::select("Description", "description");
            Xbo::select("Reported By", "reportedby");
            Xbo::select("Reported Date", "reporteddate","date");
            Xbo::select("Target Start", "wotask.targstartdate","date");
            Xbo::select("Actual Start Date", "actstartdate","date");
            Xbo::select("Target Finish", "wotask.targcompdate","date");
            Xbo::select("Actual Finish", "actcompdate","date");
            Xbo::select("Entered By", "enterby");
            Xbo::select("Entered Date", "enterdate", "date");
            Xbo::select("Progress (%)", "progress", "decimal");
            XboSet::getXboSet("wologs", $getOwner);
            XboSet::displayData(["wologs.wonum = wotask.wonum and wologs.tasklinenum = wotask.tasklinenum"]);

        }
        if($tableid=="mxtable_woterm_child"){

            Xbo::select("Line Num.", "termlinenum");
            Xbo::select("Description", "description");
            Xbo::select("is meet condition?", "meetcondition");
            XboSet::getXboSet("woterm", $getOwner);
            XboSet::displayData();
        }
        if($tableid=="mxtable_itemproduction_child"){

            Xbo::select("Application", "ownertable");
            Xbo::select("Item Number", "keynum");
            Xbo::select("Quantity", "invtrans.quantity");
            Xbo::select("Warehouse", "invtrans.warehouse");
            Xbo::select("Rack/Bin", "invtrans.racknum");
            Xbo::select("Created By", "enterdate", "date");
            Xbo::select("Entered By", "enterby");
            XboSet::getXboSet("woproduction", $getOwner. " and ownertable='item'");
            XboSet::displayData("woproduction.wonum=invtrans.wonum");

        }
        if($tableid=="mxtable_assetproduction_child"){

            Xbo::select("Application", "ownertable");
            Xbo::select("Asset Number", "keynum");
            Xbo::select("Enter Date", "enterdate", "date");
            Xbo::select("Created By", "enterby");
            XboSet::getXboSet("woproduction", $getOwner. " and ownertable='assets'");
            XboSet::displayData();

        }
        if($tableid=="mxtable_wosubs_child"){

            Xbo::select("Sub WO", "subwonum");
            Xbo::select("Description", "workorder.description");
            Xbo::select("Status", "workorder.status");
            Xbo::select("Work Type", "workorder.worktype");
            Xbo::select("Work Classification", "workorder.workclass");
            Xbo::select("Reported Date", "workorder.reportdate", "date");
            Xbo::select("Target Start Date", "workorder.targstartdate", "date");
            Xbo::select("Target Completion Date", "workorder.targcompdate", "date");
            Xbo::select("Actual Cost", "workorder.actualcost", "decimal");
            Xbo::select("Roll Up Cost", "workorder.rollupcost", "decimal");
            XboSet::getXboSet("wosubs", $getOwner);
            XboSet::displayData(["wosubs.subwonum = workorder.wonum"]);

        }
        if($tableid=="mxtable_woplanlabor_child" || $tableid=="mxtable_labortrans_child" ){

            if($tableid=="mxtable_woplanlabor_child" ){
                $xbosetname = "woplanlabor";
                $linecost = "prelinecost";
                $xbosetjoin = ["woplanlabor.wonum = wotask.wonum and woplanlabor.tasklinenum = wotask.tasklinenum"];
            }elseif($tableid=="mxtable_labortrans_child" ){
                $xbosetname = "labortrans";
                $linecost = "linecost";
                $xbosetjoin = ["labortrans.wonum = wotask.wonum and labortrans.tasklinenum = wotask.tasklinenum"];
            }
          
            Xbo::select("Team", "teamnum");
            Xbo::select("Staff", "personid");
            Xbo::select("Start Date", "startdate", "date");
            Xbo::select("Finish Date", "enddate", "date");
            Xbo::select("Work Hours", "workhours", "decimal");
            Xbo::select("Regular Hours", "reghours", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", $linecost, "decimal");
            Xbo::select("Task", "tasklinenum");
            Xbo::select("Task Description", "wotask.description");
            XboSet::getXboSet($xbosetname, $getOwner );
            XboSet::displayData($xbosetjoin);

        }
        if($tableid == "mxtable_labortransline_child"){

            Xbo::select("Person ID", "personid");
            Xbo::select("Team", "teamnum");
            Xbo::select("Start Date", "startdate", "date");
            Xbo::select("End Date", "enddate", "date");
            Xbo::select("Regular Hours", "reghours", "decimal");
            Xbo::select("Extended Hours", "extendedhours", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Overtime Cost", "overtimecost", "decimal");
            Xbo::select("Total Overtime Hours", "totalovertimecost", "decimal");
            Xbo::select("Regular Cost", "prelinecost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            Xbo::select("Apply Overtime?", "applyovertime");
            XboSet::getXboSet("labortransline", $getOwner );
            XboSet::displayData();

        }
        if($tableid=="mxtable_woplantool_child" || $tableid=="mxtable_tooltrans_child"){

            if($tableid=="mxtable_woplantool_child" ){
                $xbosetname = "woplantool";
				$locnum = "locnum";
            }else if($tableid=="mxtable_tooltrans_child" ){
                    $xbosetname = "tooltrans";
					$locnum = "fromlocnum";
            }

            Xbo::select("Line Num.", "planlinenum");
            Xbo::select("Task Line", "tasklinenum");
            Xbo::select("Task Description", "description");
            Xbo::select("Tools", "toolnum");
            Xbo::select("Description", "description");
            Xbo::select("Quantity", "quantity", "decimal");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Taken From Location", $locnum);
            XboSet::getXboSet($xbosetname, $getOwner);
            XboSet::displayData();

        }
    }
}

WorkorderChildren::init();

?>