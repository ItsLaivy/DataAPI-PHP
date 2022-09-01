<?php
namespace DataAPI\System;

use Exception;
use SQLite3;

abstract class Database {

    protected readonly DatabaseType $databaseType;
    protected readonly string $name;

    /**
     * @throws exception caso o banco de dados já exista
     */
    public function __construct(DatabaseType $databaseType, string $name) {
        $this->databaseType = $databaseType;
        $this->name = $name;

        if (isset($_SESSION['dataapi']['databases'][$databaseType->getName()][$name])) {
            if (EXISTS_ERROR) throw new exception("já existe um banco de dados criado com esse tipo de conexão e nome");
            return;
        }

        $_SESSION['dataapi']['log']['queries'][$name] = 0;

        $databaseType->databaseLoad($this);

        $_SESSION['dataapi']['databases'][$databaseType->getName()][$name] = serialize($this);
        $_SESSION['dataapi']['tables'][$this->getIdentification()] = array();
        $_SESSION['dataapi']['log']['created']['databases'] += 1;
    }

    public function __sleep(): array {
        return array(
            'databaseType',
            'name',
        );
    }

    public function delete(): void {
        foreach ($this->getTables() as $name => $table) {
            $table->delete();
        }
        $this->getDatabaseType()->databaseDelete($this);
    }

    public function getTables(): array {
        return $_SESSION['dataapi']['tables'][$this->getIdentification()];
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