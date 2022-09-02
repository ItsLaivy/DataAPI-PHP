<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\DatabaseType;

class SQLDatabase extends Database {

    private readonly SQLTable $table;

    public function __construct(DatabaseType $databaseType, SQLTable $table, string $name) {
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