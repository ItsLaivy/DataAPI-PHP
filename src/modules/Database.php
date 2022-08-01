<?php
    require_once(dirname(__FILE__).'/../DataAPI.php');

    require_once(dirname(__FILE__).'/sql/MySQLDatabaseType.php');
    require_once(dirname(__FILE__).'/sql/SQLiteDatabaseType.php');

    class SQLiteDatabase extends Database {

        public SQLite3 $connection;

        public function __construct(string $name, string $path) {
            parent::__construct(SQLITE_DATABASE_TYPE, $name, "", "", 0, "", $path);


            $dbFileName = parent::getName() . ".db";
            $this->connection = new SQLite3($dbFileName);
            $this->connection->enableExceptions(true);
        }

        public function statement(string $query): DataStatement {
            $_SESSION['dataapi']['log']['queries'][parent::getName()] += 1;
            return new SQLiteStatement($this, $query);
        }
        public function query(string $query): DataResult {
            $stmt = $this->statement($query);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        public function __sleep(): array {
            $this->connection->close();
            return array();
        }
        public function __wakeup(): void {
            $dbFileName = parent::getName() . ".db";
            $this->connection->open($dbFileName);
        }

    }
    class MySQLDatabase extends Database {

        public mysqli $connection;

        public function __construct(string $name, string $user, string $password, int $port, string $address) {
            parent::__construct(MYSQL_DATABASE_TYPE, $name, $user, $password, $port, $address, "");

            $this->connection = new mysqli($address, $user, $password);
            $this->query("CREATE DATABASE " . $name);
            $this->connection->select_db($name);
        }

        public function statement(string $query): DataStatement {
            $_SESSION['dataapi']['log']['queries'][$this->getName()] += 1;
            return new MySQLStatement($this, $query);
        }
        public function query(string $query): DataResult {
            $stmt = $this->statement($query);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

    }
    abstract class Database {

        private readonly DatabaseType $databaseType;
        private readonly string $name;

        private readonly string $user;
        private readonly string $password;
        private readonly int $port;
        private readonly string $address;

        private readonly string $path;

        abstract function statement(string $query): DataStatement;
        abstract function query(string $query): DataResult;

        /**
         * @throws exception caso o banco de dados já exista
         */
        public function __construct(DatabaseType $databaseType, string $name, string $user, string $password, int $port, string $address, string $path) {
            $this->databaseType = $databaseType;
            $this->name = $name;

            $this->user = $user;
            $this->password = $password;
            $this->port = $port;
            $this->address = $address;

            $this->path = $path;

            if (isset($_SESSION['dataapi']['databases'][$databaseType->getName()][$name])) {
                throw new exception("já existe um banco de dados criado com esse tipo de conexão e nome");
            }

            $_SESSION['dataapi']['databases'][$databaseType->getName()][$name] = $this;
            $_SESSION['dataapi']['tables'][$this->getIdentification()] = array();

            $_SESSION['dataapi']['log']['queries'][$name] = 0;
            $_SESSION['dataapi']['log']['created']['databases'] += 1;
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

        /**
         * @return string usuário
         */
        public function getUser(): string {
            return $this->user;
        }

        /**
         * @return string a senha
         */
        public function getPassword(): string {
            return $this->password;
        }

        /**
         * @return int a porta
         */
        public function getPort(): int {
            return $this->port;
        }

        /**
         * @return string o endereço
         */
        public function getAddress(): string {
            return $this->address;
        }

        /**
         * @return string o caminho (para bancos de dados SQLite)
         */
        public function getPath(): string {
            return $this->path;
        }

    }

    defined("MYSQL_DATABASE_TYPE") or define("MYSQL_DATABASE_TYPE", new MySQLDatabaseType());
    defined("SQLITE_DATABASE_TYPE") or define("SQLITE_DATABASE_TYPE", new SQLiteDatabaseType());
