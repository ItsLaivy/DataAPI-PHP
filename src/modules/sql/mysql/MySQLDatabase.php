<?php
namespace ItsLaivy\DataAPI\MySQL;

use ItsLaivy\DataAPI\Mechanics\Database;

class MySQLDatabase extends Database {

    public function __construct(MySQLDatabaseType $type, string $name) {
        parent::__construct($type, $name);
    }

}

?>