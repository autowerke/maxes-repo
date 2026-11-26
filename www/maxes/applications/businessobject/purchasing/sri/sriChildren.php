<?php
require_once ("../../eat.php");

class SRIChildren {

    public static function init(){
        XboSet::validateChildren();
        SRIChildren::displayData();
    }


    public static function displayData(){
        $getOwner = XboSet::getOwner();
        $tableid = Xbo::getTableID();

        if($tableid == "mxtable_sriline_child"){
            Xbo::select("Line Num.", "srilinenum"); // Nomor Baris SRI
            Xbo::select("PO Line Number", "polinenum");
            Xbo::select("Description", "description");			
			Xbo::select("Order Qty.", "orderqty", "decimal");
			Xbo::select("Order Unit", "orderunit");
            Xbo::select("Unit Cost", "unitcost", "decimal");
            Xbo::select("Tax Cost", "taxcost", "decimal");
            Xbo::select("Line Cost", "linecost", "decimal");
            Xbo::select("Work Order", "wonum");
            XboSet::getXboSet("sriline", $getOwner);
            XboSet::displayData();
        }

        if($tableid == "mxtable_sriinspection_child"){
            Xbo::select("Line Num.", "sriinslinenum");
			 Xbo::select("PO", "ponum");
			 Xbo::select("PO Line", "polinenum");
			 Xbo::select("WO", "wonum");
			 Xbo::select("Vendor Staff Line", "srivendorlinenum");
			  Xbo::select("Vendor Staff Name", "srivendor.staffname");
            Xbo::select("Inspection Date", "inspectiondate", "date");
            Xbo::select("Progress(%)", "progress", "decimal");
            Xbo::select("Status", "status");
            Xbo::select("Comm. Notes", "communicationnotes");
			Xbo::select("Quality. Notes", "qualitynotes");
			Xbo::select("Delay Notes", "delayreason");
			Xbo::select("Order Qty", "orderqty", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Status", "status");
            XboSet::getXboSet("sriinspection", $getOwner);
            XboSet::displayData(["sriinspection.srinum = srivendor.srinum and srivendor.srivendorlinenum = sriinspection.srivendorlinenum"]);
        }

        if($tableid == "mxtable_srivendor_child"){
            Xbo::select("Line Num.", "srivendorlinenum");
			 Xbo::select("Staff Name", "staffname");
            Xbo::select("Staff Position", "staffposition");
			Xbo::select("Job Description", "jobdesc");
			Xbo::select("Phone Number", "phonenum");
			Xbo::select("email", "email");
            XboSet::getXboSet("srivendor", $getOwner);
            XboSet::displayData();
        }

        if($tableid == "mxtable_sriinspector_child"){
            Xbo::select("Inspector ID", "sriinspectorid");
            Xbo::select("Inspector", "inspector");
			 Xbo::select("Inspector Name", "employees.fullname");
            Xbo::select("Role", "role");
            Xbo::select("Approval", "isapprovalby");
            Xbo::select("Acknowledge", "isacknowledgeby");
            XboSet::getXboSet("sriinspector", $getOwner);
            XboSet::displayData(["sriinspector.inspector = employees.personid"]);
        }

         if($tableid == "mxtable_sridocument_child"){
            Xbo::select("Document ID", "sridocumentid");
            Xbo::select("Title", "title");
            Xbo::select("Abstract", "abstract");
            Xbo::select("Issue Date", "issuedate", "date");
            Xbo::select("Approval Date", "approvaldate", "date");
            XboSet::getXboSet("sridocument", $getOwner);
            XboSet::displayData();
        }

		 if($tableid == "mxtable_sriscopechange_child"){
            Xbo::select("For Inspection Line", "sriinslinenum");
            Xbo::select("Title", "scopetitle");
			Xbo::select("Scope Description", "scopedescription");
            Xbo::select("Is Approved?", "isapproved");
            Xbo::select("Approved By", "approvedby");
            Xbo::select("Approval Date", "approvaldate", "date");
            XboSet::getXboSet("sriscopechange", $getOwner);
            XboSet::displayData();
        }
     
    }
}

SRIChildren::init();