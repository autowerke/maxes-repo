<?php
require_once ("../../../eat.php");
$common_receiving= __DIR__ . '/CommonActions.php';
require_once ($common_receiving);
Class ActionSet {
	public static function ChangeExpDate(){
		CommonActions::ChangeExpDate();
	}
	public static function ChangeReceivedDate(){
		CommonActions::ChangeReceivedDate();
	}
	public static function ReceiptFIFOLIFO($fl){
		CommonActions::ReceiptFIFOLIFO($fl);
	}
	public static function ReturnTransaction($value=null)
	{
		CommonActions::ReturnTransaction($value);
	}
	public static function setPOServrecReceiptStatus($ponum, $polinenum)

	{
		CommonActions::setPOServrecReceiptStatus($ponum, $polinenum);
	}
	public static function setPOMatrecReceiptStatus($ponum, $polinenum)

	{
		CommonActions::setPOMatrecReceiptStatus($ponum, $polinenum);
	}
	public static function setPOReceiptStatus($ponum, $polinenum, $linetype)

	{
		CommonActions::setPOReceiptStatus($ponum, $polinenum, $linetype);
	}
	public static function ReceiptTransaction($value=null)

	{
		CommonActions::ReceiptTransaction($value);
	}
	
	public static function PORecTrans(){
		
		$transtype = $_POST["transtype"];
		$msg = null;
		$countlines = count($_POST['porectrans']);
		if($transtype == "poline"){
			for($i=0;$i<$countlines; $i++){
				$value = $_POST['porectrans'][$i];
				$process = ActionSet::ReceiptTransaction($value);
				if($process=="ReceiptQtyCannotLessThanZero"){
					$msg = "ReceiptQtyCannotLessThanZero";
					break;
				}
				if($process=="ReceiptQtyIsGreaterThanOrderQty"){
					$msg = "ReceiptQtyIsGreaterThanOrderQty";
					break;
				}
			}
			
			if($msg!=null){
				echo $msg;
			}
		}
		if($transtype == "rectrans" || $transtype == "matrectrans" || $transtype == "servrectrans"){
			
			for($i=0;$i<count($_POST['porectrans']); $i++){
				$value = $_POST['porectrans'][$i];
				$process = ActionSet::ReturnTransaction($value);
				if($process=="ReturnQtyIsLesserThanZero"){
					$msg = "ReturnQtyIsLesserThanZero";
					break;
				}else{
					if($process=="RackDoesNotHaveBalance"){
						$msg = "RackDoesNotHaveBalance";
						break;
					}else{
						if($process=="ReturnQtyIsGreaterThanReceiptQty"){
							$msg = "ReturnQtyIsGreaterThanReceiptQty";
							break;
						}else{
							if($process == "notenoughfifoqty"){
								$msg = "notenoughfifoqty";
								break;
							}
						}
					}
				}
			}
			if($msg!=null){
				echo $msg;
			}
		}
	}
}

Class ValidateTables{
	public static function tbce(){
		$table = $_GET['tbce'];
		if ($table == "mxtable_vendorpolines_child" || $table == "mxtable_vendormatrectrans_child" || $table == "mxtable_vendorservrectrans_child" || $table == "mxtable_receipt_child" || $table == "mxtable_matsaletrans_child" || $table == "mxtable_servsaletrans_child" || $table == "mxtable_soshipment_child" || $table == "mxtable_sotracking_child") 
			echo "histable";
		if ($table == "mxtable_inventory_child" || $table == "mxtable_invvendor_child") echo "relatedtable";
	}
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		$appname = $dl[0];
		$vtable = $dl[2];
		
		if ($vtable == "matrectrans") {
			Set::throws(["msg" => "relateddata", "rltbl" => "poline"]);
		}
		if ($vtable == "servrectrans") {
			Set::throws(["msg" => "relateddata", "rltbl" => "poline"]);
		}
	}	
}

?>