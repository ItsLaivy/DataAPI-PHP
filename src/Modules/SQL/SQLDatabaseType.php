<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\DatabaseType;
use ItsLaivy\DataAPI\Modules\Variable;

abstract class SQLDatabaseType extends DatabaseType {

    // Tables

    /**
     * É chamado sempre que uma tabela é carregada/criada
     */
    public abstract function tableLoad(SQLTable $table): void;
    /**
     * É chamado quando uma tabela é deletada
     */
    public abstract function tableDelete(SQLTable $table): void;

    // Variables
    public abstract function variableLoad(SQLVariable|Variable $variable): void;

    public abstract function variableDelete(SQLVariable|Variable $variable): void;

}