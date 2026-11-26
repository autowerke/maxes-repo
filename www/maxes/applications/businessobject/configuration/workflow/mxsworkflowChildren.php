<?php 
require_once ("../../eat.php");

class mxsworkflowChildren {

	public static function init(){
		XboSet::validateChildren();	
		mxsworkflowChildren::displayData();
	}
	public static function displayLinkTable1($param){
			
			Xbo::select("To Node", "tonodeline");
			Xbo::select("Title", "mxswfnode.title");			
			Xbo::select("Instruction", "instruction");			
			Xbo::select("Action", "action");
			Xbo::select("Is Negative?", "isnegative");
			XboSet::getXboSet("mxswfaction", $param);
			XboSet::displayData(["mxswfnode.wfname = mxswfaction.wfname and mxswfnode.nodeline = mxswfaction.tonodeline "]);
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();	
		$tableid = Xbo::getTableID();
				
		if($tableid=="mxtable_mxswfnode_child"){
			Xbo::select("Node", "nodeline");
			Xbo::select("Title", "title");
			Xbo::select("Description", "description");
			Xbo::select("Node Type", "nodetype");
			Xbo::select("Expression", "whereclause");
			XboSet::getXboSet("mxswfnode", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_mxswfaction_child"){
			mxsworkflowChildren :: displayLinkTable1($getOwner);
		}
		$linktable = Super::getParam("linktable");
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
			if($tableName=="mxtable_mxswfaction_child") {
				mxsworkflowChildren :: displayLinkTable1($where);
			}
		}
	}
}

mxsworkflowChildren::init();

?>