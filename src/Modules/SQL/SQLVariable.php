<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Variable;

class SQLVariable extends Variable {

    private readonly SQLTable $table;

    public function __construct(SQLTable $table, string $name, mixed $default, bool $serialize = true, bool $temporary = false) {
        parent::__construct($table->getDatabase(), $name, $default, $serialize, $temporary);
        $this->table = $table;

        $this->load();

        $this->getTable()->getVariables()[$name] = $this;
    }

    /**
     * @return SQLTable
     */
    public function getTable(): SQLTable {
        return $this->table;
    }

}