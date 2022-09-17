<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;

abstract class SQLDatabase extends Database {

    private readonly array $tables;

    public function __construct(SQLDatabaseType $databaseType, string $name) {
        parent::__construct($databaseType, $name);

        $this->tables = array();
    }

    /**
     * @return SQLTable[]
     */
    public function getTables(): array {
        return $this->tables;
    }

}