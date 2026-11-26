<?php


include_once __DIR__ . '/dbo.php';


date_default_timezone_set("Asia/Jakarta");

class CreateResult
{
    public static function Create($data, $fname)
    {
        $fp = fopen($fname, "a");
        fwrite($fp, $data);
        fclose($fp);
    }

    public static function CreateJSON($data, $fname)
    {
        $fp = fopen($fname, "w");
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fclose($fp);
    }
}

class SendData
{
    public static function Send2($url, $data)
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url); // url post request
        curl_setopt($request, CURLOPT_POST, 1); //send as post request
        curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($request);
        curl_close($request);

        return $response;
    }
    public static function Send($url, $data, $type)
    {
        $url = $url;
        $ch = curl_init();
        if ($url == null) {
            $url = "http://xx.xx.11.3:8899/api/";
        } else {
            $url = $url;
        }
        if ($type == "POST" || $type == "post") {
            $data = $data;
            $post = 1;
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "content-type:application/json",
            ]);
        } else {
            if ($type == "GET") {
                $data = http_build_query($data, "", "&");
                $post = 0;
            }
        }

        $ss = new stdClass();
        $isMaximoIntegrated = Super::isMaximoIntegrated();

        if ($isMaximoIntegrated == "true") {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type:application/json','Content-Length: ' . strlen($data)));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            if ($response === false) {
                $msg = curl_error($ch);
            } else {
                $msg = $response;
            }
            curl_close($ch);
            $ss->response = $response;
            $ss->msg = $msg;
            //echo $ss;
            return $ss;
        } else {
            $ss->response = "Not integrated to External System";
            $ss->msg = "The current value for Maximo Integrated  is $isMaximoIntegrated.";
            return $ss;
        }
    }
}

class DB
{
    public $hostname;
    public $username;
    public $password;
    public $dbname;
    public $dbtype;
    function getDBProperties()
    {
        $dbo = new dbo();
        $this->hostname = $dbo->hostname();
        $this->username = $dbo->username();
        $this->password = $dbo->password();
        $this->dbname = $dbo->dbname();
        $this->dbtype = $dbo->dbtype();

        
        return $this;
    }
    public static function getMXConnection()
    {
        $dbo = new dbo();
        $hostname = $dbo->hostname();
        $username = $dbo->username();
        $password = $dbo->password();
        $dbname = $dbo->dbname();
        $dbtype = $dbo->dbtype();
        $dbport = $dbo->dbport();

        if ($dbtype == "oracle") {
            $dbchar = "AL32UTF8";
            $dbconnectionstring =
                "//" . $hostname . ":" . $dbport . "/" . $dbname;
            //$conn = oci_connect($username, $password, $dbconnectionstring, $dbchar);
            //$connectionString = '(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = '.$hostname.')(PORT = '.$dbport.')) (CONNECT_DATA = (SERVICE_NAME = '.$dbname.') (SID = '.$dbname.')))';
            //$conn = oci_connect($username, $password, $connectionString);
            $conn = oci_pconnect(
                $username,
                $password,
                $hostname . ":" . $dbport . "/" . $dbname
            );
        }
        if ($dbtype == "mysql") {
            //$hostname = $hostname.":".$dbport;
            $conn = mysqli_connect(
                $hostname,
                $username,
                $password,
                $dbname,
                $dbport
            );
        }
        if ($dbtype == "sqlserver") {
            $Server = $hostname;
            $Database = ["Database" => $dbname];
            $conn = sqlsrv_connect($Server, $Database);

            if ($conn) {
            } else {
                echo "Koneksi gagal !";
                die(print_r(sqlsrv_errors(), true));
            }
        }
        if ($conn) {
            return $conn;
        } else {
            $conn =
                "Failed to connect to MySQL: " .
                mysqli_connect_error() .
                "<br>Hostname : $hostname <br>Username : $username <br>Password : $password <br>Database Name : $dbname";
            echo $conn;
            return $conn;
        }
    }
	
	 public static function executePrepared($sql, $params, $types = null, $conn = null)
    {
        if ($conn === null) {
            $tm = TransactionManager::getInstance();
            $conn = $tm->getConnection();
        }
        
        $dbo = new dbo();
        $dbtype = $dbo->dbtype();
        $rowsAffected = 0;

        try {
            if ($dbtype == "mysql") {
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("MySQL prepare failed: (" . $conn->errno . ") " . $conn->error . " SQL: " . $sql);
                }
                
                // Jika tipe tidak dispesifikasikan, default ke string untuk semua
                if ($types === null) {
                    $types = str_repeat('s', count($params));
                }
                
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }

                if (!$stmt->execute()) {
                    throw new Exception("MySQL execute failed: (" . $stmt->errno . ") " . $stmt->error);
                }
                $rowsAffected = $stmt->affected_rows;
                $stmt->close();
            } 
            elseif ($dbtype == "sqlserver") {
                // sqlsrv_query bisa langsung menerima parameter
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    throw new Exception("SQL Server execute failed: " . print_r(sqlsrv_errors(), true));
                }
                $rowsAffected = sqlsrv_rows_affected($stmt);
            } 
            elseif ($dbtype == "oracle") {
                $stmt = oci_parse($conn, $sql);
                if (!$stmt) { throw new Exception("Oracle parse failed: " . oci_error($conn)['message']); }

                // Di Oracle, binding dilakukan dengan nama placeholder
                foreach ($params as $key => &$val) {
                    // Asumsi $params adalah associative array [':placeholder' => 'value']
                    oci_bind_by_name($stmt, $key, $val);
                }

                if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) { // Gunakan NO_AUTO_COMMIT jika dalam transaksi
                    throw new Exception("Oracle execute failed: " . oci_error($stmt)['message']);
                }
                $rowsAffected = oci_num_rows($stmt);
            }
            
            return $rowsAffected;

        } catch (Exception $e) {
            throw $e;
        }
    }
    public static function exec($stmt, $conn = null) {
        if ($conn === null) {
            $tm = TransactionManager::getInstance();
            $conn = $tm->getConnection();
        }
        
        ini_set("max_execution_time", 6000);
        global $errorexec;
        $dbo = new dbo();
        $dbtype = $dbo->dbtype();
       
        try {
            if ($dbtype == "oracle") {
              
                $statement = oci_parse($conn, $stmt);
                if (!oci_execute($statement)) {
                    $e = oci_error($statement);
                    throw new Exception(
                        "Oracle error: " .
                            htmlentities($e["message"]) .
                            " SQL: " .
                            $stmt
                    );
                }
                $rowsAffected = oci_num_rows($statement);
                oci_free_statement($statement);

            } elseif ($dbtype == "mysql") {
               
                if ($conn->query($stmt)) {
                    $rowsAffected = $conn->affected_rows;
                    
                } else {
                    $exceptMsg = mysqli_error($conn);
                    if (empty($exceptMsg)) {
                        throw new Exception(
                            "<strong>Unknown Error Occurred</strong> while executing SQL:<br><pre>{$stmt}</pre><hr>" .
                            "Please check your custom class/function. Make sure all declarations and syntax are correct."
                        );
                    } else {
                        throw new Exception(
                            "<strong>MySQL Error:</strong> " . htmlspecialchars($exceptMsg) . "<br>" .
                            "<strong>SQL:</strong><br><pre>{$stmt}</pre><hr>"
                        );
                    }
                }
             
            } elseif ($dbtype == "sqlserver") {
               
                $exec = sqlsrv_query(
                    $conn,
                    $stmt,
                    [],
                    ["Scrollable" => SQLSRV_CURSOR_KEYSET]
                );
                if ($exec === false) {
                    throw new Exception(
                        "SQL Server error: " .
                            print_r(sqlsrv_errors(), true) .
                            " SQL: " .
                            $stmt
                    );
                }
                $rowsAffected = sqlsrv_rows_affected($exec);
            }
            return null;
        } catch (Exception $e) {
            
            throw $e;
        }
    }
}

class Mxs
{
    function dateFormat($mystring)
    {
        $dbo = new dbo();
        $dbtype = $dbo->dbtype();
        if ($dbtype == "oracle") {
            $mystring = "TO_DATE('" . $mystring . "', 'yyyy-mm-dd hh24:mi:ss')";
        }
        if ($dbtype == "mysql") {
            $mystring = "'" . $mystring . "'";
        }

        return $mystring;
    }
    function dateFormatColumn($mystring)
    {
        $dbo = new dbo();
        $dbtype = $dbo->dbtype();
        if ($dbtype == "oracle") {
            $x = explode("-", $mystring);
            $mystring = $x[2] . "-" . $x[1] . "-" . $x[0];
        } else {
            $mystring = $mystring;
        }
        return $mystring;
    }
}




#[AllowDynamicProperties] 
class Set extends TransactionalSet
{
    private $transactionStarted = false;

    public function __call($name, $arguments)
    {
       
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        // Hanya handle transaksi jika menggunakan TransactionManager
        if ($this->useTransactionManager) {
            $tm = TransactionManager::getInstance();
            if ($this->transactionStarted) {
                try {
                    $tm->commit();
                } catch (Exception $e) {
                    $tm->rollback();
                    error_log("Failed to commit transaction: " . $e->getMessage());
                }
            }
        }
    }
}

#[AllowDynamicProperties]
class TransactionalSet
{
    public $conn;
	public $where;
    public $mbosetname;
    public $sql;
    public $result;
    public $columnname;
	private $joins =[];
	protected $useTransactionManager = false;
    protected $whereClause = null;
	protected $whereConditions = []; 
    protected $whereParams = [];
	public $getTableID = null;
	public $affectedRows = 0;
	public $selectedColumns = [];
	public $resultSet = [];
	public $leftjoin;
    public $offset = null;
    public $rowCount = null;
    
