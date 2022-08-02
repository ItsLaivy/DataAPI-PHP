<?php
namespace DataAPI\System;

use mysqli;
use mysqli_result;
use mysqli_stmt;
use Throwable;

class MySQLDatabaseType extends DatabaseType {

    protected mysqli $treeConnection;

    public readonly string $user;
    public readonly string $password;
    public readonly int $port;
    public readonly string $address;

    public function __wakeup(): void {
        $this->open();
    }
    public function __sleep(): array {
        return array('user','password','port','address');
    }

    public function __construct(string $user, string $password, int $port, string $address) {
        parent::__construct("MYSQL");

        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->address = $address;

        $this->open();
    }

    public function open() {
        $this->treeConnection = new mysqli($this->address, $this->user, $this->password, null, $this->port);
    }

    public function suppressedErrors(): array {
        return array(1007, 1050, 1060);
    }

    private function query(MySQLDatabase $database, string $query): void {
        try {
            $_SESSION['dataapi']['log']['queries'][$database->getName()] += 1;
            $stmt = $this->treeConnection->prepare($query);
            $stmt->execute();
        } catch (Throwable $e) {
            $this->throws($e);
        }
    }

    public function data(MySQLDatabase|Database $database, Receptor $receptor): array {
        if (!($database instanceof MySQLDatabase)) return array();
        return $database->query("SELECT * FROM " . $receptor->getTable()->getName() . " WHERE bruteid = '". $receptor->getBruteId() ."'")->results();
    }

    public function receptorLoad(MySQLDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof MySQLDatabase)) return;

        $assoc = $this->data($database, $receptor);
        if (empty($assoc)) {
            $this->query($database, "INSERT INTO ".$database->getName().".users (name,bruteid,last_update) VALUES ('".$receptor->getName()."','".$receptor->getBruteId()."','".getAPIDate()."')");
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

    public function receptorDelete(MySQLDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "DELETE FROM ".$database->getName().".".$receptor->getTable()->getName()." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    public function save(MySQLDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof MySQLDatabase)) return;

        $query = "";
        foreach ($receptor->getActiveVariables() as $variable) {
            $query = $query . "`".$variable->getVariable()->getName()."`='".serialize($variable->getData())."',";
        }
        $query = $query . "`last_update`='".getAPIDate()."'";

        $this->query($database, "UPDATE ".$database->getName().".".$receptor->getTable()->getName()." SET ".$query." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    public function tableLoad(MySQLDatabase|Database $database, Table $table): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "CREATE TABLE ".$database->getName().".".$table->getName()." (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128), bruteid VARCHAR(128), last_update VARCHAR(21));");
    }

    public function tableDelete(MySQLDatabase|Database $database, Table $table): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "DROP TABLE ".$database->getName().".".$table->getName());
    }

    public function variableLoad(MySQLDatabase|Database $database, Variable $variable): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "ALTER TABLE ".$database->getName().".".$variable->getTable()->getName()." ADD COLUMN ".$variable->getName()." MEDIUMTEXT DEFAULT '".serialize($variable->getDefault())."';");
    }

    public function variableDelete(MySQLDatabase|Database $database, Variable $variable): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "ALTER TABLE ".$database->getName().".".$variable->getTable()->getName()." DROP COLUMN ".$variable->getName());
    }

    public function databaseLoad(MySQLDatabase|Database $database): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "CREATE DATABASE ".$database->getName());
    }

    public function databaseDelete(MySQLDatabase|Database $database): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "DROP DATABASE ".$database->getName());
    }
}

class MySQLStatement extends DataStatement {

    private readonly mysqli_stmt $statement;

    public function __construct(MySQLDatabase $database, string $query) {
        parent::__construct($database, $query);

        try {
            $this->statement = $database->connection->prepare($query);
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    public function execute(): DataResult {
        $result = null;
        try {
            $this->statement->execute();
            $result = $this->statement->get_result();
            if ($result === false) $result = null;
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
        return new MySQLResult($result);
    }

    public function close(): void {
        try {
            $this->statement->close();
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    public function bindParameters(string $param, mixed $var): void {
        try {
            $this->statement->bind_param($param, $var);
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

}
class MySQLResult extends DataResult {

    private readonly mysqli_result $result;

    public function __construct(null|mysqli_result $result) {
        if ($result != null) {
            $this->result = $result;
        }
    }

    public function columns(): int {
        if (isset($this->result)) {
            return $this->result->num_rows;
        } else {
            return 0;
        }
    }

    public function results(): array {
        if (isset($this->result)) {
            $result = $this->result->fetch_assoc();
            if (is_array($result)) {
                return $result;
            }
        }
        return array();
    }

}