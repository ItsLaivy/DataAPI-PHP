<?php
namespace ItsLaivy\DataAPI\MySQL;

use ItsLaivy\DataAPI\System\Database;

require_once __DIR__ . '/../../../vendor/autoload.php';

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