<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Receptor;

class SQLReceptor extends Receptor {
    
    /**
     * Gets all receptors bruteids that have the variable with same value as value param.
     * 
     * @param SQLVariable $variable the variable
     * @param string $value the value
     * @return array array containing all receptor brute ids that contains the value of $variable same as $value
     */
    public static function getSQLReceptorBruteIdByVariable(SQLVariable $variable, mixed $value): array {
        return $variable->getTable()->getDatabase()->getDatabaseType()->receptorsFromVariableValue($variable, $value);
    }
    
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