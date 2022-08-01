<?php
    require_once(dirname(__FILE__).'/../DataAPI.php');

    class Receptor {
        private readonly Table $table;
        private readonly string $name;
        private readonly string $bruteId;

        private readonly int $id;

        private bool $autoSaveWhenSet = false;

        /**
         * @throws exception caso já haja um receptor criado com o bruteId informado
         */
        public function __construct(Table $table, string $name, string $bruteId) {
            $this->table = $table;
            $this->name = $name;
            $this->bruteId = $bruteId;

            if (isset($_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId])) {
                throw new exception("Já existe um receptor carregado com esse ID nessa tabela");
            }

            // Processo de carregamento do receptor
            $select = $table->getDatabase()->statement($table->getDatabase()->getDatabaseType()->getSelectQuery("*", $table->getName(), "bruteid = '". $bruteId ."'"));
            $insert = $table->getDatabase()->statement($table->getDatabase()->getDatabaseType()->getInsertQuery($table->getName(), "name,bruteid,last_update", "'" . $name . "','" . $bruteId . "','" . getAPIDate() . "'"));

            $result = $select->execute();
            $assoc = $result->results();

            // Verifica se o receptor não está criado
            if (empty($assoc)) {
                $insert->execute();
                $insert->close();

                $assoc = $select->execute()->results();
            }

            $select->close();

            $_SESSION['dataapi']['inactive_variables'][$bruteId] = array();
            $_SESSION['dataapi']['active_variables'][$bruteId] = array();

            $row = 0;
            foreach ($assoc as $key => $value) {
                if ($row == 0) $this->id = $value; // ID

                if ($row > 3) {
                    new InactiveVariable($this, $key, $value);
                }
                $row++;
            }

            $_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId] = $this;
            $_SESSION['dataapi']['log']['created']['receptors'] += 1;
        }

        public function unload(bool $save): void {
            unset($_SESSION['dataapi']['receptors'][$this->getTable()->getIdentification()][$this->bruteId]);
            unset($_SESSION['dataapi']['active_variables'][$this->bruteId]);
            unset($_SESSION['dataapi']['inactive_variables'][$this->bruteId]);

            if ($save) $this->save();
        }

        public function getInactiveVariables() : array {
            return $_SESSION['dataapi']['inactive_variables'][$this->getBruteId()];
        } public function getActiveVariables() : array {
            return $_SESSION['dataapi']['active_variables'][$this->getBruteId()];
        }

        /**
         * @throws exception caso a variável não seja encontrada
         */
        public function getInactiveVariable(string $name) : InactiveVariable {
            if (!isset($_SESSION['dataapi']['inactive_variables'][$this->getBruteId()][$name])) {
                throw new exception("Não existe nenhuma variável com o nome '".$name."' na tabela '".$this->getTable()->getName()."' do banco de dados '".$this->getTable()->getDatabase()->getName()."'");
            }
            return $_SESSION['dataapi']['inactive_variables'][$this->getBruteId()][$name];
        }
        /**
         * @throws exception caso a variável não seja encontrada
         */
        public function getActiveVariable(string $name) : ActiveVariable {
            if (!isset($_SESSION['dataapi']['active_variables'][$this->getBruteId()][$name])) {
                throw new exception("Não existe nenhuma variável com o nome '".$name."' na tabela '".$this->getTable()->getName()."' do banco de dados '".$this->getTable()->getDatabase()->getName()."'");
            }
            return $_SESSION['dataapi']['active_variables'][$this->getBruteId()][$name];
        }

        /**
         * @throws exception caso a variável não seja encontrada
         */
        public function get(string $name): mixed {
            return $this->getActiveVariable($name)->getData();
        }

        /**
         * @throws exception caso a variável não seja encontrada
         */
        public function set(string $name, mixed $object): void {
            $this->getActiveVariable($name)->setData($object);
            if ($this->isAutoSaveWhenSet()) $this->save();
        }

        /**
         * Sempre que uma variável é redefinida usando o método set() ele será salvo automaticamente se for true
         */
        public function isAutoSaveWhenSet(): bool {
            return $this->autoSaveWhenSet;
        }

        /**
         * @param bool $autoSaveWhenSet se true, ele salvará sempre que houver uma alteração pelo método set()
         */
        public function setAutoSaveWhenSet(bool $autoSaveWhenSet): void {
            $this->autoSaveWhenSet = $autoSaveWhenSet;
        }



        public function save(): void {
            $query = "";
            foreach ($this->getActiveVariables() as $variable) {
                $query = $query . "`".$variable->getVariable()->getName()."`='".serialize($variable->getData())."',";
            }
            $query = $query . "`last_update`='".getAPIDate()."'";

            $this->getTable()->getDatabase()->query($this->getTable()->getDatabase()->getDatabaseType()->getUpdateQuery($this->getTable()->getName(), $query, "bruteid='".$this->getBruteId()."'"));
        }

        /**
         * @return Table tabela do receptor
         */
        public function getTable(): Table {
            return $this->table;
        }

        /**
         * @return string nome do receptor
         */
        public function getName(): string {
            return $this->name;
        }

        /**
         * @return string bruteId do receptor
         */
        public function getBruteId(): string {
            return $this->bruteId;
        }

        /**
         * @return int ID no banco de dados
         */
        public function getId(): mixed {
            return $this->id;
        }

    }
