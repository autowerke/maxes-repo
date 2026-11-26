<?php 
/*author : Tommy Nitidiryo Sumarsono (c) 2015
*/

require_once ("../../eat.php");

class MxsUsersChildren {

	public static function init(){
		XboSet::validateChildren();	
		MxsUsersChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		
		Xbo::select("Group Name", "groupnum");
		Xbo::select("Description", "mxsgroups.description");
		XboSet::getXboSet("mxsusergroup", $getOwner);
		XboSet::displayData(["mxsusergroup.groupnum = mxsgroups.groupnum"]);
	}
}

MxsUsersChildren::init();

?>