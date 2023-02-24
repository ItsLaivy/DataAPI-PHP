<?php /** @noinspection SqlNoDataSourceInspection */
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use Error;
use Exception;
use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\Query\DataResult;
use ItsLaivy\DataAPI\Modules\Receptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLDatabaseType;
use ItsLaivy\DataAPI\Modules\SQL\SQLReceptor;
use ItsLaivy\DataAPI\Modules\SQL\SQLTable;
use ItsLaivy\DataAPI\Modules\SQL\SQLVariable;
use ItsLaivy\DataAPI\Modules\Variable;
use ItsLaivy\DataAPI\Modules\Variables\InactiveVariable;
use mysqli;
use Throwable;

class MySQLDatabaseType extends SQLDatabaseType {

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
        return array('user', 'password', 'port', 'address');
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
     * @throws Exception
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

    /**
     * @throws Throwable
     */
    public function statement(MySQLDatabase|Database $database, string $query): MySQLStatement {
        return new MySQLStatement($database, $query);
    }

    public function query(MySQLDatabase|Database $database, string $query): DataResult {
        return $this->statement($database, $query)->execute();
    }

    /**
     * @throws Throwable
     */
    public function data(SQLReceptor|Receptor $receptor): array {
        return $this->query($receptor->getTable()->getDatabase(), "SELECT * FROM " . $receptor->getTable()->getDatabase()->getName() . "." . $receptor->getTable()->getName() . " WHERE bruteid = '" . $receptor->getBruteId() . "'")->results();
    }

    /**
     * @throws Throwable
     */
    public function receptorLoad(SQLReceptor|Receptor $receptor): void {
        $assoc = $this->data($receptor);
        if (empty($assoc)) {
            $this->query($receptor->getTable()->getDatabase(), "INSERT INTO " . $receptor->getTable()->getDatabase()->getName() . "." . $receptor->getTable()->getName() . " (name,bruteid,last_update) VALUES ('" . $receptor->getName() . "','" . $receptor->getBruteId() . "','" . parent::getAPIDate() . "')");
            $assoc = $this->data($receptor);
            $receptor->setNew(true);
        }

        $assoc = $assoc[0];
        
        $row = 0;
        foreach ($assoc as $key => $value) {
            if ($row == 0) $receptor->setId($value); // ID

            if ($row > 3) {
                new InactiveVariable($receptor, $key, $value);
            }

            $row++;
        }
    }

    /**
     * @throws Throwable
     */
    public function receptorDelete(SQLReceptor|Receptor $receptor): void {
        $this->query($receptor->getTable()->getDatabase(), "DELETE FROM " . $receptor->getTable()->getDatabase()->getName() . "." . $receptor->getTable()->getName() . " WHERE bruteid = '" . $receptor->getBruteId() . "'");
    }

    /**
     * @throws Throwable
     */
    public function save(SQLReceptor|Receptor $receptor): void {
        $query = "";
        foreach ($receptor->getActiveVariables() as $variable) {
            $query = $query . "`".$variable->getVariable()->getName()."`='".($variable->getVariable()->isSerialize() ? serialize($variable->getData()) : $variable->getData())."',";
        }
        $query = $query . "`last_update`='".self::getAPIDate()."',`name`='".$receptor->getName()."'";

        $this->query($receptor->getTable()->getDatabase(), "UPDATE ".$receptor->getTable()->getDatabase()->getName().".".$receptor->getTable()->getName()." SET ".$query." WHERE bruteid = '".$receptor->getBruteId()."'");
    }

    /**
     * @throws Throwable
     */
    public
    function tableLoad(SQLTable $table): void {
        $this->query($table->getDatabase(), "CREATE TABLE " . $table->getDatabase()->getName() . "." . $table->getName() . " (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128), bruteid VARCHAR(128), last_update VARCHAR(21));");
    }

    /**
     * @throws Throwable
     */
    public function tableDelete(SQLTable $table): void {
        $this->query($table->getDatabase(), "DROP TABLE " . $table->getDatabase()->getName() . "." . $table->getName());
    }

    public function variableLoad(SQLVariable|Variable $variable): void {
        $this->query($variable->getTable()->getDatabase(), "ALTER TABLE " . $variable->getTable()->getDatabase()->getName() . "." . $variable->getTable()->getName() . " ADD COLUMN " . $variable->getName() . " MEDIUMTEXT DEFAULT '" . ($variable->isSerialize() ? serialize($variable->getDefault()) : strval($variable->getDefault())) . "';");
    }

    public function variableDelete(SQLVariable|Variable $variable): void {
        $this->query($variable->getTable()->getDatabase(), "ALTER TABLE " . $variable->getTable()->getDatabase()->getName() . "." . $variable->getTable()->getName() . " DROP COLUMN " . $variable->getName());
    }

    public function databaseLoad(MySQLDatabase|Database $database): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "CREATE DATABASE " . $database->getName());
    }

    public function databaseDelete(MySQLDatabase|Database $database): void {
        if (!($database instanceof MySQLDatabase)) return;
        $this->query($database, "DROP DATABASE " . $database->getName());
    }
    
    public function receptorsFromVariableValue(SQLVariable $variable, mixed $value): array {
        if ($variable->getDatabase()->getDatabaseType() != $this) {
            throw new Exception("This variable's databasetype isn't the same as requested.");
        }
        
        if ($variable->isSerialize()) {
            $new_value = serialize($value);
        } else {
            if (!method_exists($value, "__toString")) {
                throw new exception("To get the receptors from a variable value, the value param object needs to implement the __toString");
            }
            $new_value = $value->__toString();
        }
    
        return $this->query($variable->getTable()->getDatabase(), "SELECT * FROM " . $variable->getTable()->getDatabase()->getName() . "." . $variable->getTable()->getName() . " WHERE ".$variable->getName()." = '" . $new_value . "'")->results();
    }
}