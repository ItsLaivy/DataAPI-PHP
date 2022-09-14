<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\Variable;

class SQLVariable extends Variable {

    /**
     * @var SQLTable[][]
     */
    public static array $SQL_VARIABLES = array();

    public static function getSQLVariable(SQLTable $table, string $name): Variable|null {
        if (array_key_exists($table->getIdentification(), self::$SQL_VARIABLES)) {
            foreach (self::$SQL_VARIABLES[$table->getIdentification()] as $variable) {
                if ($variable->getName() == $name) {
                    return $variable;
                }
            }
        }
        return null;
    }

    private readonly SQLTable $table;

    public function __construct(SQLTable $table, string $name, mixed $default, bool $serialize = true, bool $temporary = false) {
        $this->table = $table;
        parent::__construct($table->getDatabase(), $name, $default, $serialize, $temporary);

        $this->getTable()->getVariables()[$name] = $this;

        if (!array_key_exists($table->getIdentification(), self::$SQL_VARIABLES)) {
            self::$SQL_VARIABLES[$table->getIdentification()] = array();
        }
        self::$SQL_VARIABLES[$table->getIdentification()][] = $this;
    }

    /**
     * @return SQLTable
     */
    public function getTable(): SQLTable {
        return $this->table;
    }

}