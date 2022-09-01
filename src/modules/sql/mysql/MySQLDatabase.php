<?php
namespace ItsLaivy\DataAPI\MySQL;

use DataAPI\System\Database;

class MySQLDatabase extends Database {

    public function __construct(MySQLDatabaseType $type, string $name) {
        parent::__construct($type, $name);
    }

    public function getDatabaseType(): MySQLDatabaseType {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->databaseType;
    }

}

?>