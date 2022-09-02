<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Variable;

class SQLVariable extends Variable {

    private readonly SQLTable $table;

    public function __construct(SQLTable $table, string $name, mixed $default, bool $temporary) {
        parent::__construct($table->getDatabase(), $name, $default, $temporary);
        $this->table = $table;

        $this->table->getVariables()[$name] = $this;
    }

    /**
     * @return SQLTable
     */
    public function getTable(): SQLTable {
        return $this->table;
    }

}