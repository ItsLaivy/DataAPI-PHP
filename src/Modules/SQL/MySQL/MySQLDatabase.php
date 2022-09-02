<?php
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use ItsLaivy\DataAPI\Modules\SQL\SQLDatabase;
use ItsLaivy\DataAPI\Modules\SQL\SQLTable;

class MySQLDatabase extends SQLDatabase {

    public function __construct(MySQLDatabaseType $type, SQLTable $table, string $name) {
        parent::__construct($type, $table, $name);
    }

}