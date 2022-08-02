<?php
namespace DataAPI\System;

use Exception;
use mysqli;
use SQLite3;

abstract class SQLDatabase extends Database {

    abstract function statement(string $query): DataStatement;
    abstract function query(string $query): DataResult;

    abstract function open();
    abstract function close();

}

class SQLiteDatabase extends SQLDatabase {

    public SQLite3 $connection;

    public function __construct(SQLiteDatabaseType $type, string $name) {
        parent::__construct($type, $name);
    }

    public function statement(string $query): DataStatement {
        $_SESSION['dataapi']['log']['queries'][parent::getName()] += 1;
        return new SQLiteStatement($this, $query);
    }
    public function query(string $query): DataResult {
        $stmt = $this->statement($query);
        $result = $stmt->execute();
        return $result;
    }

    public function __sleep(): array {
        return parent::__sleep();
    }
    public function __wakeup(): void {
        $dbFileName = parent::getName() . ".db";
        if (isset($this->connection)) $this->connection->open($dbFileName);
    }

    public function getFileName(): string {
        return $this->databaseType->path . "/" . $this->getName() . ".db";
    }

    function open() {
        // TODO: Implement open() method.
    }

    function close() {
        // TODO: Implement close() method.
    }
}

class MySQLDatabase extends SQLDatabase {

    public mysqli $connection;

    protected readonly string $user;
    protected readonly string $password;
    protected readonly int $port;
    protected readonly string $address;

    public function __construct(MySQLDatabaseType $type, string $name) {
        parent::__construct($type, $name);

        $this->user = $type->user;
        $this->password = $type->password;
        $this->port = $type->port;
        $this->address = $type->address;

        $this->connection = new mysqli($type->address, $this->user, $this->password, $name, $this->port);
    }

    public function statement(string $query): DataStatement {
        $_SESSION['dataapi']['log']['queries'][$this->getName()] += 1;
        return new MySQLStatement($this, $query);
    }
    public function query(string $query): DataResult {
        $stmt = $this->statement($query);
        $result = $stmt->execute();
        return $result;
    }

    function open() {
        $this->connection = new mysqli($this->getDatabaseType()->address, $this->getDatabaseType()->user, $this->getDatabaseType()->password, $this->getName(), $this->getDatabaseType()->port);
    }

    function close() {
    }

    public function __wakeup(): void {
        $this->open();
    }

    public function __sleep(): array {
        $super = parent::__sleep();
        array_push($super, 'user','password','port','address');
        $this->close();
        return $super;
    }

}

abstract class Database {

    protected readonly DatabaseType $databaseType;
    protected readonly string $name;

    /**
     * @throws exception caso o banco de dados já exista
     */
    public function __construct(DatabaseType $databaseType, string $name) {
        $this->databaseType = $databaseType;
        $this->name = $name;

        if (isset($_SESSION['dataapi']['databases'][$databaseType->getName()][$name])) {
            if (EXISTS_ERROR) throw new exception("já existe um banco de dados criado com esse tipo de conexão e nome");
            return;
        }

        $_SESSION['dataapi']['log']['queries'][$name] = 0;

        $databaseType->databaseLoad($this);

        $_SESSION['dataapi']['databases'][$databaseType->getName()][$name] = serialize($this);
        $_SESSION['dataapi']['tables'][$this->getIdentification()] = array();
        $_SESSION['dataapi']['log']['created']['databases'] += 1;
    }

    public function __sleep(): array {
        return array(
            'databaseType',
            'name',
        );
    }

    public function delete(): void {
        foreach ($this->getTables() as $name => $table) {
            $table->delete();
        }
        $this->getDatabaseType()->databaseDelete($this);
    }

    public function getTables(): array {
        return $_SESSION['dataapi']['tables'][$this->getIdentification()];
    }

    /**
     * Foi feito para uso interno, é usado para armazenar nas variáveis sem precisar saltar o objeto inteiro
     */
    public function getIdentification(): string {
        return $this->getDatabaseType()->getName() . "-" . $this->getName();
    }

    /**
     * @return DatabaseType tipo do banco de dados
     */
    public function getDatabaseType(): DatabaseType {
        return $this->databaseType;
    }

    /**
     * @return string nome
     */
    public function getName(): string {
        return $this->name;
    }

}