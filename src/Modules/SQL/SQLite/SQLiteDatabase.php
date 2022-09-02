<?php
namespace ItsLaivy\DataAPI\Modules\SQL\SQLite;

use ItsLaivy\DataAPI\Modules\SQL\SQLDatabase;
use SQLite3;

class SQLiteDatabase extends SQLDatabase {

    private SQLite3 $connection;
    private bool $conn_opened = false;

    public function __construct(SQLiteDatabaseType $type, string $name) {
        parent::__construct($type, $name);
    }

    public function statement(string $query): SQLiteStatement {
        return new SQLiteStatement($this, $query);
    }
    public function query(string $query): SQLiteResult {
        $stmt = $this->statement($query);
        return $stmt->execute();
    }

    public function getFileName(): string {
        return $this->databaseType->getPath() . "/" . $this->getName() . ".db";
    }

    /**
     * @return SQLite3 a conexão
     */
    public function getConnection(bool $safe): SQLite3 {
        if ($safe && !$this->conn_opened) {
            $this->open();
        }
        return $this->connection;
    }

    function open() {
        if (!$this->conn_opened) {
            $this->connection = new SQLite3($this->getFileName());
            $this->conn_opened = true;
        }
    }

    function close() {
        if ($this->conn_opened) {
            $this->connection->close();
            $this->conn_opened = false;
        }
    }
}