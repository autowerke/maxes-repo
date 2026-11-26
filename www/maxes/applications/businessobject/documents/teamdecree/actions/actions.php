<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function addTeamMember()
	{
		// Langkah 0: Mulai Transaksi
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// Langkah 1: Ambil parameter dari payload
			// Sesuai struktur payload, dibungkus di dalam key "addTeamMember"
			$payload = Super::getParam("addTeamMember");
			$teamdecreenum = $payload["teamdecreenum"] ?? null;
			$membersData = $payload["data"] ?? [];

			// Validasi input utama
			if (empty($teamdecreenum) || empty($membersData) || !is_array($membersData)) {
				throw new InvalidArgumentException("Parameter 'teamdecreenum' dan array 'data' (members) wajib diisi.");
			}

			// Langkah 2: Kumpulkan semua employeesid yang dikirim untuk query massal
			$employeeIds = [];
			foreach ($membersData as $row) {
				if (!empty($row["employeesid"])) {
					$employeeIds[] = (int)$row["employeesid"];
				}
			}
			$employeeIds = array_unique($employeeIds);

			if (empty($employeeIds)) {
				throw new Exception("Tidak ada 'employeesid' yang valid untuk diproses.");
			}

			// Langkah 3: Ambil data tambahan (title, department) dari tabel 'employees' SEKALI SAJA
			// Gunakan fetchDataAssoc dengan key 'employeesid' agar pencarian O(1)
			$employeesMap = (new Set())->useTransactionManager()
				->MboSetRemote("employees")
				->whereIn("employeesid", $employeeIds)
				->MboRemote("employeesid, personid, title, department")
				->getMboSet()
				->fetchDataAssoc('employeesid');

			// Langkah 4: Siapkan data audit dan array untuk bulk insert ke 'teamdecreemember'
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate();
			$dataRowsToInsert = [];

			foreach ($membersData as $row) {
				$empId = $row["employeesid"] ?? null;
				$personid = $row["personid"] ?? null;

				// Pastikan employee ini ada di database
				if (isset($employeesMap[$empId])) {
					$empRecord = $employeesMap[$empId];

					$dataRowsToInsert[] = [
						"teamdecreenum" => $teamdecreenum,
						"personid"      => $personid ?: $empRecord["personid"], // Fallback ke DB jika payload kosong
						"memberrole"    => $empRecord["title"] ?? null,       // Mapping dari employees.title
						"division"      => $empRecord["department"] ?? null,  // Mapping dari employees.department
						"notes"         => null,
						"description"   => null,
						"remarks"       => null,
						"enterdate"     => $enterdate,
						"enterby"       => $enterby
					];
				}
			}

			// Langkah 5: Eksekusi Bulk Insert menggunakan addStream()
			if (!empty($dataRowsToInsert)) {
				(new Set())->useTransactionManager()
					->MboSetRemote("teamdecreemember")
					->addStream($dataRowsToInsert);
			} else {
				throw new Exception("Tidak ada baris valid yang dapat ditambahkan ke teamdecreemember.");
			}

			// Langkah 6: Commit Transaksi jika semua sukses
			$tm->commit();

			$insertedCount = count($dataRowsToInsert);
			Super::sendJsonResponse("success", "$insertedCount team member(s) have been added successfully.", [
				"teamdecreenum" => $teamdecreenum,
				"total_added"   => $insertedCount
			]);

		} catch (Exception $e) {
			// Rollback jika terjadi error di tengah jalan
			$tm->rollback();
			Super::sendJsonResponse("error", "Gagal menambahkan team member: " . $e->getMessage());
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