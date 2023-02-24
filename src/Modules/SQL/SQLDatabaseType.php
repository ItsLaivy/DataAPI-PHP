<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\DatabaseType;
use ItsLaivy\DataAPI\Modules\Variable;

abstract class SQLDatabaseType extends DatabaseType {

    // Tables

    /**
     * It's called after a table are loaded/created
     */
    public abstract function tableLoad(SQLTable $table): void;
    /**
     * Called when a table is deleted
     */
    public abstract function tableDelete(SQLTable $table): void;

    // Variables
    public abstract function variableLoad(SQLVariable|Variable $variable): void;

    public abstract function variableDelete(SQLVariable|Variable $variable): void;

    public abstract function receptorsFromVariableValue(SQLVariable $variable, mixed $value): array;
    
}