    public $mboset;
    public $stacked;
    public $mysqlerror;
    public $data;
    public $setval = []; // Inisialisasi array karena dipakai sebagai array
    public $cnval;       // Tadi sempat kulihat dipakai juga
    public $colvals;     // Untuk update
    public $sqlcols;
    public $sqlvals;
    public $msg;         // Untuk getErrorMessage
    

    protected $havingConditions = [];
    protected $havingParams = [];
    

    private function fixTableNameCase($sql) {
        $dbo = new dbo();
        // Hanya jalankan untuk MySQL
        if ($dbo->dbtype() != 'mysql') {
            return $sql;
        }

        // Regex Magic: Mencari kata setelah FROM atau JOIN dan mengubahnya jadi lowercase
        // Pola: Menangkap FROM/JOIN (Group 1), spasi, lalu NamaTabel (Group 2)
        return preg_replace_callback(
            '/(FROM|JOIN|UPDATE|INTO)\s+([`A-Za-z0-9_]+)/i', 
            function($matches) {
                // $matches[1] adalah perintah (FROM, JOIN, dll)
                // $matches[2] adalah NamaTabel -> Kita Lowercase
                return strtoupper($matches[1]) . " " . strtolower($matches[2]);
            }, 
            $sql
        );
    }

	private static function sanitizeIdentifier(string $identifier): string
	{
		return preg_replace('/[^a-zA-Z0-9_.]/', '', $identifier);
	}
	
	
    public function having(string $clause, ...$params): self
    {
        // Gunakan logika validasi yang sama persis seperti di where()
        $placeholderCount = substr_count($clause, '?');
        $paramCount = count($params);

        if ($placeholderCount !== $paramCount) {
           throw new Exception("The number of placeholders '?' ($placeholderCount) in HAVING does not match the number of parameters ($paramCount).");

        }
            
        // Tambahkan ke array kondisi HAVING
        $this->havingConditions[] = "($clause)"; 
        if (!empty($params)) {
            $this->havingParams = array_merge($this->havingParams, $params);
        }
    
        return $this;
    }


    private function _buildHavingClause(): string
    {
       
        if (!empty($this->havingConditions) && is_array($this->havingConditions)) {
            
            $combinedClause = implode(' AND ', $this->havingConditions);
            $params = $this->havingParams;
            
            if (substr_count($combinedClause, '?') != count($params)) {
                throw new Exception('Internal error: Mismatch in having clause parameters.');
            }

            $dbo = new dbo();
            $dbtype = $dbo->dbtype();
            $i = 0;

            while (($pos = strpos($combinedClause, '?')) !== false) {
                $param = $params[$i++];
                $escapedParam = '';

                if (is_null($param)) {
                    $escapedParam = 'NULL';
                } 
                elseif (is_int($param) || is_float($param)) {
                    $escapedParam = $param;
                } 
                else {
                    if ($dbtype == 'mysql' && $this->conn) {
                        $escapedParam = "'" . $this->conn->real_escape_string((string)$param) . "'";
                    } else {
                        $escapedParam = "'" . str_replace("'", "''", (string)$param) . "'";
                    }
                }
                $combinedClause = substr_replace($combinedClause, $escapedParam, $pos, 1);
            }
            
            return ' HAVING ' . $combinedClause;
        }

      
        if (!empty($this->having)) {
            return " " . $this->having;
        }

        return "";
    }


	public function addStream(array $dataRows): self
	{
		
		if (empty($this->mbosetname)) {
			throw new Exception("Use MboSetRemote first before using addStream");
		}
		if (empty($dataRows) || !is_array($dataRows[0])) {
			throw new Exception("addStream Data is null, please define the array of associative arrays.");
		}

		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			
			$columns = array_keys($dataRows[0]);
			$sanitizedColumns = array_map([self::class, 'sanitizeIdentifier'], $columns);
			$columnSql = implode(', ', $sanitizedColumns);
			$numColumns = count($columns);
			
			$dbType = (new dbo())->dbtype();

			// Pilih Strategi Berdasarkan Tipe Database
			
			if ($dbType === 'mysql' || $dbType === 'sqlserver') {
			
				// <<< SQL Server & MySQL >>>
				
				$valuePlaceholders = [];
				$allParams = [];
				$rowPlaceholder = '(' . implode(',', array_fill(0, $numColumns, '?')) . ')';

				foreach ($dataRows as $row) {
					$valuePlaceholders[] = $rowPlaceholder;
					foreach ($columns as $col) {
						$allParams[] = $row[$col] ?? null;
					}
				}

				$sql = "INSERT INTO " . $this->mbosetname . " ($columnSql) VALUES " . implode(', ', $valuePlaceholders);
				
				// Tentukan tipe data untuk MySQL, default 's' (string) untuk semua
				$types = ($dbType === 'mysql') ? str_repeat('s', count($allParams)) : null;

				
				// Semua kerumitan eksekusi sekarang ditangani oleh DB::executePrepared
				$this->affectedRows = DB::executePrepared($sql, $allParams, $types, $this->conn);

			} else if ($dbType === 'oracle') {
		
				// <<< ORACLE >>>
				
				$this->affectedRows = 0;
				$sqlTemplate = "INSERT INTO " . $this->mbosetname . " ($columnSql) VALUES ";

				foreach ($dataRows as $row) {
					$params = [];
					$placeholders = [];
					$i = 0;
					foreach($columns as $col) {
						// Gunakan placeholder bernama untuk Oracle
						$placeholder = ":p".$i++;
						$placeholders[] = $placeholder;
						$params[$placeholder] = $row[$col] ?? null;
					}
					
					$sql = $sqlTemplate . "(" . implode(",", $placeholders) . ")";
					$this->affectedRows += DB::executePrepared($sql, $params, null, $this->conn);
				}
			}
			//echo $sql;
			$tm->commit();

		} catch (Exception $e) {
			$tm->rollback();
			throw $e;
		}

