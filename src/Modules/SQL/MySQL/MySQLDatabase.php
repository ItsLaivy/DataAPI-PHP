<?php
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use ItsLaivy\DataAPI\Modules\SQL\SQLDatabase;

class MySQLDatabase extends SQLDatabase {

    public function __construct(MySQLDatabaseType $type, string $name) {
        parent::__construct($type, $name);
    }

}