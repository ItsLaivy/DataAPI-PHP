<?php

/** @noinspection SqlDialectInspection */
/** @noinspection SqlNoDataSourceInspection */

namespace ItsLaivy\DataAPI\Modules\SQL\SQLite;

use Exception;
use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\Receptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLDatabaseType;
use ItsLaivy\DataAPI\Modules\SQL\SQLReceptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLTable;
use ItsLaivy\DataAPI\Modules\SQL\SQLVariable;
use ItsLaivy\DataAPI\Modules\Variable;
use Throwable;

class SQLiteDatabaseType extends SQLDatabaseType {

    private readonly string $path;

    public function __construct(string $path) {
        parent::__construct("SQLITE");

        $this->path = $path;
    }

    public function suppressedErrors(): array {
        return array(2003, 2004);
    }

    public function query(SQLiteDatabase|Database $database, string $query): void {
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
            $receptor->setNew(true);
        }

        $row = 0;
        foreach ($assoc as $key => $value) {
            if ($row == 0) $receptor->setId($value); // ID

            // If the unserialize method cannot be performed it will use the string value
            set_error_handler(function($errno, $errstr, $errfile, $errline){
                if($errno === E_WARNING){
                    trigger_error($errstr, E_ERROR);
                    return true;
                } else {
                    return false;
                }
            });
            //

            if ($row > 3) {
                try {
                    $unserialized = @unserialize($value);
                    $receptor->getVariables()[$key] = $unserialized;
                } catch (exception) {
                    $receptor->getVariables()[$key] = $value;
                }
            }

            // Return to the old handler
            restore_error_handler();
            //

            $row++;
        }
    }

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
        $query = "";
        foreach ($receptor->getVariables() as $key => $value) {
            if (!array_key_exists($key, Variable::$VARIABLES)) {
                throw new exception("Cannot save variable '".$key."' because the instance of this variable cannot be found.");
            } $var = Variable::$VARIABLES[$key];

            if (!$var->isSerialize() && !method_exists($value, "__toString")) {
                throw new exception("Cannot save variable '".$key."' because the variable isn't serializable and value cannot be converted as a string!");
            }

            $query = $query . "`" . $key . "`='" . ($var->isSerialize() ? serialize($value) : $value->__toString()) . "',";
        }
        $query = $query . "`last_update`='".parent::getAPIDate()."'";

        $this->query($receptor->getTable()->getDatabase(), "UPDATE '".$receptor->getTable()->getName()."' SET ".$query." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    /**
     * @throws Throwable
     */
    public function tableLoad(SQLTable $table): void {
        try {
            $this->query($table->getDatabase(), "CREATE TABLE '".$table->getName()."' ('id' INTEGER PRIMARY KEY AUTOINCREMENT, 'name' TEXT, bruteid TEXT, 'last_update' TEXT);");
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), "table '".$table->getName()."' already exists")) {
                $e = new exception("Já existe uma tabela criada com o nome '".$table->getName()."'", 2003);
            }
            $this->throws($e);
        }
    }

    /**
     * @throws Throwable
     */
    public function tableDelete(SQLTable $table): void {
        $this->query($table->getDatabase(), "DROP TABLE '".$table->getName() . "'");
    }

    public function variableLoad(SQLVariable|Variable $variable): void {
        try {
            $this->query($variable->getTable()->getDatabase(), "ALTER TABLE '".$variable->getTable()->getName()."' ADD COLUMN '".$variable->getName()."' TEXT DEFAULT '". ($variable->isSerialize() ? serialize($variable->getDefault()) : strval($variable->getDefault())) ."';");
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), "duplicate column name: ".$variable->getName())) {
                $e = new exception("Já existe uma coluna criada com o nome '".$variable->getName()."'", 2004);
            }
            $this->throws($e);
        }
    }

    public function variableDelete(SQLVariable|Variable $variable): void {
        //throw new exception("A função de deletar variáveis ainda não está disponível para SQLite!");
    }

    public function databaseLoad(SQLiteDatabase|Database $database): void {
        if (!file_exists($this->path)) mkdir($this->path, 0777, true);

        $database->open();
        $database->getConnection(true)->enableExceptions(true);
    }

    public function databaseDelete(SQLiteDatabase|Database $database): void {
        unlink($database->getFileName());
    }

    public function open(): void {
        // TODO: Implement open() method.
    }

    public function close(): void {
        // TODO: Implement close() method.
    }
}