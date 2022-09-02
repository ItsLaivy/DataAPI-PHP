<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;

abstract class SQLDatabase extends Database {

    private readonly SQLTable $table;

    public function __construct(SQLDatabaseType $databaseType, SQLTable $table, string $name) {
        parent::__construct($databaseType, $name);
        $this->table = $table;
    }

    /**
     * @return SQLTable
     */
    public function getTable(): SQLTable {
        return $this->table;
    }

}