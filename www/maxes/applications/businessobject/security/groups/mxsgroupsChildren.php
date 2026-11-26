<?php 
require_once ("../../eat.php");

class ApplicationActions extends MxsGroupChildren{
	public static function addActions(){
		global $ownerCols;
		global $childCols;
		global $childTable;
		if($childTable=="mxsgroupline"){
			$_GET['package'] = $childCols;
			$appname = Xbo::getString("appname");
			$_GET['package'] = $ownerCols;
			$groupnum =  Xbo::getString("groupnum");
			$set = new Set();
			$mxsactionSet = $set->MboSetRemote("mxsaction")->setWhere("appname = '$appname'")->MboRemote("actionname")->MboRemote("appname")->getMboSet();
			$count = $mxsactionSet->counts();
			for($i=0; $i<$count; $i++){
				$mxsaction = $mxsactionSet->getMbo();
				$actionname=$mxsaction->getString('actionname');
				$set2= new Set();
				$set2->MboSetRemote("mxsgroupaction")->setValue("groupnum", $groupnum)->setValue("appname", $appname)->setValue("actionname", $actionname)->add();
			}
		}
	}
}

class MxsGroupChildren {

	public static function init(){
		XboSet::validateChildren();	
		MxsGroupChildren::displayData();
		ApplicationActions::addActions();
	}
	public static function displayApplicationAction($param){
			Xbo::select("Action", "actionname");
			Xbo::select("Description", "mxsaction.appname.description");
			Xbo::select("Application", "appname");
			XboSet::getXboSet("mxsgroupaction", $param);
			XboSet::displayData();
	}
	
	public static function displayData(){
		
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_mxsgroupline_child"){
			Xbo::select("Application", "appname");
			Xbo::select("Description", "mxsapp.description");
			Xbo::select("Can Read?", "canread");
			Xbo::select("Can Save?", "cansave");
			Xbo::select("Home Page?", "homepage");
			XboSet::getXboSet("mxsgroupline", $getOwner);
			XboSet::displayData("mxsgroupline.appname = mxsapp.appname");
		}
		
		if($tableid=="mxtable_mxsusergroup_child"){
			Xbo::select("User ID", "userid");
			Xbo::select("Staff", "mxsusers.personid");
			Xbo::select("Full Name", "employees.fullname");
			Xbo::select("Title", "employees.title");
			Xbo::select("Email", "employees.primaryemail");
			XboSet::getXboSet("mxsusergroup", $getOwner);
			XboSet::displayData(["mxsusers.userid = mxsusergroup.userid", "employees.personid = mxsusers.personid"]);
		}
		if($tableid=="mxtable_mxsgroupsite_child"){
			Xbo::select("Site", "sitecode");
			Xbo::select("Description", "site.description");
			Xbo::select("Site Contact", "site.sitecontact");
			Xbo::select("Company", "site.orgcode");
			XboSet::getXboSet("mxsgroupsite", $getOwner);
			XboSet::displayData(["site.sitecode = mxsgroupsite.sitecode"]);
		}
		
		if($tableid=="mxtable_mxsgroupcolres_child"){
			Xbo::select("Attribute Name", "attributename");
			Xbo::select("Larangan-larangan", "restricttype");
			XboSet::getXboSet("mxsgroupcolres", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_mxslayoutres_child"){
			Xbo::select("Application", "appname");
			Xbo::select("ID Name", "idname");
			Xbo::select("Larangan-larangan", "restricttype");
			XboSet::getXboSet("mxslayoutres", $getOwner);
			XboSet::displayData();
		}
		$linktable = Super::getParam("linktable");
		if($tableid=="mxtable_groupaction_child"){
			MxsGroupChildren :: displayApplicationAction($getOwner);
		}
	
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
		
			$tableName = Xbo::getLinkTableName($linktable);
			if($tableName=="mxtable_groupaction_child") {
				MxsGroupChildren :: displayApplicationAction($where);
			}
		}
	}
}

MxsGroupChildren::init();

?>