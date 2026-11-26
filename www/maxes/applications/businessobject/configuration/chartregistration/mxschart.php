<?php 
require_once ("../../eat.php");

class mxschartistration extends Xbo {
	
	protected $chartnum;
		
	public function __construct(){
		$this->chartnum = Xbo::getString("chartnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Chart ID", "chartnum");
		Xbo::select("Description", "description");
		Xbo::select("Type", "type");
		Xbo::select("Select", "stselect");
		Xbo::select("From", "stfrom");
		Xbo::select("Where", "stwhere");
		Xbo::select("Group By", "stgroupby");
	}
	public static function displayData(){
		mxschartistration::selectData();
		XboSet::getXboSet("mxschart");
		renderData();	
	}
	public static function init(){
		error_reporting(0);
		$get = new mxschartistration();		
		$chartnum = $get->chartnum;
		
		if(isset($chartnum)) {
		
			XboSet::getXboSet("mxschart", "chartnum = '$chartnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				
				$exec = mxschartistration::changes();
				$sql = createStatement::createSt();
					if($sql!="sqlsuccess"){
						echo "<font color=red><b>ERROR:</b> $sql</font>|";
					}	
				}else{
				$exec = mxschartistration::add();	
				$sql = createStatement::createSt();
					if($sql!="sqlsuccess"){
						echo "<font color=red><b>ERROR:</b> $sql</font>|";
					}		
			}
		}
			
		mxschartistration::displayData();
		
				
	}
	

	public static function add(){
		XboSet::getXboSet("mxschart");
		XboSet::add();
	}

	public static function changes(){
		$get = new mxschartistration();		
		$chartnum =	$get->chartnum;		
		XboSet::getXboSet("mxschart", "chartnum='$chartnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

class createStatement extends mxschartistration{
	public static function createSt(){
		error_reporting(0);
		
		$chartnum = Xbo::getString("chartnum");
		$select = Xbo::getString("stselect");
		$from = Xbo::getString("stfrom");
		$where = Xbo::getString("stwhere");
		$groupby = Xbo::getString("stgroupby");
		$revaxis=Xbo::getString("revaxis");
		
		$msg = "";
		$set=new Set();
		if(!empty($select)){
			$stmt =  $select." ".$from." ".$where." ".$groupby;
			$result = $set->getMboSet("",$stmt);
			$numfields = $result->countFields();
			$colArr =array();
			//echo $stmt;			
			for($i=0; $i<$numfields; $i++){
				$meta = $set->fetchFields();
				$colname = $meta->name;
				array_push($colArr, $colname);
			}
			$numrows = $result->counts();
			$dtchart = array();
			$lblchart = array();
			for($i=0; $i<$numrows; $i++){
				$data2=$result->getMbo();
				for($j=0; $j<count($colArr); $j++){
					if($j%2==0){
						array_push($dtchart, $data2->getString($colArr[$j]));						
					}else{
						array_push($lblchart, $data2->getString($colArr[$j]));	
					}
				}
			}
			$msg="sqlsuccess";
		}else{
			$msg=$set->getErrorMessage();
		}
			
		
		if(!empty($select)){
				
			$lblchartSt = implode(",", $lblchart);
			$dtchartSt = implode(",", $dtchart);
			$xaxis=addslashes($lblchartSt);
			$yaxis=$dtchartSt;
			
			if($revaxis=="Y"){
				$xaxis=$dtchartSt;
				$yaxis=addslashes($lblchartSt);
			}
			
			$st = "update mxschart set label='".$xaxis."', data='".$yaxis."' where chartnum='$chartnum'";
			
			DB::exec($st);
		}
		
		return $msg;
	}
}

mxschartistration::init();




?>