		return $this;
	}
		
		
	
	public function __construct()
    {
        $this->conn = DB::getMXConnection();
        $this->useTransactionManager = false;
    }
	public function useTransactionManager() {
        try {
			
            $tm = TransactionManager::getInstance();
            $this->conn = $tm->getConnection();
            $this->useTransactionManager = true;
        } catch (Exception $e) {
			
            $this->conn = DB::getMXConnection();
            $this->useTransactionManager = false;
        }
        return $this;
    }
	
    public function getSql()
    {
        return $this->sql;
    }
	public function combineTables(
        $sourceTable,
        $destTable,
        $fieldMappings,
        $userInputs,
        $whereClause = ""
    ) {
        try {
            $this->conn->begin_transaction();

            $idfieldDestination = $destTable . "id";

            // Dapatkan nilai maksimum dari kolom ID di tabel tujuan
            $maxIdQuery = "SELECT COALESCE(MAX(`$idfieldDestination`), 0) AS max_id FROM `$destTable`";
            $maxIdResult = $this->conn->query($maxIdQuery);

            if (!$maxIdResult) {
                throw new Exception("Error querying max ID: " . $this->conn->error . " SQL: " . $maxIdQuery);
            }

            $row = $maxIdResult->fetch_assoc();
            $maxId = intval($row["max_id"]) + 1;

            $maxIdResult->close();

            $selectFields = [];
            $sourceToDestMap = [];

            foreach ($fieldMappings as $mapping) {
                $sourceField = $mapping[0];
                $destField = $mapping[1];

                $selectFields[] = "`$sourceField`";
                $sourceToDestMap[$sourceField] = $destField;
            }

            $selectQuery =
                "SELECT " .
                implode(", ", $selectFields) .
                " FROM `$sourceTable`";

            if (!empty($whereClause)) {
                $selectQuery .= " WHERE $whereClause";
            }

            $result = $this->conn->query($selectQuery);

            if (!$result) {
                throw new Exception(
                    "Error querying source table: " . $this->conn->error . " SQL: " . $selectQuery
                );
            }

            $userInputMap = [];
            foreach ($userInputs as $input) {
                $fieldName = $input[0];
                $fieldValue = $input[1];
                $userInputMap[$fieldName] = $fieldValue;
            }

            while ($row = $result->fetch_assoc()) {
                // Persiapkan array untuk penyisipan
                $destFields = [];
                $destValues = [];

                $destFields[] = "`$idfieldDestination`";
                $destValues[] = $maxId;

                foreach ($row as $sourceField => $value) {
                    $destField = $sourceToDestMap[$sourceField];
                    $destFields[] = "`$destField`";
                    $destValues[] =
                        "'" . $this->conn->real_escape_string($value) . "'";
                }

                foreach ($userInputMap as $destField => $value) {
                    if (!in_array("`$destField`", $destFields)) {
                        $destFields[] = "`$destField`";
                        $destValues[] =
                            "'" . $this->conn->real_escape_string($value) . "'";
                    }
                }

                $insertQuery =
                    "INSERT INTO `$destTable` (" .
                    implode(", ", $destFields) .
                    ") 
							   VALUES (" .
                    implode(", ", $destValues) .
                    ")";

                if (!$this->conn->query($insertQuery)) {
                    throw new Exception(
                        "Error inserting into destination table: " .
                            $this->conn->error . " SQL: " . $insertQuery
                    );
                }

                $maxId++;
            }

            // Commit transaksi
            $this->conn->commit();

            // Tutup hasil query
            $result->close();


            return true;
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            $this->conn->rollback();


            // Output error
            error_log("combineTables Error: " . $e->getMessage());

            return false;
        }
    }
    public function whereDestination($where_clause)
    {
        $this->newWhereClause = $where_clause;
        return $this;
    }
	public function updateDestinationFields(...$fieldValuePairs)
    {
        if (empty($this->mbosetname)) {
            trigger_error(
                "No destination table set. Call copyTableToTable first.",
                E_USER_ERROR
            );
            return $this;
        }

        $setStatements = [];

        foreach ($fieldValuePairs as $pair) {
            if (!is_array($pair) || count($pair) !== 2) {
                trigger_error("Invalid field-value pair format", E_USER_ERROR);
                return $this;
            }

            $field = $this->conn->real_escape_string($pair[0]);

            if (is_null($pair[1])) {
                $setStatements[] = "`$field` = NULL";
            } elseif (is_numeric($pair[1])) {
                $setStatements[] = "`$field` = " . $pair[1];
            } else {
                $value = $this->conn->real_escape_string($pair[1]);
                $setStatements[] = "`$field` = '$value'";
            }
        }

        if (empty($setStatements)) {
            return $this;
        }

        $sql =
            "UPDATE `{$this->mbosetname}` SET " . implode(", ", $setStatements);

        if (!empty($this->newWhereClause)) {
            $sql .= " WHERE {$this->newWhereClause}";
        } else {
            if (!empty($this->lastWhereClause)) {
                $sql .= " WHERE {$this->lastWhereClause}";
            }
        }

        try {
            $this->conn->begin_transaction();

            $result = $this->conn->query($sql);

            if ($result === false) {
                throw new Exception("Update query failed: " . $this->conn->error . " SQL: <br>" . $sql);
            }

            $this->conn->commit();
            $affectedRows = $this->conn->affected_rows;
        } catch (Exception $e) {
            $this->conn->rollback();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        return $this;
    }
	
	public function copyTableToTable($fromTable, $toTable,$whereClause, ...$fieldMappings) {
		if (empty($fromTable) || empty($toTable) || empty($fieldMappings)) {
			trigger_error("Invalid input parameters: fromTable, toTable, and fieldMappings are required.", E_USER_ERROR);
			return $this;
		}

		$safeFromTable = $this->conn->real_escape_string($fromTable);
		$safeToTable = $this->conn->real_escape_string($toTable);

		$idfieldDestination = $safeToTable . "id";
		$safeIdfieldDestination = $this->conn->real_escape_string($idfieldDestination);

		$this->mbosetname = $safeToTable;
		$this->lastWhereClause = $whereClause;

		$fromFields = [];
		$toFields = [];
		foreach ($fieldMappings as $mapping) {
			if (!is_array($mapping) || count($mapping) !== 2) {
				trigger_error("Invalid field mapping format. Each mapping must be an array with two elements [fromField, toField].", E_USER_ERROR);
				return $this;
			}

			$safeFromField = $this->conn->real_escape_string($mapping[0]);
			$safeToField = $this->conn->real_escape_string($mapping[1]);

			$fromFields[] = $safeFromField;
			$toFields[] = $safeToField;
		}

		if (empty($fromFields)) {
			trigger_error("No valid field mappings provided.", E_USER_ERROR);
			return $this;
		}

		try {
			$this->conn->begin_transaction();

			$safeWhereClause = !empty($whereClause) ? " WHERE " . $whereClause : '';

			$countQuery = "SELECT COUNT(*) AS record_count FROM `$safeFromTable`" . $safeWhereClause;
			$countResult = $this->conn->query($countQuery);
			if ($countResult === false) {
				throw new Exception("Failed to count records: " . $this->conn->error . " SQL: " . $countQuery);
			}

			$countRow = $countResult->fetch_assoc();
			$recordCount = (int) $countRow["record_count"];
			$countResult->free();

			if ($recordCount === 0) {
				$this->conn->commit();
				return $this;
			}

			$maxIdQuery = "SELECT IFNULL(MAX(`$safeIdfieldDestination`), 0) + 1 AS start_id FROM `$safeToTable`";
			$maxIdResult = $this->conn->query($maxIdQuery);
			if ($maxIdResult === false) {
				throw new Exception("Failed to get max ID: " . $this->conn->error . " SQL: " . $maxIdQuery);
			}

			$maxIdRow = $maxIdResult->fetch_assoc();
			$startId = (int) $maxIdRow["start_id"];
			$maxIdResult->free();

			// Bangun bagian INSERT dari query
			$insertFields = array_map([$this->conn, 'real_escape_string'], $toFields);
			$insertFields[] = $safeIdfieldDestination; 
			$insertFieldsSql = "`" . implode("`, `", $insertFields) . "`";

			$selectFields = array_map([$this->conn, 'real_escape_string'], $fromFields); 
			$selectFieldsSql = "`" . implode("`, `", $selectFields) . "`";
			$selectFieldsSql .= ", ";
			$selectFieldsSql .= "($startId + @rownum := @rownum + 1) as `$safeIdfieldDestination`"; 

			$sql = "INSERT INTO `$safeToTable` ($insertFieldsSql)
					SELECT $selectFieldsSql
					FROM `$safeFromTable`
					JOIN (SELECT @rownum := -1) r" . $safeWhereClause; 

			$result = $this->conn->query($sql);
			if ($result === false) {
				throw new Exception("Failed to copy records: " . $this->conn->error . " SQL: " . $sql);
			}
			$this->conn->commit();

		} catch (Exception $e) {
			$this->conn->rollback();
			error_log("copyTableToTable Error: " . $e->getMessage());
			throw $e;
		}

		return $this; // Untuk chaining
	}

    public static function throws($data, $pretty = null)
    {
        if ($pretty == null) {
            echo json_encode($data);
        } else {
            echo json_encode($data, $pretty);
        }
    }
    function MboSetRemote($n)
    {
        
        $this->mboset = "$n";
        $this->stacked = null;
        $this->mbosetname = $n;
	
        if (strpos($this->mbosetname, " ") !== false) {
            $ss = explode(" ", $this->mbosetname);
            $this->mbosetname = $ss[0];
        }

        $dbo = new dbo();
        if ($dbo->dbtype() == 'mysql') {
            $this->mbosetname = strtolower($this->mbosetname);
        }

        return $this;
    }
    
	function setRelationship($co = "", $wc = "")
    {
        if ($co != "") {
            $dbo = new dbo();
            if ($dbo->dbtype() == 'mysql') {
                $co = strtolower($co); 
            }

            $this->joins[] = " left join $co on $wc";
            $this->leftjoin = implode(" ", $this->joins);
        } else {
            $this->leftjoin = "";
        }
        return $this;
    }

    function setOuterRelationship($co = "", $wc = "")
    {
        if ($co != "") {
            // DETEKSI DB TYPE
            $dbo = new dbo();
            if ($dbo->dbtype() == 'mysql') {
                // Khusus MySQL: Lowercase nama tabel
                $co = strtolower($co);
            }

            $this->joins[] = " left outer join $co on $wc";
            $this->leftjoin = implode(" ", $this->joins);
        } else {
            $this->leftjoin = "";
        }
        return $this;
    }
	
	
    public function buildWhereFromJson(string $jsonString, string $operator = 'AND'): self
    {
        $filters = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Format JSON untuk where tidak valid.");
        }

        if (empty($filters) || !is_array($filters)) {
            return $this; 
        }

        $conditions = [];
        $params = [];

        //  struktur adalah array of objects
        foreach ($filters as $filterGroup) {
            if (is_array($filterGroup)) {
                foreach ($filterGroup as $column => $value) {
                    $sanitizedColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
                    if (!empty($sanitizedColumn)) {
                        // Cek jika value adalah array (untuk klausa IN)
                        if (is_array($value)) {
                            if (empty($value)) continue; // Lewati jika array IN kosong
                            $placeholders = implode(',', array_fill(0, count($value), '?'));
                            $conditions[] = "$sanitizedColumn IN ($placeholders)";
                            $params = array_merge($params, $value);
                        } else {
                            $conditions[] = "$sanitizedColumn = ?";
                            $params[] = $value;
                        }
                    }
                }
            }
        }
        
        if (!empty($conditions)) {
            $whereClause = implode(" $operator ", $conditions);
            $this->where($whereClause, ...$params);
        }

        return $this;
    }
	 public function whereNull(string $column): self
    {
     
        $safeColumn = self::sanitizeIdentifier($column);
        $clause = "$safeColumn IS NULL";
        $this->where($clause); 
        
        return $this;
    }
	public function whereNotNull(string $column): self
    {
       
        $safeColumn = self::sanitizeIdentifier($column);
        $clause = "$safeColumn IS NOT NULL";
        $this->where($clause);
        
        return $this;
    }
	 public function whereNotIn(string $column, array $values): self
    {
      
        if (empty($values)) {
            $this->where('1 = 0');
            return $this;
        }

        $safeColumn = self::sanitizeIdentifier($column);
        $placeholders = implode(',', array_fill(0, count($values), '?'));

        $whereClause = "$safeColumn NOT IN ($placeholders)";

        $this->where($whereClause, ...$values);
        return $this;
    }
	 public function whereIn(string $column, array $values): self
    {
      
        if (empty($values)) {
            $this->where('1 = 0');
            return $this;
        }

        $safeColumn = self::sanitizeIdentifier($column);
        $placeholders = implode(',', array_fill(0, count($values), '?'));

        $whereClause = "$safeColumn IN ($placeholders)";

        $this->where($whereClause, ...$values);
        return $this;
    }
	public function where($clause, ...$params)
    {
        if (substr_count($clause, '?') != count($params)) {
            throw new Exception('The number of placeholders (?) does not match the number of parameters in the where() method');
        }
		
		$this->whereConditions[] = "($clause)"; 
		$this->whereParams = array_merge($this->whereParams, $params);
       
        $this->where = null;
        return $this;
    }
	
	function setWhere($w) {
		/*
		' or '1=1
		*/
		// Validasi pattern 
		
			
			// Hanya replace triple quotes atau lebih
			// Replace ''' (3 quotes) dengan '' (2 quotes)
			$w = str_replace("'''", "''", $w);
			
			// Replace '''' (4 quotes) dengan '' (2 quotes) 
			while(strpos($w, "''''") !== false) {
				$w = str_replace("''''", "''", $w);
			}
			
			$w = preg_replace("/'{3,}/", "''", $w);
		
		$w = preg_replace("/'{3,}/", "''", $w);
		
		$this->where = " where $w";

        $this->whereClause = null;
        $this->whereParams = [];
		
		return $this;
	}
	
    
	// Di dalam class TransactionalSet (object.txt)

    private function resetState()
    {
        // Reset semua properti yang berhubungan dengan pembangunan kueri
        $this->joins = [];
        $this->leftjoin = null;
        $this->getstring = null;
        $this->select = null;
        
        // Ini yang paling penting
        $this->whereConditions = [];
        $this->whereParams = [];
        $this->where = null;
        $this->whr = null;
        
        $this->groupby = null;
        $this->grp = null;
        $this->order = null;
        $this->ordr = null;
        $this->limit = null;
        $this->lmt = null;

        // Reset juga properti untuk INSERT/UPDATE
        $this->setval = [];

        // Hentikan penggunaan variabel global di MboRemote
        global $strings;
        $strings = [];
    }
	 private function _buildWhereClause()
	{
		
		if (!empty($this->whereConditions) && is_array($this->whereConditions)) {
			
			// 1. Gabungkan semua string kondisi menjadi satu.
			// Contoh: ["(col1 = ?)", "(col2 IN (?,?))"] menjadi "(col1 = ?) AND (col2 IN (?,?))"
			$combinedClause = implode(' AND ', $this->whereConditions);
			
			// 2. Ambil semua parameter yang sudah terkumpul.
			$params = $this->whereParams;
			
			// Safety check awal
			if (substr_count($combinedClause, '?') != count($params)) {
				 throw new Exception('The number of placeholders (' . count($params) . ') does not match the number of parameters in the placeholder (?) (' . substr_count($combinedClause, '?') . ') in _buildWhereClause.'); //sinibang
			}

			
			$dbo = new dbo();
			$dbtype = $dbo->dbtype();
			$i = 0;

			while (($pos = strpos($combinedClause, '?')) !== false) {
	
				$param = $params[$i++];
				$escapedParam = '';

				if (is_null($param)) {
					$escapedParam = 'NULL';
				} elseif (is_int($param) || is_float($param)) {
                    // Hanya angka asli PHP (bukan string angka) yang tidak diberi kutip.
                    $escapedParam = $param;
                }  else {
					if ($dbtype == 'mysql' && $this->conn) {
						$escapedParam = "'" . $this->conn->real_escape_string((string)$param) . "'";
					} else { 
						$escapedParam = "'" . str_replace("'", "''", (string)$param) . "'";
					}
				}
				$combinedClause = substr_replace($combinedClause, $escapedParam, $pos, 1);
			}
			
			return ' WHERE ' . $combinedClause;
		}

		if (!empty($this->where)) {
			return $this->where; 
		}

		return ""; // Tidak ada klausa where
	}
	
	function isSqlInjection($w) {
		// 1. Deteksi OR 1=1 atau '1'='1' literal
		if (preg_match("/\sor\s+1\s*=\s*1/i", $w)) return true;
		if (preg_match("/(['\"])\s*or\s*\\1?\s*\d+\s*=\\1?\s*\d+/i", $w)) return true;

		// 2. Deteksi pola 'x'='x' (tautology klasik)
		if (preg_match("/(['\"])[a-zA-Z0-9]+\\1\s*=\s*\\1[a-zA-Z0-9]+\\1/i", $w)) return true;

		// 3. Deteksi komentar SQL
		if (preg_match("/--|#|\/\*/", $w)) return true;

		// 4. Deteksi UNION SELECT, SLEEP, dll
		if (preg_match("/\b(UNION|SLEEP|BENCHMARK|DROP|INSERT|UPDATE|DELETE|EXEC|LOAD_FILE)\b/i", $w)) return true;

		// 5. Subquery: Izinkan SELECT MAX|MIN|AVG|COUNT
		if (preg_match("/\(\s*select\b/i", $w)) {
			if (!preg_match("/in\s*\(\s*select\s+(max|min|avg|count)\s*\(/i", $w)) {
				return true; // blokir subquery lain
			}
		}

		// 6. Deteksi numeric-only tautology: 1=1 atau 0=0
		if (preg_match("/(^|\s)(\d+)\s*=\s*\2(\s|$)/", $w)) return true;

		return false; // lolos
	}
    function group($g)
    {
        $this->groupby = " group by $g";
        return $this;
    }
    function order($o)
	{
		if(empty($o) || $o == ""){
			// Jangan kembalikan null, kembalikan objeknya sendiri
			// agar method chaining tidak putus.
			return $this;

		}
		$this->order = " order by $o ";
		return $this;
	}

    function limit($offset = null, $rowCount = null)
    {
        if ($offset === null) {
            return $this; 
        }

        $dbo = new dbo();
        $dbType = $dbo->dbtype();

        $offset = (int)$offset;
        $rowCount = ($rowCount !== null) ? (int)$rowCount : null;

        if ($dbType === 'mysql') {
            if ($rowCount !== null) {
                $this->limit = " LIMIT {$offset}, {$rowCount} ";
            } else {
                $this->limit = " LIMIT {$offset} ";
            }
        } else if ($dbType === 'sqlserver' || $dbType === 'oracle') {
                      
            $this->offset = $offset;
            $this->rowCount = $rowCount;
        }
        
        return $this;
    }

    function MboRemote($s)
	{
		
		if (!isset($this->selectedColumns)) {
			$this->selectedColumns = [];
		}
		
		$this->selectedColumns[] = $s;
		$this->getstring = implode(",", $this->selectedColumns);
		return $this;
	}


    public function setValue(string $col, $val): self
    {
      
        if ($val === null || $val === '') {
            $colvalue = new stdClass();
            $colvalue->col = $col;
            $colvalue->val = null;
            $this->setval[] = $colvalue;
            return $this;
        }

 
        // Hanya trim jika nilainya adalah string.
        // Ini untuk menghindari error jika ada yang mengirim tipe data lain (angka, dll.)
        $trimmedValue = is_string($val) ? trim($val) : $val;
        // =============================================================

        // Jika setelah di-trim ternyata stringnya kosong, perlakukan sebagai NULL
        if ($trimmedValue === '') {
            $colvalue = new stdClass();
            $colvalue->col = $col;
            $colvalue->val = null;
            $this->setval[] = $colvalue;
            return $this;
        }

        $finalValue = $trimmedValue;
        if (stripos($col, 'date') !== false && is_string($finalValue)) {
            try {
                $dateObj = new DateTime($finalValue);
                if (strpos($finalValue, ':') !== false) { 
                    $finalValue = $dateObj->format('Y-m-d H:i:s');
                } else {
                    $finalValue = $dateObj->format('Y-m-d');
                }
            } catch (Exception $e) {
                $finalValue = $trimmedValue;
            }
        }
        
        $colvalue = new stdClass();
        $colvalue->col = $col;
        $colvalue->val = $finalValue;
        $this->setval[] = $colvalue;

        return $this;
    }

    function query()
    {
        $this->cnval = count($this->setval);
        return $this;
    }

	public function delete($showcommand = null)
	{
		
		if (empty($this->mbosetname)) {
			throw new Exception("The table for the DELETE operation has not been specified. Please call MboSetRemote() first.");
		}

		
		$whereClause = $this->_buildWhereClause();

		if (empty(trim($whereClause))) {
			throw new Exception("DELETE operation without a WHERE clause is not allowed to prevent deleting the entire table.");
		}

		$this->sql = "DELETE FROM " . $this->mbosetname . $whereClause;

		if ($showcommand == "showcommand") {
			echo $this->sql . "<hr>";
		}
		//echo $this->sql;
		DB::exec($this->sql, $this->conn);

		$this->where = null;
		$this->whereClause = null;
		$this->whereParams = [];
		$this->resetState();
		return $this;
	}

	
	public function remove($showcommand = null)
	{
		
		return $this->delete($showcommand);
	}
	
	
	
	function truncate($showcommand = null)
    {
        if ($this->mboset) {
            $this->sql = "truncate table " . $this->mboset;
            if ($showcommand == "showcommand") {
                echo $this->sql;
            }
            $e = DB::exec($this->sql, $this->conn);

            $this->setval = [];
            $col = [];
            $val = [];

            return $this;
        }
    }
    function change($showcommand = "no")
	{
		if (empty($this->setval)) {
			// Tidak ada yang perlu diubah, langsung kembali
			return $this;
		}

		$colval = [];
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();

		foreach ($this->setval as $cv) {
			$colname = $cv->col;
			$colvalue = $cv->val;
			$formatted_value = "";

			if ($colvalue === null) {
				$formatted_value = "NULL";
			} else {
				// 2. Lakukan escaping yang benar per database
				$escaped_value = '';
				if ($dbtype == 'mysql') {
					$escaped_value = $this->conn->real_escape_string($colvalue);
				} else { // Oracle dan SQL Server menggunakan '' untuk escape
					$escaped_value = str_replace("'", "''", $colvalue);
				}

				// 3. Handle tipe data spesifik (seperti tanggal)
				$isDate = (DateTime::createFromFormat("Y-m-d G:i:s", (string)$colvalue) !== false);

				if ($isDate) {
					if ($dbtype == "oracle") {
						$formatted_value = "TO_DATE('" . $escaped_value . "', 'yyyy-mm-dd hh24:mi:ss')";
					} elseif ($dbtype == "sqlserver") {
						// Gunakan format aman untuk SQL Server untuk menghindari ambiguitas
						$date = new DateTime($escaped_value);
						$formatted_value = "'" . $date->format('Y-m-d\TH:i:s') . "'";
					} else { // MySQL
						$formatted_value = "'" . $escaped_value . "'";
					}
				} else {
					// Untuk non-tanggal (string, angka, dll)
					$formatted_value = "'" . $escaped_value . "'";
				}
			}
			
			$colval[] = $colname . " = " . $formatted_value;
		}

		$colvals = implode(", ", $colval);
		$this->colvals = $colvals;

		// Pastikan ada klausa WHERE untuk mencegah update seluruh tabel secara tidak sengaja
		$whereClause = $this->_buildWhereClause(); // Gunakan method yang aman
		if (empty($whereClause)) {
            $this->sql = "update " . $this->mboset . " set " . $colvals;
			throw new Exception("Unsafe UPDATE attempted without a WHERE clause on table '" . $this->mboset ." SQL : " . $this->sql);
		}
		$this->sql = "update " . $this->mboset . " set " . $colvals . " " . $whereClause;
		

		if ($showcommand == "showcommand") {
			echo $this->sql . "<hr>";
		}
        
		$rowsAffected = DB::exec($this->sql, $this->conn);

		// Reset state setelah eksekusi
		$this->setval = [];
		$colval = [];
		$this->resetState();
		return $this;
	}
	   
   function add($showcommand = "no")
	{
		if (empty($this->setval)) {
			return $this; // Tidak ada yang ditambahkan
		}
		
		$cols_arr = [];
		$vals_arr = [];
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();

		foreach ($this->setval as $cv) {
			$cols_arr[] = $cv->col; 
			$colvalue = $cv->val;
			$formatted_value = "";

			if ($colvalue === null) {
				$formatted_value = "NULL";
			} else {
				$escaped_value = '';
				if ($dbtype == 'mysql') {
					$escaped_value = $this->conn->real_escape_string($colvalue);
				} else { 
					$escaped_value = str_replace("'", "''", $colvalue);
				}

				$isDate = (DateTime::createFromFormat("Y-m-d G:i:s", (string)$colvalue) !== false);

				if ($isDate) {
					if ($dbtype == "oracle") {
						$formatted_value = "TO_DATE('" . $escaped_value . "', 'yyyy-mm-dd hh24:mi:ss')";
					} elseif ($dbtype == "sqlserver") {
						$date = new DateTime($escaped_value);
						$formatted_value = "'" . $date->format('Y-m-d\TH:i:s') . "'";
					} else { 
						$formatted_value = "'" . $escaped_value . "'";
					}
				} else {
					$formatted_value = "'" . $escaped_value . "'";
				}
			}
			$vals_arr[] = $formatted_value;
		}

		$cols = implode(", ", $cols_arr);
		$vals = implode(", ", $vals_arr);

		$this->sql = null;
		$this->getTableID = null;

		if ($dbtype == "mysql") {
			$this->sql = "insert into " . $this->mboset . "(" . $cols . ") values(" . $vals . ")";
			DB::exec($this->sql, $this->conn);
			if ($this->conn && property_exists($this->conn, 'insert_id')) {
				$this->getTableID = $this->conn->insert_id;
			}
		}
		elseif ($dbtype == "sqlserver") {
			$this->sql = "insert into " . $this->mboset . "(" . $cols . ") values(" . $vals . "); SELECT SCOPE_IDENTITY() as last_id;";
			$stmt = sqlsrv_query($this->conn, $this->sql);
			if ($stmt === false) { throw new Exception("SQL Server error: " . print_r(sqlsrv_errors(), true)); }
			sqlsrv_next_result($stmt);
			sqlsrv_fetch($stmt);
			$this->getTableID = sqlsrv_get_field($stmt, 0);
		}
		elseif ($dbtype == "oracle") {
			
			$idColumnName = strtoupper($this->mboset . "id");
			$returnVar = ":new_id";
			$this->sql = "insert into " . $this->mboset . "(" . $cols . ") values(" . $vals . ") RETURNING " . $idColumnName . " INTO " . $returnVar;
			
			$stmt = oci_parse($this->conn, $this->sql);
			if (!$stmt) { $e = oci_error($this->conn); throw new Exception("Oracle parse error: " . htmlentities($e['message'])); }
			
			oci_bind_by_name($stmt, $returnVar, $this->getTableID, 4000); 
			
			if (!oci_execute($stmt)) { $e = oci_error($stmt); throw new Exception("Oracle execute error: " . htmlentities($e['message'])); }
		}
		
       
		if ($showcommand == "showcommand") { echo $this->sql . "<hr>"; }
		$this->setval = [];
        $this->resetState();
		return $this;
	}	
		
	function getStatement($type)
    {
        $dbo = new dbo();
        $dbtype = $dbo->dbtype();
        if ($type == "insert") {
            $col = [];
            $val = [];
            $this->cnval = count($this->setval);
            $dbo = new dbo();
            $dbtype = $dbo->dbtype();

            for ($i = 0; $i < $this->cnval; $i++) {
                $a = explode("·", $this->setval[$i]);
                array_push($col, $a[0]);

                $myString = addslashes($a[1]);
                if ($dbtype == "oracle") {
                    if (
                        DateTime::createFromFormat("Y-m-d G:i:s", $myString) !==
                        false
                    ) {
                        array_push(
                            $val,
                            "TO_DATE('" .
                                $myString .
                                "', 'yyyy-mm-dd hh24:mi:ss')"
                        );
                    } else {
                        if (empty($myString)) {
                            array_push($val, "null");
                        } else {
                            array_push($val, "'" . $myString . "'");
                        }
                    }
                }
                if ($dbtype == "mysql" || $dbtype == "sqlserver") {
                    if (empty($myString)) {
                        array_push($val, "null");
                    } else {
                        array_push($val, "'" . $myString . "'");
                    }
                    array_push($val, "'" . $myString . "'");
                }
            }
            $tableid = $this->mboset . "id";
            $tablevalueid = Xbo::getMaxTableID($this->mboset);
            //array_push($col, $tableid);
            //array_push($val, $tablevalueid);
            $cols = implode(",", $col);
            $vals = implode(",", $val);

            $this->sqlcols = $cols;
            $this->sqlvals = $vals;
            $this->sql =
                "insert into " .
                $this->mboset .
                "(" .
                $this->sqlcols .
                ") values(" .
                $this->sqlvals .
                ")";
        }
        if ($type == "update") {
            $colval = [];
            $this->cnval = count($this->setval);
            for ($i = 0; $i < $this->cnval; $i++) {
                $a = explode("·", $this->setval[$i]);
                $myString = addslashes($a[1]);
                if ($dbtype == "oracle") {
                    if (
                        DateTime::createFromFormat("Y-m-d G:i:s", $myString) !==
                        false
                    ) {
                        array_push(
                            $colval,
                            $a[0] .
                                " = TO_DATE('" .
                                $myString .
                                "', 'yyyy-mm-dd hh24:mi:ss')"
                        );
                    } else {
                        array_push($colval, $a[0] . "='" . $myString . "'");
                    }
                }
                if ($dbtype == "mysql" || $dbtype == "sqlserver") {
                    array_push($colval, $a[0] . "='" . $myString . "'");
                }
            }
            $colvals = implode(",", $colval);
            $this->colvals = $colvals;
            $this->sql =
                "update " .
                $this->mboset .
                " set " .
                $colvals .
                " " .
                $this->where;
        }
        if ($type == "delete") {
            $this->sql = "delete from  " . $this->mboset . " " . $this->where;
        }

        $this->sqlcols = "";
        $this->sqlvals = "";
        $this->colvals = "";
        $this->setval = [];
        $colval = [];
        return $this->sql;
    }
	public function getAllColumnNamesAsArray($tableName)
	{
		$columnNames = [];
		$this->getAllColumns($tableName);
		$resultSet = $this->result;
		if ($resultSet) {
			while ($row = $resultSet->fetch()) {
				$columnNames[] = $row['columnname'];
			}
		}
		return $columnNames;
	}
	public function fetchOneField($tableName, $columnToFetch, $criteria = [])
	{
		if (empty($criteria)) {
			// Tidak aman atau tidak diinginkan mengambil data tanpa filter
			return null;
		}

		$whereParts = [];
		$paramValues = [];

		// 1. Bangun klausa WHERE dan parameter dengan aman
		foreach ($criteria as $key => $value) {
			// Handle logika bisnis spesifik Anda di sini
			if ($key === 'tonodeline') {
				$key = 'nodeline';
			}
			
			$whereParts[] = "`" . $key . "` = ?"; // Gunakan placeholder (?)
			$paramValues[] = $value; // Kumpulkan nilainya
		}

		$whereClause = implode(" AND ", $whereParts);

		// 2. Jalankan kueri dengan method where() yang aman dan limit(1)
		$resultSet = $this->MboSetRemote($tableName)
						 ->where($whereClause, ...$paramValues) // Parameterized query, AMAN!
						 ->MboRemote($columnToFetch) // Hanya minta kolom yang kita butuhkan
						 ->limit(1) // Sangat penting untuk efisiensi!
						 ->getMboSet();
						 
		// 3. Ambil datanya
		$dataRow = $resultSet->fetch();
		
		// 4. Kembalikan nilainya
		if ($dataRow && isset($dataRow[$columnToFetch])) {
			return $dataRow[$columnToFetch];
		}
		
		return null;
	}
	   

   
   
    function fetchFields()
	{
		
		if (!$this->resultSet) {
			return []; 
		}

		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$columnNames = [];

		if ($dbtype == "mysql") {
			while ($fieldInfo = mysqli_fetch_field($this->resultSet)) {
				$columnNames[] = $fieldInfo->name;
			}
		} 
		elseif ($dbtype == "oracle") {
			
			$numFields = oci_num_fields($this->resultSet);
			for ($i = 1; $i <= $numFields; $i++) {
				$columnNames[] = strtolower(oci_field_name($this->resultSet, $i));
			}
		} 
		elseif ($dbtype == "sqlserver") {
			$fieldMetadata = sqlsrv_field_metadata($this->resultSet);
			if (is_array($fieldMetadata)) {
				foreach ($fieldMetadata as $field) {
					$columnNames[] = $field['Name'];
				}
			}
		}

		return $columnNames;
	}
    function countFields()
	{
		if (!$this->resultSet) {
			return 0;
		}

		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$nCols = 0; 

		if ($dbtype == "mysql") {
			$nCols = mysqli_num_fields($this->resultSet);
		} 
		elseif ($dbtype == "oracle") {
			$nCols = oci_num_fields($this->resultSet);
		} 
		elseif ($dbtype == "sqlserver") {
			
			$nCols = sqlsrv_num_fields($this->resultSet);
		}

		return (int) $nCols;
	}
    function getMboSet($rd = "", $sqlimport = "")
    {
        ini_set("max_execution_time", 6000);
        global $strings;
        $strings = [];
        $this->mysqlerror = null;
        
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();

		if ($sqlimport == "") {
			$join_str = isset($this->leftjoin) ? $this->leftjoin : "";
			$select_str = isset($this->getstring) ? $this->getstring : "*";
			$where_str = $this->_buildWhereClause();
			$group_str = isset($this->groupby) ? $this->groupby : "";
			$order_str = isset($this->order) ? $this->order : "";
			
			$limitVal = isset($this->limit) ? trim(str_ireplace('limit', '', $this->limit)) : '';

			$selectClause = "select ";
			if ($rd == "remove_duplicate" || $rd == "distinct") {
				$selectClause .= "distinct ";
			}
			
			if ($dbtype == "sqlserver" && !empty($limitVal)) {
				$selectClause .= "TOP " . $limitVal . " ";
			}
			
			$selectClause .= $select_str;
            $this->hvng = $this->_buildHavingClause();

			$this->mboset = strtolower($this->mboset);

			// Bangun kueri menggunakan variabel lokal
			$this->sql = $selectClause . " from " . $this->mboset . " " . 
						   $join_str . $where_str . $group_str . $this->hvng. $order_str;

            
			
            if (($dbtype === 'sqlserver' || $dbtype === 'oracle') && $this->rowCount !== null) {
                if (stripos($this->sql, 'ORDER BY') === false) {
                    throw new Exception("ORDER BY clause is required for pagination in SQL Server/Oracle.");
                }
                $this->sql .= " OFFSET {$this->offset} ROWS FETCH NEXT {$this->rowCount} ROWS ONLY";
            } else if ($dbtype === 'mysql' && isset($this->limit)) {
                $this->sql .= $this->limit; 
            }
			
			if ($rd == "stacked") {
				$this->stacked = "stacked";
			}
			if ($rd == "showcommand") {
				echo "<hr>" . $this->sql;
			}
			
		} else {
			if ($this->conn === null) {
				$this->conn = DB::getMXConnection();
			}
			$this->sql = $sqlimport;
            $this->sql = $this->fixTableNameCase($this->sql);
		}

        if ($dbtype == "oracle") {
            $this->resultSet = oci_parse($this->conn, $this->sql);
            oci_execute($this->resultSet);
        }
        
        if ($dbtype == "mysql") {
            if (isset($this->stacked)) {
                if ($this->stacked == null) {
                    $this->resultSet = $this->conn->query($this->sql);
                } elseif ($this->stacked == "stacked") {
                    $this->resultSet = $this->resultSet;
                }
            } else {
               try{
                     $this->resultSet = $this->conn->query($this->sql);
               }catch (Exception $e) {
                    error_log("Error: " . $e->getMessage());
                    return true; 
                }
            }

            if (!$this->resultSet) {
                $errno = $this->conn->errno;
                $connerror = $this->conn->error;
                
                if ($errno == null || $connerror == null) {
                } else {
                    $err_msg = "DATABASE_ERROR $errno $connerror your SQL Query is : " . $this->sql;
                    throw new Exception($err_msg);
                }
            }

            if (!$this->useTransactionManager) {
               // $this->conn->close();
            }
        }

        if ($dbtype == "sqlserver") {
            $this->resultSet = sqlsrv_query(
                $this->conn,
                $this->sql,
                [],
                ["Scrollable" => SQLSRV_CURSOR_KEYSET]
            );
        }
		
        //$this->joins = [];
        //$this->leftjoin = "";
        //$this->where = "";
        //$this->whr = "";
        
        // Reset juga klausa where yang baru setelah dieksekusi
        //$this->whereClause = null;
        //$this->whereParams = [];
        $this->resetState();

        if ($this->mysqlerror != null) {
            Set::throws(["msg" => $this->mysqlerror]);
        } else {
            return $this;
        }
        
        return $this;
    }
	
	
	
	public function isEmpty()
	{
		
		try {
			$dbo = new dbo();
			$dbtype = $dbo->dbtype();
			
			if ($dbtype == "oracle") {
				oci_execute($this->resultSet, OCI_DEFAULT);
				
				// Cek apakah ada data
				$row = oci_fetch_array($this->resultSet, OCI_ASSOC);
				oci_execute($this->resultSet, OCI_DEFAULT);
				
				return $row === false; // false berarti tidak ada data
			}
			
			if ($dbtype == "mysql" || $dbtype == "sqlserver") {
				
				if ($this->resultSet) {
					// Case 1: MySQL result dengan num_rows
					if (method_exists($this->resultSet, 'num_rows')) {
						
						return $this->resultSet->num_rows === 0;
					} 
					// Case 2: SQL Server resource
					else if (function_exists('sqlsrv_num_rows') && is_resource($this->resultSet)) {
						
						return sqlsrv_num_rows($this->resultSet) === 0;
					}
					// Case 3: Coba fetch data untuk mengecek ketersediaan
					else {
						// Untuk MySQL mysqli result
						if (method_exists($this->resultSet, 'fetch_assoc')) {
							// Simpan posisi current
							$current_pos = $this->resultSet->data_seek(0);
							$row = $this->resultSet->fetch_assoc();
							// Reset ke posisi awal
							$this->resultSet->data_seek(0);
							return $row === null;
						}
						// Untuk SQL Server
						else if (function_exists('sqlsrv_fetch_array')) {
							$row = sqlsrv_fetch_array($this->resultSet, SQLSRV_FETCH_ASSOC);
							// Reset cursor jika memungkinkan
							if (function_exists('sqlsrv_free_stmt')) {
								// Note: Ini akan memerlukan re-execute query
							}
							return $row === false;
						}
						// Untuk PDO statement
						else if (method_exists($this->resultSet, 'rowCount')) {
							return $this->resultSet->rowCount() === 0;
						}
						// Jika tidak ada method yang cocok, coba fetch generic
						else if (method_exists($this->resultSet, 'fetch')) {
							$row = $this->resultSet->fetch();
							return $row === false;
						}
						// Fallback: jika tidak bisa menentukan, return true (anggap kosong)
						return true;
					}
				} else {
					return true; // Jika $this->mboset tidak ada, anggap kosong
				}
			}
			
			return true;
			
		} catch (Exception $e) {
			error_log("isEmpty Error: " . $e->getMessage());
			return true; 
		}
	}
    function counts()
    {
      
        if ($this->resultSet) {
            $dbo = new dbo();
            $dbtype = $dbo->dbtype();

            if ($dbtype == "mysql") {
                return mysqli_num_rows($this->resultSet);
            }
            if ($dbtype == "sqlserver") {
                $num_rows = sqlsrv_num_rows($this->resultSet);
                if ($num_rows !== false) {
                    return $num_rows;
                }
            }
        }
        // 1. Bangun komponen kueri tanpa ORDER BY atau SELECT
        $join_str = isset($this->leftjoin) ? $this->leftjoin : "";
        $where_str = $this->_buildWhereClause();
        $group_str = isset($this->groupby) ? $this->groupby : "";

  
        // Jika ada GROUP BY, kita harus menghitung jumlah baris dari hasil subquery
        if (!empty($group_str)) {
            $count_sql = "SELECT COUNT(*) as total_rows FROM (SELECT 1 as dummy_col FROM " . $this->mbosetname . " " . $join_str . $where_str . $group_str . ") as subquery";
        } else {
            $count_sql = "SELECT COUNT(*) as total_rows FROM " . $this->mbosetname . " " . $join_str . $where_str;
        }

        // 3. Eksekusi kueri COUNT yang ringan
        $result = self::fetchAll($count_sql);
        
        return isset($result[0]['total_rows']) ? (int)$result[0]['total_rows'] : 0;
    }
    public function getMboValue(string $columnName, $defaultValue = null)
    {
  
        $this->getMboSet();
        $firstRow = $this->fetch();
        if ($firstRow && array_key_exists($columnName, $firstRow)) {
            return $firstRow[$columnName];
        }
        return $defaultValue;
    }

    public function getMbo()
	{
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$rowData = null; // Default value

		if ($dbtype == "oracle") {
			$rowData = oci_fetch_assoc($this->resultSet);
			if ($rowData) {
				$this->data = array_change_key_case($rowData, CASE_LOWER);
			} else {
				$this->data = false; 
			}
		}
		if ($dbtype == "mysql") {
			$this->data = $this->resultSet ? $this->resultSet->fetch_assoc() : null;
		}
		if ($dbtype == "sqlserver") {
			$this->data = sqlsrv_fetch_array($this->resultSet, SQLSRV_FETCH_ASSOC);
		}
		
	
		return $this;
	}
	
	public function fetch()
	{
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$rowData = null; 

		if ($dbtype == "oracle") {
			$rowData = oci_fetch_assoc($this->resultSet);
			if ($rowData) {
				return array_change_key_case($rowData, CASE_LOWER);
			}
		}
		if ($dbtype == "mysql") {
			$rowData = $this->resultSet ? $this->resultSet->fetch_assoc() : null;
		}
		if ($dbtype == "sqlserver") {
			$rowData = sqlsrv_fetch_array($this->resultSet, SQLSRV_FETCH_ASSOC);
		}

		return $rowData; 
	}
	public function fetchData()
	{
		//  Pastikan kueri sudah dieksekusi.
		// Jika getMboSet() belum pernah dipanggil, panggil sekarang.
		if ($this->resultSet === null) {
			$this->getMboSet();
		}
		
		if (!$this->resultSet) {
			return [];
		}
		
		//  Loop menggunakan method fetch() yang sudah ada.
		$data = [];
		while ($row = $this->fetch()) { // fetch() akan mengambil satu baris per satu
			// Kita bisa tambahkan lagi logic pembersihan di sini jika perlu,
			// meskipun seharusnya sudah ditangani di level getString().
			$data[] = $row;
		}

		// Reset pointer result set jika memungkinkan (best practice untuk MySQL)
		$dbo = new dbo();
		if ($dbo->dbtype() == 'mysql' && count($data) > 0) {
			$this->resultSet->data_seek(0);
		}
		
		return $data;
	}

    /**
     * Mengambil semua hasil kueri dan mengembalikannya sebagai associative array,
     * di mana kuncinya adalah nilai dari kolom yang ditentukan.
     * */
    public function fetchDataAssoc(string $keyColumn): array
    {
      
        $firstRow = $this->fetch();
        if ($firstRow === null) {
            return [];
        }

        // Jika tidak ada di sini, kemungkinan besar tidak ada di baris lain juga.
        if (!isset($firstRow[$keyColumn])) {
            if (method_exists($this->resultSet, 'data_seek')) {
                $this->resultSet->data_seek(0);
            }
            throw new Exception("Key Column '$keyColumn' not found in result set query.");
        }
        
        $data = [];
        $data[$firstRow[$keyColumn]] = $firstRow;

        while ($row = $this->fetch()) {
            $data[$row[$keyColumn]] = $row;
        }

        return $data;
    }
    public function fetchDataAssoc2(string $keyColumn) {
        $data = [];
        while ($row = $this->fetch()) {
            if (isset($row[$keyColumn])) {
                $data[$row[$keyColumn]] = $row;
            }
        }
        return $data;
    }


    public function fetchDataAssoc3(string $keyColumn): array
    {
        // Gunakan method fetchData() yang sudah ada untuk mengambil semua data.
        $allData = $this->fetchData();
        if (empty($allData)) {
            return [];
        }

        // Validasi - Pastikan kolom kunci ada di hasil pertama.
        $firstRow = $allData[0];
        if (!array_key_exists($keyColumn, $firstRow)) {
            throw new Exception("Key Column '$keyColumn' not found in query result for fetchDataAssoc.");
        }

        $associativeData = array_column($allData, null, $keyColumn);

        return $associativeData;
    }

    public function countRecords(): int
    {
        if (empty($this->mbosetname)) {
            throw new Exception("Table Name is not set for counting the records");
        }

        $counterSet = new Set();
        $counterSet->MboSetRemote($this->mbosetname);

        $counterSet->joins = $this->joins;
        $counterSet->leftjoin = $this->leftjoin;
        $counterSet->whereConditions = $this->whereConditions;
        $counterSet->whereParams = $this->whereParams;
        $counterSet->where = $this->where; // Fallback

        $count = $counterSet->MboRemote("COUNT(*) as total_records")
                            ->getMboValue("total_records", 0); 
        
        return (int)$count;
    }

	public static function fetchAll($sql, $params = [])
	{
		$set = new Set();
		$set->useTransactionManager();
		$conn = $set->conn; 
		$dbtype = (new dbo())->dbtype();
		
		
		if (!empty($params)) {
			$i = 0;
			while (($pos = strpos($sql, '?')) !== false) {
				if (!array_key_exists($i, $params)) {
					throw new Exception('The number of parameters does not match the number of placeholders (?) in the SQL statement.');
				}
				
				$param = $params[$i];
				$escapedParam = '';

				// Handle tipe data yang berbeda
				if (is_null($param)) {
					$escapedParam = 'NULL';
				} elseif (is_numeric($param)) {
					$escapedParam = $param; // Angka tidak perlu kutip
				} else {
					// String, lakukan escaping berdasarkan tipe database
					if ($dbtype == 'mysql') {
						$escapedParam = "'" . $conn->real_escape_string((string)$param) . "'";
					} else { // Oracle dan SQL Server
						$escapedParam = "'" . str_replace("'", "''", (string)$param) . "'";
					}
				}
				
				$sql = substr_replace($sql, $escapedParam, $pos, 1);
				$i++;
			}
            //echo $sql;
            //exit;
			// Safety check: pastikan semua parameter sudah digunakan
			if ($i < count($params)) {
				 throw new Exception('The number of parameters does not match the number of placeholders (?) in the SQL statement.');
			}
		}
		
        $dbtype = (new dbo())->dbtype();
        if ($dbtype == 'mysql') {
            $sql = preg_replace_callback(
                '/(FROM|JOIN|UPDATE|INTO)\s+([`A-Za-z0-9_]+)/i', 
                function($matches) {
                    return strtoupper($matches[1]) . " " . strtolower($matches[2]);
                }, 
                $sql
            );
        }
        
        
        $resultSet = $set->getMboSet("", $sql);
		$data = [];
		while ($row = $resultSet->fetch()) {
			$cleanedRow = [];
			foreach ($row as $col => $value) {
				if ($value !== null) {
					$cleanedValue = preg_replace('/[\x00-\x1F\x7F-\xFF]/', ' ', $value);
					$cleanedRow[$col] = $cleanedValue;
				} else {
					$cleanedRow[$col] = null;
				}
			}
			$data[] = $cleanedRow;
		}
        
		return $data;
	}


    function getErrorMessage()
    {
        $dbo = new dbo();
        $dbtype = $dbo->dbtype();
        if ($dbtype == "oracle") {
            $this->msg = oci_error($this->conn);
            oci_close($this->conn);
        }
        if ($dbtype == "mysql") {
            $this->msg = mysqli_error($conn);
            $this->conn->close();
        }
        if ($dbtype == "sqlserver") {
            $this->msg = sqlsrv_errors($conn);
            sqlsrv_close($this->conn);
        }

        return $this->msg;
    }
	
    function getString($s, $formatdate = null)
    {
      
        if (!isset($this->data[$s])) {
           
            $s_lower = strtolower($s);
            if (!isset($this->data[$s_lower])) {
                return null;
            }
            $s = $s_lower;
        }
        
        $rawValue = $this->data[$s];

        // JARING PENGAMAN UTAMA: Jika nilainya null atau kosong, langsung kembalikan null.
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // Jika nilainya adalah objek (misal: tanggal dari SQL Server)
        if (is_object($rawValue)) {
            if ($rawValue instanceof DateTime) {
                $format = $formatdate ?? 'Y-m-d H:i:s';
                return $rawValue->format($format);
            }
            // Handle objek lain jika ada (misal: LOB dari Oracle)
            if (method_exists($rawValue, 'load')) {
                return $rawValue->load();
            }
            // Fallback: coba konversi ke string
            return (string) $rawValue;
        }

        // Jika nilainya adalah string, bersihkan dan trim.
        if (is_string($rawValue)) {
            // Ganti karakter kontrol dengan string kosong, BUKAN spasi.
            $cleanedString = preg_replace('/[\x00-\x1F\x7F]/', '', $rawValue);
            // Hapus spasi di awal dan akhir.
            return trim($cleanedString);
        }

        // Untuk tipe data lain (angka, dll.), kembalikan apa adanya.
        return $rawValue;
    }
	 function getDecimal($s, $decimalPlaces = null)
    {
        $stringValue = $this->getString($s);

        if ($stringValue === null || $stringValue === '') {
            return null; 
        }

        if (is_numeric($stringValue)) {
            $floatValue = (float) $stringValue; 

            if ($decimalPlaces !== null && is_int($decimalPlaces)) {
                return round($floatValue, $decimalPlaces); 
            }

            return $floatValue; 
        } else {
            error_log("Warning: Value for key '$s' is not a valid decimal: " . $stringValue);
            return null; 
        }
    }
	function getInt($s)
    {
        $stringValue = $this->getString($s);

        if ($stringValue === null || $stringValue === '') {
            return null; 
        }

        if (is_numeric($stringValue)) {
            return (int) $stringValue; 
        } else {
            error_log("Warning: Value for key '$s' is not a valid integer: " . $stringValue);
            return null; 
        }
    }

     public function getDate(string $columnName, string $format = 'Y-m-d H:i:s'): ?string
    {
       
        if (!isset($this->data[$columnName])) {
            return null;
        }
        $rawValue = $this->data[$columnName];

        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // 3. Handle nilai tanggal invalid dari MySQL.
        if ($rawValue === '0000-00-00' || $rawValue === '0000-00-00 00:00:00') {
            return null;
        }
        
        // 4. Cek apakah nilainya sudah berupa objek DateTime (umum dari driver sqlsrv).
        if ($rawValue instanceof DateTime) {
         
            return $rawValue->format($format);
        }

        // 5. Jika bukan, coba parsing sebagai string.
        try {
    
            $dateObj = new DateTime((string)$rawValue);
            return $dateObj->format($format);
        } catch (Exception $e) {
            error_log("Failed to Parse Date '$columnName'. with the value: " . $rawValue);
            return null;
        }
    }

 
    public function getDateTimeObject(string $columnName): ?DateTime
    {

        $dateString = $this->getDate($columnName);

        if ($dateString) {
            try {
                return new DateTime($dateString);
            } catch (Exception $e) {
                // Seharusnya tidak akan pernah terjadi karena getDate() sudah memvalidasi,
                return null;
            }
        }

        return null;
    }

	
	public function getColumnResult($XboSet)
	{
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$dbname = $dbo->dbname();
		
		// Langsung buat objek Set yang akan kita gunakan dan kembalikan
		$this->result = new Set();
		
		// Sanitasi input
		$XboSet = preg_replace("/[^a-zA-Z0-9_]/", "", $XboSet);

		if ($dbtype == "oracle") {
			$XboSet = strtoupper($XboSet);
			// Langsung panggil method pada objek $resultSet
			$this->result->MboSetRemote("USER_TAB_COLUMNS")
					  ->setWhere("table_name = '$XboSet'")
					  ->MboRemote("column_name as columnname")
					  ->getMboSet();
		}
		elseif ($dbtype == "mysql") {
			$this->result->MboSetRemote("information_schema.columns")
					  ->setWhere("table_name = '$XboSet' and table_schema='$dbname'")
					  ->MboRemote("column_name as columnname")
					  ->getMboSet();
		}
		elseif ($dbtype == "sqlserver") {
			$this->result->MboSetRemote("information_schema.columns")
					  ->setWhere("TABLE_NAME = '$XboSet' AND TABLE_CATALOG = '$dbname'")
					  ->MboRemote("COLUMN_NAME as columnname")
					  ->getMboSet();
		}
		
		// Kembalikan objek Set yang sudah berisi hasil kueri
		return $this->result;
	}
    function getAllColumns($XboSet)
	{
		$this->mboset = $XboSet;
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$dbname = $dbo->dbname();
		$set = new Set();
	
		$XboSet = preg_replace("/[^a-zA-Z0-9_]/", "", $XboSet);

		if ($dbtype == "oracle") {
			$XboSet = strtoupper($XboSet);
			$this->result = $set
				->MboSetRemote("USER_TAB_COLUMNS")
				->setWhere("table_name = '$XboSet'")
				->MboRemote("column_name as columnname")
				->getMboSet();
		}
		if ($dbtype == "mysql") {
			$this->result = $set
				->MboSetRemote("information_schema.columns")
				->setWhere("table_name = '$XboSet' and table_schema='$dbname'")
				->MboRemote("column_name as columnname")
				->getMboSet();
		}
	
		if ($dbtype == "sqlserver") {
			$this->result = $set
				->MboSetRemote("information_schema.columns")
				->setWhere("TABLE_NAME = '$XboSet' AND TABLE_CATALOG = '$dbname'")
				->MboRemote("COLUMN_NAME as columnname")
				->getMboSet();
		}

		$this->columnname = "columnname";
		return $this;
	}

    public static function getDataType($XboSet, $column)
	{
		$dbo = new dbo();
		$dbtype = $dbo->dbtype();
		$dbname = $dbo->dbname();
		$conn = DB::getMXConnection();
		$datatype = null; // Default value

		// Sanitasi sederhana untuk mencegah masalah dasar
		$XboSet = preg_replace("/[^a-zA-Z0-9_]/", "", $XboSet);
		$column = preg_replace("/[^a-zA-Z0-9_]/", "", $column);

		try {
			if ($dbtype == "mysql") {
				$sql = "SELECT data_type FROM information_schema.columns WHERE table_schema = '$dbname' AND table_name = '$XboSet' AND column_name = '$column'";
				$result = $conn->query($sql);
				if ($result && $result->num_rows > 0) {
					$datatype = $result->fetch_assoc()["data_type"];
				}
			} 
			else if ($dbtype == "sqlserver") {
				$sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG = '$dbname' AND TABLE_NAME = '$XboSet' AND COLUMN_NAME = '$column'";
				// Eksekusi untuk SQL Server
				$stmt = sqlsrv_query($conn, $sql);
				if ($stmt && sqlsrv_has_rows($stmt)) {
					$datatype = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)["DATA_TYPE"];
				}
			} 
			else if ($dbtype == "oracle") {
				$XboSet = strtoupper($XboSet);
				$column = strtoupper($column);
				
				$sql = "SELECT DATA_TYPE FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '$XboSet' AND COLUMN_NAME = '$column'";
				// Eksekusi untuk Oracle
				$stmt = oci_parse($conn, $sql);
				oci_execute($stmt);
				if (($row = oci_fetch_assoc($stmt)) != false) {
					$datatype = $row['DATA_TYPE'];
				}
			}
		} finally {
			// Pastikan koneksi ditutup, kecuali jika dikelola oleh TransactionManager
			// Untuk amannya, kita serahkan penutupan koneksi pada skrip utama atau TransactionManager
			// $conn->close(); // Mungkin lebih baik tidak ditutup di sini
		}

		return $datatype;
	}
}

