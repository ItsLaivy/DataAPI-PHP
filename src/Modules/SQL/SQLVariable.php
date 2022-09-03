<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Variable;

class SQLVariable extends Variable {

    private readonly SQLTable $table;

    public function __construct(SQLTable $table, string $name, mixed $default, bool $serialize = true, bool $temporary = false) {
        $this->table = $table;
        parent::__construct($table->getDatabase(), $name, $default, $serialize, $temporary);

        $this->getTable()->getVariables()[$name] = $this;
    }

    /**
     * @return SQLTable
     */
    public function getTable(): SQLTable {
        return $this->table;
    }

}