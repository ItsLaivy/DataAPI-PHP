<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Database {

    public static array $DATABASES = array();

    protected readonly DatabaseType $databaseType;
    protected readonly string $name;

    private readonly array $tables;

    /**
     * @throws exception caso o banco de dados já exista
     */
    public function __construct(DatabaseType $databaseType, string $name) {
        $this->databaseType = $databaseType;
        $this->name = $name;

        $databaseType->databaseLoad($this);
        $databaseType->getDatabases()[$name] = $this;

        Database::$DATABASES[$name] = $this;

        $this->tables = array();
    }

    /**
     * @return array
     */
    public function getTables(): array {
        return $this->tables;
    }

    public function __sleep(): array {
        return array(
            'databaseType',
            'name',
        );
    }

    public function delete(): void {
        $this->getDatabaseType()->databaseDelete($this);
    }

    /**
     * Foi feito para uso interno, é usado para armazenar nas variáveis sem precisar saltar o objeto inteiro
     */
    public function getIdentification(): string {
        return $this->getDatabaseType()->getName() . "-" . $this->getName();
    }

    /**
     * @return DatabaseType tipo do banco de dados
     */
    public function getDatabaseType(): DatabaseType {
        return $this->databaseType;
    }

    /**
     * @return string nome
     */
    public function getName(): string {
        return $this->name;
    }

}