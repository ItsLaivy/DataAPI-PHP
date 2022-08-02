<?php
namespace DataAPI\System;

use Exception;

class Table {
    private readonly Database $database;
    private readonly string $name;

    public function __construct(Database $database, string $name) {
        $this->database = $database;
        $this->name = $name;

        if (isset($_SESSION['dataapi']['tables'][$database->getIdentification()][$name])) {
            if (EXISTS_ERROR) throw new exception("Já existe uma tabela carregada com esse nome nesse banco de dados");
            return;
        }

        $this->database->getDatabaseType()->tableLoad($database, $this);

        $_SESSION['dataapi']['tables'][$database->getIdentification()][$name] = $this;
        $_SESSION['dataapi']['receptors'][$this->getIdentification()] = array();

        $_SESSION['dataapi']['log']['created']['tables'] += 1;
    }

    /**
     * Foi feito para uso interno, é usado para armazenar nas variáveis sem precisar saltar o objeto inteiro
     */
    public function getIdentification(): string {
        return $this->getDatabase()->getIdentification() . "-" . $this->getName();
    }

    public function delete(): void {
        // Unload receptors
        foreach ($this->getReceptors() as $name => $receptor) {
            $receptor->unload(false);
        }
        // Unload variables
        foreach ($this->getVariables() as $name => $variable) {
            $variable->delete();
        }

        $this->database->getDatabaseType()->tableDelete($this->database, $this);
    }

    public function getReceptors(): array {
        return $_SESSION['dataapi']['receptors'][$this->getIdentification()];
    }
    public function getVariables(): array {
        $vars = array();
        foreach ($_SESSION['dataapi']['variables'][$this->getIdentification()] as $name => $variable) {
            $vars[] = $variable;
        }
        return $vars;
    }

    /**
     * @return Database banco de dados da tabela
     */
    public function getDatabase(): Database {
        return $this->database;
    }

    /**
     * @return string nome da tabela
     */
    public function getName(): string {
        return $this->name;
    }

}