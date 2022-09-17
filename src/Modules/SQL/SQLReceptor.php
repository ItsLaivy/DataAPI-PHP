<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;
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

    public function unload(bool $save): void {
        unset($this->getTable()->getReceptors()[$this->getBruteId()]);
        parent::unload($save);
    }

    public static function getBruteIdById(SQLTable $table, int $id): string|null {
        $array = $table->getDatabase()->getDatabaseType()->receptorById($table, $id);

        if (count($array) != 0 && array_key_exists('bruteid', $array)) {
            return $array['bruteid'];
        } return null;
    }

}