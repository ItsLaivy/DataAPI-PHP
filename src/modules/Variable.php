<?php
    require_once(dirname(__FILE__).'/../DataAPI.php');

    class Variable {
        private readonly string $name;
        private readonly Table $table;
        private readonly mixed $default;
        private readonly bool $temporary;

        public function __construct(string $name, Table $table, mixed $default, bool $temporary) {
            $this->name = $name;
            $this->table = $table;
            $this->default = $default;
            $this->temporary = $temporary;

            if (isset($_SESSION['dataapi']['variables'][$table->getIdentification()][$name])) {
                throw new exception("Já existe uma tabela carregada com esse nome nesse banco de dados");
            }

            $table->getDatabase()->query($table->getDatabase()->getDatabaseType()->getColumnCreationQuery($table->getName(), $name, serialize($default)));

            $_SESSION['dataapi']['variables'][$table->getIdentification()][$name] = $this;
            $_SESSION['dataapi']['log']['created']['variables'] += 1;
        }

        /**
         * @return string o nome da variável
         */
        public function getName(): string {
            return $this->name;
        }

        /**
         * @return Table a tabela da variável
         */
        public function getTable(): Table {
            return $this->table;
        }

        /**
         * @return mixed o valor padrão da variável
         */
        public function getDefault(): mixed {
            return $this->default;
        }

        /**
         * @return bool se uma variável for temporária, ela não será salva no banco de dados, e ficará apenas na memória do servidor
         */
        public function isTemporary(): bool {
            return $this->temporary;
        }

    }