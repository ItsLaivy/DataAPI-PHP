<?php /** @noinspection SqlNoDataSourceInspection */
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use Exception;
use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\DatabaseType;
use ItsLaivy\DataAPI\Modules\Query\DataResult;
use ItsLaivy\DataAPI\Modules\Receptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLReceptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLTable;
use ItsLaivy\DataAPI\Modules\Variable;
use mysqli;
use Throwable;

class MySQLDatabaseType extends DatabaseType {

    private mysqli $treeConnection;
    private bool $conn_opened = false;

    public readonly string $user;
    public readonly string $password;
    public readonly int $port;
    public readonly string $address;

    public function __wakeup(): void {
        $this->open();
    }
    public function __sleep(): array {
        $this->close();
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

    public function open(): void {
        if ($this->conn_opened) {
            return;
        }

        $this->treeConnection = new mysqli($this->address, $this->user, $this->password, null, $this->port);

        if ($this->treeConnection->connect_errno) {
            throw new exception("Não foi possível conectar-se ao banco de dados MySQL: '" . $this->treeConnection->connect_error . "'");
        }
        $this->treeConnection->set_charset("utf8");
        $this->conn_opened = true;
    }
    public function close(): void {
        if (!$this->conn_opened) {
            return;
        }

        try {
            mysqli_close($this->getTreeConnection(false));
            $this->conn_opened = false;
        } catch (Throwable $e) {
            if ($e->getMessage() != 'mysqli object is already closed') {
                $this->throws($e);
            }
        }
    }

    /**
     * @param bool $safe Se a conexão estiver fechada e este parâmetro for true, abrirá novamente
     * @return mysqli A conexão do banco de dados principal
     */
    public function getTreeConnection(bool $safe): mysqli {
        if ($safe && !$this->conn_opened) {
            $this->open();
        }
        return $this->treeConnection;
    }

    public function suppressedErrors(): array {
        return array(1007, 1050, 1060);
    }

<<<<<<< Updated upstream
    public function statement(MySQLDatabase $database, string $query): MySQLStatement {
        $_SESSION['dataapi']['log']['queries'][$database->getName()] += 1;
=======
    /**
     * @throws Throwable
     */
    public function statement(Database $database, string $query): MySQLStatement {
>>>>>>> Stashed changes
        return new MySQLStatement($database, $query);
    }
    public function query(MySQLDatabase $database, string $query): DataResult {
        return $this->statement($database, $query)->execute();
    }

<<<<<<< Updated upstream
    public function data(Database $database, Receptor $receptor): array {
        if (!($database instanceof MySQLDatabase)) return array();
        return $this->query($database, "SELECT * FROM ".$database->getName().".".$receptor->getTable()->getName()." WHERE bruteid = '". $receptor->getBruteId() ."'")->results();
    }

    public function receptorLoad(Database $database, Receptor $receptor): void {
        if (!($database instanceof MySQLDatabase)) return;

        $assoc = $this->data($database, $receptor);
        if (empty($assoc)) {
            $this->query($database, "INSERT INTO ".$database->getName().".".$receptor->getTable()->getName()." (name,bruteid,last_update) VALUES ('".$receptor->getName()."','".$receptor->getBruteId()."','".getAPIDate()."')");
            $assoc = $this->data($database, $receptor);
=======
    /**
     * @throws Throwable
     */
    public function data(SQLReceptor|Receptor $receptor): array {
        return $this->query($receptor->getTable()->getDatabase(), "SELECT * FROM ".$receptor->getTable()->getDatabase()->getName().".".$receptor->getTable()->getName()." WHERE bruteid = '". $receptor->getBruteId() ."'")->results();
    }

    /**
     * @throws Throwable
     */
    public function receptorLoad(SQLReceptor|Receptor $receptor): void {
        $assoc = $this->data($receptor);
        if (empty($assoc)) {
            $this->query($receptor->getTable()->getDatabase(), "INSERT INTO ".$receptor->getTable()->getDatabase()->getName().".".$receptor->getTable()->getName()." (name,bruteid,last_update) VALUES ('".$receptor->getName()."','".$receptor->getBruteId()."','".parent::getAPIDate()."')");
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
    public function receptorDelete(MySQLDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "DELETE FROM ".$database->getName().".".$receptor->getTable()->getName()." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    public function save(MySQLDatabase|Database $database, Receptor $receptor): void {
        if (!($database instanceof MySQLDatabase)) return;
=======
    /**
     * @throws Throwable
     */
    public function receptorDelete(SQLReceptor|Receptor $receptor): void {
        $this->query($receptor->getTable()->getDatabase(), "DELETE FROM ".$receptor->getTable()->getDatabase()->getName().".".$receptor->getTable()->getName()." WHERE bruteid = '".$receptor->getBruteId()."'");
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

        $this->query($database, "UPDATE ".$database->getName().".".$receptor->getTable()->getName()." SET ".$query." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

<<<<<<< Updated upstream
    public function tableLoad(MySQLDatabase|Database $database, Table $table): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "CREATE TABLE ".$database->getName().".".$table->getName()." (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128), bruteid VARCHAR(128), last_update VARCHAR(21));");
    }

    public function tableDelete(MySQLDatabase|Database $database, Table $table): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "DROP TABLE ".$database->getName().".".$table->getName());
=======
    /**
     * @throws Throwable
     */
    public function tableLoad(SQLTable $table): void {
        $this->query($table->getDatabase(), "CREATE TABLE ".$table->getDatabase()->getName().".".$table->getName()." (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128), bruteid VARCHAR(128), last_update VARCHAR(21));");
    }

    /**
     * @throws Throwable
     */
    public function tableDelete(SQLTable $table): void {
        $this->query($table->getDatabase(), "DROP TABLE ".$table->getDatabase()->getName().".".$table->getName());
>>>>>>> Stashed changes
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