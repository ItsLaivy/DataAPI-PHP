<?php /** @noinspection SqlNoDataSourceInspection */
namespace DataAPI\MySQL;

require_once("MySQLDatabase.php");
require_once("MySQLStatement.php");
require_once("MySQLResult.php");

use DataAPI\System\Database;
use DataAPI\System\DatabaseType;
use DataAPI\System\DataResult;
use DataAPI\System\InactiveVariable;
use DataAPI\System\Receptor;
use DataAPI\System\Table;
use DataAPI\System\Variable;
use mysql_xdevapi\Exception;
use mysqli;
use Throwable;
use function DataAPI\System\getAPIDate;

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
            throw new Exception("Não foi possível conectar-se ao banco de dados MySQL: '" . $this->treeConnection->connect_error . "'");
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

    public function statement(MySQLDatabase $database, string $query): MySQLStatement {
        $_SESSION['dataapi']['log']['queries'][$database->getName()] += 1;
        return new MySQLStatement($database, $query);
    }
    public function query(MySQLDatabase $database, string $query): DataResult {
        return $this->statement($database, $query)->execute();
    }

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
            $receptor->setNew(true);
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