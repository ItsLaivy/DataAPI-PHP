<?php

/** @noinspection SqlDialectInspection */
/** @noinspection SqlNoDataSourceInspection */

namespace ItsLaivy\DataAPI\Modules\SQL\SQLite;

use Exception;
use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\DatabaseType;
use ItsLaivy\DataAPI\Modules\Receptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLReceptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLTable;
use ItsLaivy\DataAPI\Modules\Variable;
use Throwable;

class SQLiteDatabaseType extends DatabaseType {

    private readonly string $path;

    public function __construct(string $path) {
        parent::__construct("SQLITE");

        $this->path = $path;
    }

    public function suppressedErrors(): array {
        return array(2003, 2004);
    }

    public function query(SQLiteDatabase $database, string $query): void {
        try {
            $database->query($query);
        } catch (Throwable $e) {
            $this->throws($e);
        }
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

<<<<<<< Updated upstream
    public function data(Database $database, Receptor $receptor): array {
        if (!($database instanceof SQLiteDatabase)) return array();
        return $database->query("SELECT * FROM '" . $receptor->getTable()->getName() . "' WHERE bruteid = '". $receptor->getBruteId() ."'")->results();
    }

    public function receptorLoad(Database $database, Receptor $receptor): void {
        if (!($database instanceof SQLiteDatabase)) return;

        $assoc = $this->data($database, $receptor);
        if (empty($assoc)) {
            $this->query($database, "INSERT INTO '" . $receptor->getTable()->getName() . "' (name,bruteid,last_update) VALUES ('".$receptor->getName()."','".$receptor->getBruteId()."','".getAPIDate()."')");
            $assoc = $this->data($database, $receptor);
=======
    public function data(SQLReceptor|Receptor $receptor): array {
        return $receptor->getTable()->getDatabase()->query("SELECT * FROM '" . $receptor->getTable()->getName() . "' WHERE bruteid = '". $receptor->getBruteId() ."'")->results();
    }

    /**
     * @throws Throwable
     */
    public function receptorLoad(SQLReceptor|Receptor $receptor): void {
        $assoc = $this->data($receptor);
        if (empty($assoc)) {
            $this->query($receptor->getTable()->getDatabase(), "INSERT INTO '" . $receptor->getTable()->getName() . "' (name,bruteid,last_update) VALUES ('".$receptor->getName()."','".$receptor->getBruteId()."','".parent::getAPIDate()."')");
            $assoc = $this->data($receptor);
>>>>>>> Stashed changes
            $receptor->setNew(true);
        }

        $row = 0;
        foreach ($assoc as $key => $value) {
            if ($row == 0) $receptor->setId($value); // ID

            if ($row > 3) {
                $receptor->getVariables()[$key] = unserialize($value);
            }
            $row++;
        }
    }

<<<<<<< Updated upstream
    public function receptorDelete(Database $database, Receptor $receptor): void {
        if (!($database instanceof SQLiteDatabase)) return;
        $this->query($database, "DELETE FROM '".$receptor->getTable()->getName()."' WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    public function save(Database $database, Receptor $receptor): void {
        if (!($database instanceof SQLiteDatabase)) return;

=======
    /**
     * @throws Throwable
     */
    public function receptorDelete(SQLReceptor|Receptor $receptor): void {
        $this->query($receptor->getTable()->getDatabase(), "DELETE FROM '".$receptor->getTable()->getName()."' WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    /**
     * @throws Throwable
     */
    public function save(SQLReceptor|Receptor $receptor): void {
>>>>>>> Stashed changes
        $query = "";
        foreach ($receptor->getVariables() as $key => $value) {
            $query = $query . "`".$key."`='".serialize($value)."',";
        }
        $query = $query . "`last_update`='".parent::getAPIDate()."'";

        $this->query($database, "UPDATE '".$receptor->getTable()->getName()."' SET ".$query." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

<<<<<<< Updated upstream
    public function tableLoad(Database $database, Table $table): void {
        if (!($database instanceof SQLiteDatabase)) return;

=======
    /**
     * @throws Throwable
     */
    public function tableLoad(SQLTable $table): void {
>>>>>>> Stashed changes
        try {
            $this->query($database, "CREATE TABLE '".$table->getName()."' ('id' INTEGER PRIMARY KEY AUTOINCREMENT, 'name' TEXT, bruteid TEXT, 'last_update' TEXT);");
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), "table '".$table->getName()."' already exists")) {
                $e = new exception("Já existe uma tabela criada com o nome '".$table->getName()."'", 2003);
            }
            $this->throws($e);
        }
    }

<<<<<<< Updated upstream
    public function tableDelete(Database $database, Table $table): void {
        if (!($database instanceof SQLiteDatabase)) return;
        $this->query($database, "DROP TABLE '".$table->getName() . "'");
=======
    /**
     * @throws Throwable
     */
    public function tableDelete(SQLTable $table): void {
        $this->query($table->getDatabase(), "DROP TABLE '".$table->getName() . "'");
>>>>>>> Stashed changes
    }

    public function variableLoad(Database $database, Variable $variable): void {
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

    public function variableDelete(Database $database, Variable $variable): void {
        if (!($database instanceof SQLiteDatabase)) return;
        //throw new exception("A função de deletar variáveis ainda não está disponível para SQLite!");
    }

    public function databaseLoad(Database $database): void {
        if (!($database instanceof SQLiteDatabase)) return;

        if (!file_exists($this->path)) mkdir($this->path, 0777, true);

        $database->open();
        $database->getConnection(true)->enableExceptions(true);
    }

    public function databaseDelete(Database $database): void {
        if (!($database instanceof SQLiteDatabase)) return;
        unlink($database->getFileName());
    }

    public function open(): void {
        // TODO: Implement open() method.
    }

    public function close(): void {
        // TODO: Implement close() method.
    }
}