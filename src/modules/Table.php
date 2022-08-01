<?php
    require_once(dirname(__FILE__).'/../DataAPI.php');

    class Table {
        private readonly Database $database;
        private readonly string $name;
        
        public function __construct(Database $database, string $name) {
            $this->database = $database;
            $this->name = $name;

            if (isset($_SESSION['dataapi']['tables'][$database->getIdentification()][$name])) {
                throw new exception("Já existe uma tabela carregada com esse nome nesse banco de dados");
            }

            $this->database->query($this->database->getDatabaseType()->getTableCreationQuery($this->name));

            $_SESSION['dataapi']['tables'][$database->getIdentification()][$name] = $this;
            $_SESSION['dataapi']['receptors'][$this->getIdentification()] = array();

            $_SESSION['dataapi']['log']['created']['tables'] += 1;
        }

        /**
         * Foi feito para uso interno, é usado para armazenar nas variáveis sem precisar saltar o objeto inteiro
         */
        public function getIdentification(): string {
            return $this->getDatabase()->getIdentification() . "-" . $this->getName();
        }

        /**
         * @return Database banco de dados da tabela
         */
        public function getDatabase(): Database {
            return $this->database;
        }

        /**
         * @return string nome da tabela
         */
        public function getName(): string {
            return $this->name;
        }

    }