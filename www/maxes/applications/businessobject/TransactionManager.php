<?php

class TransactionManager {
    private static $instance = null;
    private $connection = null;
    private $transactionActive = false;
    private $dbtype = null;

    private function __construct() {
        $dbo = new dbo();
        $this->dbtype = $dbo->dbtype();
        $this->connection = DB::getMXConnection();
    }

    public static function getInstance() {
		
        if (self::$instance === null) {
			
            self::$instance = new TransactionManager();
            register_shutdown_function([self::$instance, 'closeConnection']);
        }
        return self::$instance;
    }
    /**
     * Memeriksa apakah sebuah transaksi sedang aktif.
     * @return bool True jika transaksi aktif, false jika tidak.
     */
    public function isTransactionActive() {
        return $this->transactionActive;
    }
    public function closeConnection() {
        if ($this->connection) {
            if ($this->dbtype == "mysql") {
                $this->connection->close();
            }
            $this->connection = null;
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    
    public function beginTransaction() {
        if (!$this->transactionActive) {
            if ($this->dbtype == "mysql") {
                $this->connection->begin_transaction();
            } elseif ($this->dbtype == "sqlserver") {
                sqlsrv_begin_transaction($this->connection);
            }
            $this->transactionActive = true;
        }
    }

    public function commit() {
        if ($this->transactionActive) {
            if ($this->dbtype == "mysql") {
                $this->connection->commit();
            } elseif ($this->dbtype == "sqlserver") {
                sqlsrv_commit($this->connection);
            } elseif ($this->dbtype == "oracle") {
                oci_commit($this->connection);
            }
            $this->transactionActive = false;
        }
    }

    public function rollback() {
        if ($this->transactionActive) {
            if ($this->dbtype == "mysql") {
                $this->connection->rollback();
            } elseif ($this->dbtype == "sqlserver") {
                sqlsrv_rollback($this->connection);
            } elseif ($this->dbtype == "oracle") {
                oci_rollback($this->connection);
            }
            $this->transactionActive = false;
        }
    }
    
    public function getDBType() {
        return $this->dbtype;
    }
}


?>