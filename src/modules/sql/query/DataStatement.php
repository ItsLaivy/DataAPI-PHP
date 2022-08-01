<?php
    require_once(dirname(__FILE__).'/../../../DataAPI.php');

    abstract class DataStatement {

        private readonly string $query;
        private readonly Database $database;

        /**
         * @param string $query query a ser executado
         */
        public function __construct(Database $database, string $query) {
            $this->query = $query;
            $this->database = $database;
        }

        public function getQuery(): string {
            return $this->query;
        }
        public function getDatabase(): Database {
            return $this->database;
        }

        public abstract function execute(): DataResult;
        public abstract function close(): void;

        public abstract function bindParameters(string $param, mixed $var): void;

    }