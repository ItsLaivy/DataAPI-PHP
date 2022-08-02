<?php
namespace DataAPI\System;

use Exception;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;
use Throwable;

class SQLiteDatabaseType extends DatabaseType {

    public readonly string $path;

    public function __construct(string $path) {
        parent::__construct("SQLITE");

        $this->path = $path;
    }

    public function suppressedErrors(): array {
        return array(2003, 2004);
    }

    private function query(SQLiteDatabase $database, string $query): void {
        try {
            $database->query($query);
        } catch (Throwable $e) {
            $this->throws($e);
        }
    }

    public function data(SQLiteDatabase|Database $database, Receptor $receptor): array {
        if (!($database instanceof SQLiteDatabase)) return array();
        return $database->query("SELECT * FROM '" . $receptor->getTable()->getName() . "' WHERE bruteid = '". $receptor->getBruteId() ."'")->results();
    }

    public function receptorLoad(SQLiteDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof SQLiteDatabase)) return;

        $assoc = $this->data($database, $receptor);
        if (empty($assoc)) {
            $this->query($database, "INSERT INTO 'users' (name,bruteid,last_update) VALUES ('".$receptor->getName()."','".$receptor->getBruteId()."','".getAPIDate()."')");
            $assoc = $this->data($database, $receptor);
        }

        $row = 0;
        foreach ($assoc as $key => $value) {
            if ($row == 0) $receptor->setId($value); // ID

            if ($row > 3) {
                new InactiveVariable($receptor, $key, $value);
            }
            $row++;
        }
    }

    public function receptorDelete(SQLiteDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof SQLiteDatabase)) return;
        $this->query($database, "DELETE FROM '".$receptor->getTable()->getName()."' WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    public function save(SQLiteDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof SQLiteDatabase)) return;

        $query = "";
        foreach ($receptor->getActiveVariables() as $variable) {
            $query = $query . "`".$variable->getVariable()->getName()."`='".serialize($variable->getData())."',";
        }
        $query = $query . "`last_update`='".getAPIDate()."'";

        $this->query($database, "UPDATE '".$receptor->getTable()->getName()."' SET ".$query." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    public function tableLoad(SQLiteDatabase|Database $database, Table $table): void {
        if (!($database instanceof SQLiteDatabase)) return;

        try {
            $this->query($database, "CREATE TABLE '".$table->getName()."' ('id' INTEGER PRIMARY KEY AUTOINCREMENT, 'name' TEXT, bruteid TEXT, 'last_update' TEXT);");
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), "table '".$table->getName()."' already exists")) {
                $e = new exception("Já existe uma tabela criada com o nome '".$table->getName()."'", 2003);
            }
            $this->throws($e);
        }
    }

    public function tableDelete(SQLiteDatabase|Database $database, Table $table): void {
        if (!($database instanceof SQLiteDatabase)) return;
        $this->query($database, "DROP TABLE '".$table->getName() . "'");
    }

    public function variableLoad(SQLiteDatabase|Database $database, Variable $variable): void {
        if (!($database instanceof SQLiteDatabase)) return;

        try {
            $this->query($database, "ALTER TABLE '".$variable->getTable()->getName()."' ADD COLUMN '".$variable->getName()."' TEXT DEFAULT '".serialize($variable->getDefault())."';");
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), "duplicate column name: ".$variable->getName())) {
                $e = new exception("Já existe uma coluna criada com o nome '".$variable->getName()."'", 2004);
            }
            $this->throws($e);
        }
    }

    public function variableDelete(SQLiteDatabase|Database $database, Variable $variable): void {
        if (!($database instanceof SQLiteDatabase)) return;
        //throw new exception("A função de deletar variáveis ainda não está disponível para SQLite!");
    }

    public function databaseLoad(SQLiteDatabase|Database $database): void {
        if (!($database instanceof SQLiteDatabase)) return;

        if (!file_exists($this->path)) mkdir($this->path, 0777, true);

        $database->connection = new SQLite3($database->getFileName());
        $database->connection->enableExceptions(true);
    }

    public function databaseDelete(SQLiteDatabase|Database $database): void {
        if (!($database instanceof SQLiteDatabase)) return;
        unlink($database->getFileName());
    }
}

class SQLiteStatement extends DataStatement {

    private readonly SQLite3Stmt $statement;

    public function __construct(SQLiteDatabase $database, string $query) {
        parent::__construct($database, $query);

        try {
            $stmt = $database->connection->prepare($query);
            if ($stmt === false) {
                $this->getDatabase()->getDatabaseType()->throwsDirectly($this->getDatabase()->connection->lastErrorCode(), $this->getDatabase()->connection->lastErrorMsg());
            } else {
                $this->statement = $stmt;
            }
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    public function execute(): DataResult {
        if (!isset($this->statement)) {
            if (DEBUG) echo "Não foi possível realizar isso, pois o statement não foi criado com sucesso<br>";
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
            if (DEBUG) echo "Não foi possível realizar isso, pois o statement não foi criado com sucesso<br>";
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
class SQLiteResult extends DataResult {

    private readonly SQLite3Result $result;

    public function __construct(null|SQLite3Result $result) {
        if (isset($result)) {
            $this->result = $result;
        }
    }

    public function columns(): int {
        if (isset($this->result)) {
            return $this->result->numColumns();
        } else {
            return 0;
        }
    }

    public function results(): array {
        if (isset($this->result)) {
            $result = $this->result->fetchArray(SQLITE3_ASSOC);
            if ($result !== false) {
                return $result;
            }
        }
        return array();
    }

}