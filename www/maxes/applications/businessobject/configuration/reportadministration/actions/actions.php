<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function trundt()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$dbo = new dbo();
			$dbtype = $dbo->dbtype();
			$dbname = $dbo->dbname();
			$conn = $tm->getConnection(); 

			// 1. Matikan Foreign Key 
			if ($dbtype == 'mysql') {
				DB::exec("SET FOREIGN_KEY_CHECKS = 0", $conn);
			} elseif ($dbtype == 'sqlserver') {
				// SQL Server: Nonaktifkan constraint
				// DB::exec("EXEC sp_msforeachtable \"ALTER TABLE ? NOCHECK CONSTRAINT all\"", $conn);
			}

			// 2. Daftar Tabel yang DIKECUALIKAN (Whitelist)
			$excludedTables = array_flip([
				"employees", "mxsnextval", "mxsusers", "mxsusersstatus", 
				"mxschart", "mxsusergroup", "mxsgroupline", "mxsgroups",
				"mxsgroupaction", "mxstracking", "mxsmodule", "mxsattrvalue",
				"mxschartline", "mxsapp", "mxsaction", "report",
				"reportparameter", "tax", "allowance", "papersetting", "papersettingbc",
				"measureunit", "conversion", "country", "attribute", "currency", "meter",
				"mxsimportdata", "mxskpi", "mxsrelationship", "qrcodeconfig",
				"orggldefault", "crontask", "woclass", "wotype", "zone", "assetfailure"
			]);

			// 3. Ambil daftar semua tabel secara Cross-Platform
			$allTables = [];
			if ($dbtype == "mysql") {
				$sql = "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '$dbname'";
				$tablesSet = (new Set())->useTransactionManager()->getMboSet("", $sql);
				while ($row = $tablesSet->fetch()) {
					$allTables[] = $row['table_name'];
				}
			} elseif ($dbtype == "sqlserver") {
				$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG = '$dbname'";
				// ... fetch ...
			} elseif ($dbtype == "oracle") {
				$sql = "SELECT TABLE_NAME FROM USER_TABLES";
				// ... fetch ...
			}

			// 4. TRUNCATE untuk tabel yang tidak dikecualikan
			foreach ($allTables as $table) {
				// Cek apakah tabel ada di daftar pengecualian
				if (!isset($excludedTables[$table])) {
					// TRUNCATE jauh lebih cepat dari DELETE dan mereset auto_increment
					DB::exec("TRUNCATE TABLE " . $table, $conn);
				}
			}

			// 5. Jalankan SQL Pembersihan Spesifik (Reset data inti)
			$currentyear = "-" . date("Y");
			$cleanupQueries = [
				"DELETE FROM woclass WHERE classcode NOT IN ('STANDING', 'OPERATION', 'QA', 'CUSTORDER', 'OWNPROJECT','PRODUCTION')",
				"DELETE FROM employees WHERE personid NOT IN ('maxesadmin')",
				// ... (query update employees yang panjang, disingkat untuk keterbacaan) ...
				"UPDATE employees SET firstname=null, lastname=null, fullname='maxesadmin', longlat=null, npwp=null, employeeid=null, citizenid=null, primaryphone=null, blackberrypin=null, wanum=null, lineid=null, primaryemail=null, emailpassword=null, emailprotocol=null, emailserver=null, emailport=null, emaillimit=null, dateofbirth=null, hiredate=null, terminationdate=null, address=null, city=null, nettincome=0, grossincome=0, yearlynettincome=0, yearlytaxcost=0, yearlytaxcharge=0, monthlytaxcharge=0, monthlytaxcharge2=0, yearlytaxcharge2=0, stateprovince=null, zippostalcode=null, country=null, department=null, section=null, supervisor=null, glaccount=null, bankname=null, basesalary=0, allowance=0, bpjsallowance=0, commision=0, potbpjs=0, otherallowance=0, yearlygrossincomecom=0, taxexemptcode=null, taxexemptcost=0, orgcode=null, sitecode=null WHERE personid IN ('maxesadmin')",
				
				"DELETE FROM mxsusers WHERE userid NOT IN ('maxesadmin')",
				"UPDATE mxsusers SET personid='maxesadmin' WHERE userid='maxesadmin'",
				"UPDATE mxsusers SET personid='eatadmin' WHERE userid='eatadmin'",
				"DELETE FROM mxsusersstatus WHERE userid NOT IN ('maxesadmin')",
				"UPDATE mxschart SET data=null, label=null",
				"UPDATE chartofaccount SET orgcode=null, sitecode=null",
				
				// Hapus data user/group selain admin inti
				"DELETE FROM mxsusergroup WHERE userid NOT IN ('maxesadmin', 'MAXESADMIN')",
				"DELETE FROM mxsgroupline WHERE groupnum NOT IN ('maxesadmin', 'MAXESADMIN', 'SYSTEM', 'FADMIN')",
				"DELETE FROM mxsgroupaction WHERE groupnum NOT IN ('maxesadmin', 'MAXESADMIN', 'SYSTEM', 'FADMIN')",
				"DELETE FROM mxsgroups WHERE groupnum NOT IN ('maxesadmin', 'MAXESADMIN', 'SYSTEM', 'FADMIN')",
				
				// Reset penomoran otomatis
				"UPDATE mxsnextval SET suffix='$currentyear', prefix=CONCAT(LEFT(keyattr, 2), '-'), nextval=1, pad=5"
			];

			foreach ($cleanupQueries as $query) {
				DB::exec($query, $conn);
			}

			// 6. Hidupkan Kembali Foreign Key Checks (SANGAT PENTING)
			if ($dbtype == 'mysql') {
				DB::exec("SET FOREIGN_KEY_CHECKS = 1", $conn);
			} elseif ($dbtype == 'sqlserver') {
				// DB::exec("EXEC sp_msforeachtable \"ALTER TABLE ? CHECK CONSTRAINT all\"", $conn);
			}

			$tm->commit();
			Super::sendJsonResponse("success", "Database has been successfully reset to factory defaults.");

		} catch (Exception $e) {
			// Jika terjadi error, pastikan FK checks dinyalakan kembali sebelum keluar
			if ($dbtype == 'mysql') {
				DB::exec("SET FOREIGN_KEY_CHECKS = 1", $conn);
			}
			$tm->rollback();
			Super::sendJsonResponse("error", "Reset failed: " . $e->getMessage());
		}
	}
} 

Class ValidateTables {
	
}
	

?>