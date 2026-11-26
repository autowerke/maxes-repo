<?php
require_once ("../../../eat.php");
Class DataLoader {
	
	public static function Load() {
		
		try {
			$value = $_POST["loaddata"];
			if (!is_array($value) || empty($value)) return;

			$finalMsg = "success";
			$errormsg = null;
			$reviewmsg = null;

			foreach ($value as $val) {
				// 1. Logic Cari File (Gunakan Helper agar konsisten)
				$val['real_filepath'] = Super::resolveImportFilePath($val);

				$actionname = $val["actionname"] ?? null;

				if ($actionname !== null) {
					// 2. Ambil data Excel SEKALI saja untuk semua action
					$excel = Super::getExcelValue($val);
					$data = $excel->data;
					$totalRows = count($data);
					$successCount = 0;

					// 3. Gunakan Switch agar rapi
					switch ($actionname) {
						case "physcount":
							foreach ($data as $dt) {
								// Mapping data langsung ke object
								$status = ActionSet::AdjustPhysicalCountXL($dt); // Pastikan function ini terima $dt langsung
								if ($status->msg == "ok") $successCount++;
							}
							$reviewmsg = "Physical Count: $successCount / $totalRows records adjusted.";
							break;

						case "baladj":
							foreach ($data as $dt) {
								$status = XboSet::CurrentBalanceAdjustment($dt);
								if ($status->msg == "curbaladjusted") $successCount++;
							}
							$reviewmsg = "Balance Adjustment: $successCount / $totalRows records processed.";
							break;

						default:
							throw new Exception("Action '$actionname' belum terdaftar.");
					}
				} else {
					// 4. Import Standar
					$get = Super::importxls($val);
					$finalMsg = $get->msg;
					$errormsg = $get->errormsg;
					$reviewmsg = $get->reviewmsg;
				}

				if ($finalMsg == "error") break;
			}

			Set::throws(["msg" => $finalMsg, "errormsg" => $errormsg, "reviewmsg" => $reviewmsg]);

		} catch (Exception $e) {
			Set::throws(["msg" => "error", "errormsg" => $e->getMessage(), "reviewmsg" => null]);
		}
	}


	
} 


?>