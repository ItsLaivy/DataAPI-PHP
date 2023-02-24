<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Database {

    public static array $DATABASES = array();

    protected readonly DatabaseType $databaseType;
    protected readonly string $name;

    private readonly array $tables;

    /**
     * @throws exception throws if a database with that name already exists
     */
    public function __construct(DatabaseType $databaseType, string $name) {
        $this->databaseType = $databaseType;
        $this->name = $name;

        if (array_key_exists($name, self::$DATABASES)) {
            throw new Exception("A database named '". $name ."' already exists");
        }
        
        $databaseType->databaseLoad($this);
        $databaseType->getDatabases()[$name] = $this;
    
        self::$DATABASES[$name] = $this;

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
     * Created for internal use purposes.
     */
    public function getIdentification(): string {
        return $this->getDatabaseType()->getName() . "-" . $this->getName();
    }

    public function getDatabaseType(): DatabaseType {
        return $this->databaseType;
    }

    public function getName(): string {
        return $this->name;
    }

}