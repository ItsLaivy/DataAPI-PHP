<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Receptor;

class SQLReceptor extends Receptor {

    private readonly SQLTable $table;

    public function __construct(SQLTable $table, string $name, string $bruteId) {
        $this->table = $table;
        parent::__construct($table->getDatabase(), $name, $bruteId);
    }

    /**
     * @return SQLTable
     */
    public function getTable(): SQLTable {
        return $this->table;
    }

}