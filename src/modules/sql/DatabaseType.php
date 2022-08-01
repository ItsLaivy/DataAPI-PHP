<?php
    require_once(dirname(__FILE__).'/../../DataAPI.php');

    abstract class DatabaseType {

        // Queries
        private readonly string $name;

        private readonly string $select;
        private readonly string $insert;
        private readonly string $update;
        private readonly string $tableCreate;
        private readonly string $columnCreate;
        private readonly string $receptorDelete;
        // Queries

        public function __construct(string $name, string $select, string $insert, string $update, string $tableCreate, string $columnCreate, string $receptorDelete) {
            $this->name = $name;

            $this->select = $select;
            $this->insert = $insert;
            $this->update = $update;
            $this->tableCreate = $tableCreate;
            $this->columnCreate = $columnCreate;
            $this->receptorDelete = $receptorDelete;

            $_SESSION['dataapi']['databases'][$name] = array();
        }

        /**
         * @throws Throwable caso o erro não esteja categorizado em commonErrors()
         */
        public function throws(Throwable $throwable): void {
            $throws = true;
            if (DEBUG) echo "Possível erro; Código: '".$throwable->getCode()."' - '".$throwable->getMessage()."'<br>";
            foreach ($this->commonErrors() as $code) {
                if ($code == $throwable->getCode()) $throws = false;
            } if ($throws) throw $throwable;
        }
        public function throwsDirectly(int $tCode, string $tMessage): void {
            $throws = true;
            if (DEBUG) echo "Possível erro; Código: '".$tCode."' - '".$tMessage."'<br>";
            foreach ($this->commonErrors() as $code) {
                if ($code === $tCode) $throws = false;
                echo $code . ":" . $tCode . "<br><br>";
            } if ($throws) throw new exception($tMessage, $tCode);
        }

        public abstract function commonErrors(): array;

        public function getName(): string {
            return $this->name;
        }

        public function getSelectQuery(string $select, string $at, string $where): string {
            return $this->replace($this->select, array($select, $at, $where));
        }
        public function getInsertQuery(string $into, string $columns, string $values): string {
            return $this->replace($this->insert, array($into, $columns, $values));
        }
        public function getUpdateQuery(string $at, string $set, string $where): string {
            return $this->replace($this->update, array($at, $set, $where));
        }
        public function getTableCreationQuery(string $name): string {
            return $this->replace($this->tableCreate, array($name));
        }
        public function getColumnCreationQuery(string $at, string $name, string $default): string {
            return $this->replace($this->columnCreate, array($at, $name, $default));
        }
        public function getDeleteReceptorQuery(string $at, string $bruteid): string {
            return $this->replace($this->receptorDelete, array($at, $bruteid));
        }

        private function replace(string $str, array $a): string {
            foreach ($a as $rpl) {
                $str = preg_replace("/%/", $rpl, $str, 1);
            }
            return $str;
        }

    }