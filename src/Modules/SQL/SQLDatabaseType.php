<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use ItsLaivy\DataAPI\Modules\Database;
use ItsLaivy\DataAPI\Modules\DatabaseType;
use ItsLaivy\DataAPI\Modules\Query\DataResult;
use ItsLaivy\DataAPI\Modules\Query\DataStatement;
use ItsLaivy\DataAPI\Modules\SQL\MySQL\MySQLDatabase;
use ItsLaivy\DataAPI\Modules\SQL\MySQL\MySQLStatement;
use ItsLaivy\DataAPI\Modules\Variable;

abstract class SQLDatabaseType extends DatabaseType {

    public abstract function statement(SQLDatabase $database, string $query): DataStatement;
    public abstract function query(SQLDatabase $database, string $query): DataResult;

    // Tables

    /**
     * Retorna uma array com os valores de um row com o ID (auto_increment) especificado
     */
    public abstract function receptorById(SQLTable $table, int $id): array;

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