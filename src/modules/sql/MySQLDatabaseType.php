<?php
    require_once(dirname(__FILE__).'/../../DataAPI.php');

    class MySQLDatabaseType extends DatabaseType {

        /**
         * Não é recomendado usar diretamente a variável, use somente caso os métodos statement() ou query() não sirvam para o seu uso
         */
        public mysqli $connection;

        public function __construct() {
            parent::__construct(
                "MYSQL",
                "SELECT % FROM % WHERE %;",
                "INSERT INTO % (%) VALUES (%);",
                "UPDATE % SET % WHERE %;",
                "CREATE TABLE % (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(128), bruteid VARCHAR(128), last_update VARCHAR(21));",
                "ALTER TABLE % ADD COLUMN % MEDIUMTEXT DEFAULT '%';",
                "DELETE FROM % WHERE bruteid = '%';"
            );
        }

        public function commonErrors(): array {
            return array(1007, 1050, 1060);
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