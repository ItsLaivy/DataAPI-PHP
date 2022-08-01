<?php
    require_once(dirname(__FILE__).'/DatabaseType.php');

    class SQLiteDatabaseType extends DatabaseType {

        /**
         * Não é recomendado usar diretamente a variável, use somente caso os métodos statement() ou query() não sirvam para o seu uso
         */
        public SQLite3 $connection;

        public function __construct() {
            parent::__construct(
                "SQLITE",
                "SELECT % FROM '%' WHERE %;",
                "INSERT INTO '%' (%) VALUES (%);",
                "UPDATE '%' SET % WHERE %;",
                "CREATE TABLE '%' ('id' INT PRIMARY KEY AUTOINCREMENT, 'name' TEXT, 'bruteid' TEXT, 'last_update' TEXT);",
                "ALTER TABLE '%' ADD COLUMN '%' TEXT DEFAULT '%';",
                "DELETE FROM '%' WHERE bruteid = '%';"
            );
        }

        public function commonErrors(): array {
            return array(0);
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
                if (DEBUG) echo "Não foi possível realizar isso pois o statement não foi criado com sucesso<br>";
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
                if (DEBUG) echo "Não foi possível realizar isso pois o statement não foi criado com sucesso<br>";
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
                if (DEBUG) echo "Não foi possível realizar isso pois o statement não foi criado com sucesso<br>";
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