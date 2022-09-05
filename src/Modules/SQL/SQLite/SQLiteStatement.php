<?php
namespace ItsLaivy\DataAPI\Modules\SQL\SQLite;

use ItsLaivy\DataAPI\Modules\Query\DataStatement;
use SQLite3Stmt;
use Throwable;

class SQLiteStatement extends DataStatement {

    private readonly SQLite3Stmt $statement;

    public function __construct(SQLiteDatabase $database, string $query) {
        parent::__construct($database, $query);

        try {
            $stmt = $database->getConnection(true)->prepare($query);
            if ($stmt === false) {
                $this->getDatabase()->getDatabaseType()->throwsDirectly($this->getDatabase()->getConnection(true)->lastErrorCode(), $this->getDatabase()->getConnection(true)->lastErrorMsg());
            } else {
                $this->statement = $stmt;
            }
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    public function execute(): SQLiteResult {
        if (!isset($this->statement)) {
            return new SQLiteResult(null);
        }

        try {
            return new SQLiteResult($this->statement->execute());
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
        return new SQLiteResult(null);
    }

    public function close(): void {
        if (!isset($this->statement)) {
            return;
        }

        try {
            $this->statement->close();
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    /**
     * @return SQLite3Stmt
     */
    public function getStatement(): SQLite3Stmt {
        return $this->statement;
    }

}