class JWT
{
    private $str;
    function _construct()
    {
        $this->jwt = "";
    }
    function headr($x)
    {
        $this->headr = base64_encode($x);
        return $this;
    }
    function payload($x)
    {
        $this->payload = base64_encode($x);
        return $this;
    }
    function signature($x)
    {
        $pl = $this->headr . "." . $this->payload;
        $xx = utf8_encode($x);
        $this->signature = base64_encode(hash_hmac("sha256", $pl, $xx, true));
        return $this;
    }
    function generate()
    {
        $this->jwt =
            $this->headr . "." . $this->payload . "." . $this->signature;
        return $this->jwt;
    }
    function decode($x)
    {
        $y = base64_decode($x);
        $z = explode(".", $y);
        $this->headr = base64_decode($z[0]);
        $this->payload = base64_decode($z[1]);
        $this->signature = $z[2];
        return $this;
    }
    function getIssuer()
    {
        $set = new Set();

        $mxstracking = $set
            ->MboSetRemote("mxstracking")
            ->MboRemote("rowstamp")
            ->getMboSet()
            ->getMbo();
        $dt = $mxstracking->getString("rowstamp");

        $setDec = new Super();

        $upreg = $setDec->decodeUpregTracking($dt);

        $super = new Super();

        $rwst = $super->breakapart($upreg);

        if ($rwst->pl->iss == "javarave.com") {
            $syspath = Super::setSysPath() . DIRECTORY_SEPARATOR;
            $sysfile = $syspath . "system.xml";

            if (file_exists($sysfile)) {
                $mtime = filectime($sysfile);

                if ($mtime == $rwst->pl->ts) {
                    $this->ret = "ls";
                } else {
                    $this->ret = "sx";
                    unlink($sysfile);
                }
            } else {
                $this->ret = "sx";
            }
        } else {
            $this->ret = "mxe";
        }
        return $this;
    }
    function getPstat($jsonArr = "")
    {
        if ($this->ret == "ls") {
            $this->ast = "\"ros\":\"Registered Original JVR Maxes\"";
        } else {
            $this->ast =
                "\"ros\":\"[font color=red][h3]This copy of JVR Maxes is NOT GENUINE[/h3][/font][br]You may be a victim of software counterfeiting. [br]Some functions will go abnormal\"";
        }
        array_push($jsonArr, $this->ast);
        $this->jsonval = "{" . implode(",", $jsonArr) . "}";
        return $this;
    }
}
class MXMail
{
    public static function sendout($l)
    {
        $mail = new PHPMailer();
        $mail->isHTML(true);
        $mail->SMTPDebug = 2;

        $o = (object) $l;

        if (empty($o->fromAlias)) {
            $o->fromAlias = "Maxes Administrator";
        }
        if (empty($o->toAlias)) {
            $o->toAlias = "Maxes Recipients";
        }
        if (empty($o->path)) {
            $o->path = "";
        }
        if (empty($o->filename)) {
            $o->filename = "";
        }
        if (empty($o->AddCC)) {
            $o->AddCC = "";
        }
        if (empty($o->emaillogsid)) {
            $o->emaillogsid = "";
        }

        $mail->setFrom($o->fromEmail, $o->fromAlias);

        if ($o->toEmail != "") {
            if (strpos($o->toEmail, ",") !== false) {
                $toArr = explode(",", $o->toEmail);
                for ($i = 0; $i < count($toArr); $i++) {
                    $mail->addAddress($toArr[$i], $o->fromAlias);
                }
            } else {
                $mail->addAddress($o->toEmail, $o->toAlias);
            }
        }

        if ($o->AddCC != "") {
            if (strpos($o->AddCC, ",") !== false) {
                $ccArr = explode(",", $o->AddCC);
                for ($i = 0; $i < count($ccArr); $i++) {
                    $mail->AddCC($ccArr[$i]);
                }
            } else {
                $mail->AddCC($o->AddCC);
            }
        }

        $ftype = mime_content_type($o->path);
        if (!empty($ftype)) {
            $mail->AddAttachment($o->path, $o->filename, "base64", $ftype);
        }

        $msge = str_replace("[", "<", $o->toMessage);
        $msgq = str_replace("]", ">", $msge);

        $mail->Subject = $o->toSubject;
        $mail->Body = $msgq;

        if (!$mail->send()) {
            $errorinfo = $mail->ErrorInfo;
            $set = new Set();
            $set->MboSetRemote("emaillogs")
                ->setWhere("emaillogsid='" . $o->emaillogsid . "'")
                ->setValue("status", "FAILED")
                ->setValue("errormsg", $errorinfo)
                ->change();
        } else {
            $errorinfo = $mail->ErrorInfo;
            $set = new Set();
            $set->MboSetRemote("emaillogs")
                ->setWhere("emaillogsid='" . $o->emaillogsid . "'")
                ->setValue("status", "SUCCESS")
                ->change();
        }
    }
}



function globalExceptionHandler($exception) {
    // Cek apakah sudah ada output sebelumnya
    if (ob_get_length()) {
        ob_clean(); 
    }
    
    // CRITICAL: Hanya output JSON, tidak ada HTML
    header('Content-Type: application/json');
    
    echo json_encode([
        "error" => $exception->getMessage(),
        "file" => $exception->getFile(),
        "line" => $exception->getLine()
    ]);
    
    // Log untuk debugging (tidak tampil di response)
    error_log("Uncaught exception: " . $exception->getMessage());
    
    exit(1);
}



?>