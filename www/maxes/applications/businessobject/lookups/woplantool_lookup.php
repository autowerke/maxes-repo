<?php 
include ("../eat.php");

class ToolTrans extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$wonum = Super::getParam("wonum");
		$flag = Super::getParam("flag");
		
		$wr = [];
		if(!empty($wonum))  array_push($wr, " wonum = '$wonum'");
		if(!empty($flag))  array_push($wr, " planlinenum not in (select planlinenum from tooltrans where wonum='$wonum')");
		
		if(count($wr)>0){
			$where = implode(" and ", $wr);
		}else  $where =null;
		
		Xbo::select("Line Num.", "planlinenum");
		Xbo::select("For Task?", "tasklinenum");
		Xbo::select("Tool Num.", "toolnum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		XboSet::getXboSet("woplantool", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		ToolTrans::displayData();
	}
	
}

ToolTrans::init();




?>