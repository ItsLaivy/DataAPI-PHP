<?php
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use ItsLaivy\DataAPI\Modules\Database;

class MySQLDatabase extends Database {

    public function __construct(MySQLDatabaseType $type, string $name) {
        parent::__construct($type, $name);
    }

}

?>