<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function updateProcedureFromIntegManual()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("updateProcedureFromIntegManual");
			$integManualNum = $payload["integmanualnum"] ?? null;
			$integManualId = $payload["id"] ?? null;

			if (empty($integManualNum) || empty($integManualId)) {
				throw new InvalidArgumentException("Integ Manual Number and ID are required.");
			}

			$integManualData = (new Set())->useTransactionManager()
				->MboSetRemote("integmanual")
				->where("integmanualid = ? AND integmanualnum = ?", $integManualId, $integManualNum)
				->MboRemote("status, integmanualnum")
				->getMboSet()
				->fetch();

			if (!$integManualData) {
				throw new Exception("Integ Manual document not found for ID: $integManualId and Number: $integManualNum.");
			}

			if (strtolower($integManualData['status'] ?? '') !== 'active') {
				$tm->commit(); // Selesai tanpa error, tapi tidak melakukan apa-apa karena belum active
				Super::sendJsonResponse("success", "No action taken as the document status is not active.");
				return;
			}

			$itemRows = (new Set())->useTransactionManager()
				->MboSetRemote("integmanualitem")
				->where("integmanualnum = ?", $integManualNum)
				->MboRemote("proceduresnum")
				->getMboSet()
				->fetchData();

			if (empty($itemRows)) {
				$tm->commit();
				Super::sendJsonResponse("success", "Document is active, but no linked procedures items were found.");
				return;
			}

			$procedureNums = array_unique(array_column($itemRows, 'proceduresnum'));

			(new Set())->useTransactionManager()
				->MboSetRemote("procedures")
				->whereIn("proceduresnum", $procedureNums)
				->setValue("integmanualnum", $integManualNum)
				->change();

			$tm->commit();

			Super::sendJsonResponse("success", "Procedures have been successfully updated with the active Integ Manual number.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to update procedures from Integ Manual: " . $e->getMessage());
		}
	}
	public static function addProcedures()
	{
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("addProcedures");
			$integmanualnum = $payload["integmanualnum"] ?? null;
			$itemsData = $payload["data"] ?? [];

			if (empty($integmanualnum) || empty($itemsData) || !is_array($itemsData)) {
				throw new InvalidArgumentException("Parameter 'integmanualnum' dan array 'data' (procedures) wajib diisi.");
			}

			$procedureIds = [];
			foreach ($itemsData as $row) {
				if (!empty($row["proceduresid"])) {
					// Pastikan integer agar aman
					$procedureIds[] = (int)$row["proceduresid"];
				}
			}
			$procedureIds = array_unique($procedureIds);

			if (empty($procedureIds)) {
				throw new Exception("Tidak ada 'proceduresid' yang valid untuk diproses.");
			}

			$proceduresMap = (new Set())->useTransactionManager()
				->MboSetRemote("procedures")
				->whereIn("proceduresid", $procedureIds)
				->MboRemote("proceduresid, proceduresnum, description, remarks")
				->getMboSet()
				->fetchDataAssoc('proceduresid');

			if (empty($proceduresMap)) {
				throw new Exception("Data procedures tidak ditemukan di database untuk ID yang diberikan.");
			}

			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();

			

			$dataRowsToInsert = [];

			foreach ($itemsData as $row) {
				$procId = $row["proceduresid"] ?? null;

				if (isset($proceduresMap[$procId])) {
					$procRecord = $proceduresMap[$procId];

					$dataRowsToInsert[] = [
						"integmanualnum" => $integmanualnum,
						"proceduresnum"  => $procRecord["proceduresnum"],
						"notes"          => null, // atau "" (kosong sesuai request)
						"description"    => $procRecord["description"],
						"remarks"        => $procRecord["remarks"],
						"enterdate"      => $enterdate,
						"enterby"        => $enterby
					];
				}
			}

			if (!empty($dataRowsToInsert)) {
				(new Set())->useTransactionManager()
					->MboSetRemote("integmanualitem")
					->addStream($dataRowsToInsert);
			} else {
				throw new Exception("No valid records available for copying to integmanualitem.");
			}
			$tm->commit();

			$insertedCount = count($dataRowsToInsert);
			Super::sendJsonResponse("success", "$insertedCount procedure(s) have been added successfully.", [
				"integmanualnum" => $integmanualnum,
				"total_added" => $insertedCount
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to add procedures: " . $e->getMessage());
		}
	}
	
} 

Class ValidateTables {
	public static function tbcx(){
		$payload = Super::getParam("tbcx");
		$dataPackage = $payload["dataPackage"];

		$childTable = $dataPackage["datasetChild"];
		$deleteBy = $dataPackage["ownership"]["userid"];
		$parent = $dataPackage["ownership"]["xboset"];
		$parentid = $dataPackage["ownership"]["xbosetid"];
		$selectedRows =  $dataPackage["selectedRows"];
		
		if($childTable == "relatedwoticket"){
			Super::sendJsonResponse("error", "Cannot Delete followed up Work Order");
		}
	}
}

?>