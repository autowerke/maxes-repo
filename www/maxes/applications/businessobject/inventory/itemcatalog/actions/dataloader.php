<?php
require_once ("../../../eat.php");
Class DataLoader {
	public static function UploadLoader(){
		
	}
	public static function DownloadLoader(){
		error_reporting(0);
		$value = $_POST["downloadloader"];
		$isarr = is_array($value);
		if($isarr == 1){
			$countloadfiles = count($value);
			for($i=0; $i<$countloadfiles; $i++){
				$val = $value[$i];
				$get = Super::getExcelPath($val);
				$fullPath = $get->data;
				$filename = $get->filename;
				$appurl = $get->appurl;
				if (file_exists($fullPath)) {
					$fullhost =  ContextPath::getFullHostName()."/applications/importdata/".$appurl.$filename;
					Set::throws(["msg"=>"downloaded", "errormsg"=>"success", "reviewmsg"=>$fullhost]);
					exit;
				}else{
					Set::throws(["msg"=>"none", "errormsg"=>"File does not exist in $fullPath", "reviewmsg"=>""]);
				}
			}
		}
		
	}
	

	public static function Load() {
		try {
			$value = $_POST["loaddata"];
			if (!is_array($value) || empty($value)) return;

			$msg = null;
			$errormsg = null;
			$reviewmsg = null;

			foreach ($value as $val) {
			
				$val['real_filepath'] = Super::resolveImportFilePath($val);
				
				$actionname = $val["actionname"] ?? null;

				if ($actionname !== null || !empty($actionname)) {
					// --- LOGIKA BERDASARKAN ACTION ---
					switch ($actionname) {
						case "addtowarehouse":
							$get = Super::getExcelValue($val);
							$data = $get->data;
							$successCount = 0;

							foreach ($data as $dt) {
								$ss = new stdClass();
								$ss->itemnum = $dt->itemnum;
								$ss->issuetype = $dt->issuetype;
								$ss->warehouse = $dt->warehouse;
								$ss->defrack = $dt->racknum;
								$ss->conditiontype = $dt->conditiontype;
								$ss->curbal = $dt->quantity;
								$ss->unitcost = $dt->unitcost;
								$ss->orgcode = $dt->orgcode;
								$ss->sitecode = $dt->sitecode;

								// Eksekusi Business Logic
								$status = XboSet::AddToInventory(null, $ss, null, false);

								if ($status == "conditiondoesnotexist") {
									$msg = "ERROR";
									$errormsg = "Item does not have condition";
									$reviewmsg = "Item: <b>{$dt->itemnum}</b> in Wh: <b>{$dt->warehouse}</b> doesn't support Condition Type. Check Excel Row.";
									break 2; // Keluar dari loop data DAN loop file
								} 
								elseif ($status == "hasexist") {
									// Lewati jika sudah ada, tidak perlu error
									continue;
								} 
								elseif ($status == "warehousenull") {
									$msg = "ERROR";
									$errormsg = "Warehouse is not set!";
									$reviewmsg = "Item: <b>{$dt->itemnum}</b> warehouse is required!";
									break 2;
								} 
								else {
									$successCount++;
								}
							}
							$msg = "SUCCESS";
							$reviewmsg = "Successfully processed $successCount items to warehouse.";
							break;

						default:
							$get = Super::importxls($val);
							$msg = $get->msg;
							$errormsg = $get->errormsg;
							$reviewmsg = $get->reviewmsg;
					}
				} else {
					// --- IMPORT STANDAR (Tanpa Action) ---
					$get = Super::importxls($val);
					$msg = $get->msg;
					$errormsg = $get->errormsg;
					$reviewmsg = $get->reviewmsg;
				}

				if ($msg == "ERROR") break;
			}

			if ($msg != null) {
				Set::throws(["msg" => $msg, "errormsg" => $errormsg, "reviewmsg" => $reviewmsg]);
			}

		} catch (Exception $e) {
			Set::throws(["msg" => "ERROR", "errormsg" => $e->getMessage(), "reviewmsg" => null]);
		}
	}
} 


?>