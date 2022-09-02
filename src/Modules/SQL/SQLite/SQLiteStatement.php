<?php
namespace ItsLaivy\DataAPI\Modules\SQL\SQLite;

use ItsLaivy\DataAPI\Modules\Query\DataStatement;
use SQLite3Stmt;
use Throwable;
use const ItsLaivy\DataAPI\DEBUG;

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
            if (DEBUG) echo "Não foi possível executar o statement do SQLite, pois o statement não foi criado com sucesso<br>";
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
            if (DEBUG) echo "Não foi possível executar o statement do SQLite, pois o statement não foi criado com sucesso<br>";
            return;
        }

        try {
            $this->statement->close();
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    public function bindParameters(string $param, mixed $var): void {
        if (!isset($this->statement)) {
            if (DEBUG) echo "Não foi possível realizar isso, pois o statement não foi criado com sucesso<br>";
            return;
        }

        try {
            $this->statement->bindParam($param, $var);
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

}

?>