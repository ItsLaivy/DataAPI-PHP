<?php
    require_once(dirname(__FILE__).'/../../DataAPI.php');

    class ActiveVariable {

        private readonly Receptor $receptor;
        private readonly Variable $variable;

        private mixed $data;

        public function __construct(Receptor $receptor, Variable $variable, mixed $data) {
            $this->variable = $variable;
            $this->data = $data;
            $this->receptor = $receptor;

            $_SESSION['dataapi']['log']['created']['active_variables'] += 1;
            $_SESSION['dataapi']['active_variables'][$receptor->getBruteId()][$variable->getName()] = $this;
        }

        /**
         * @return Receptor receptor da variável inativa
         */
        public function getReceptor(): Receptor {
            return $this->receptor;
        }

        /**
         * @return Variable a variável
         */
        public function getVariable(): Variable {
            return $this->variable;
        }

        /**
         * @return mixed valor da variável
         */
        public function getData(): mixed {
            return $this->data;
        }

        public function setData(mixed $value): void {
            $this->data = $value;
        }

    }