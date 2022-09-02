<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;

abstract class SQLDatabase extends Database {

    public function __construct(SQLDatabaseType $databaseType, string $name) {
        parent::__construct($databaseType, $name);
    }

}