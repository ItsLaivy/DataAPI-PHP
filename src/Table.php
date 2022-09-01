<?php
namespace DataAPI\System;

use Exception;

class Table {
    private readonly Database $database;
    private readonly string $name;
    private readonly bool $changesSensitive;

    /**
     * @param Database $database the database
     * @param string $name the Table name
     * @param bool $changesSensitive if something is changed at the database while a receptor are loaded, the receptor will receive these new changes after a {@link Receptor::save()}
     * @throws Exception if a table with that parameters already exists
     */
    public function __construct(Database $database, string $name, bool $changesSensitive) {
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

        $this->changesSensitive = $changesSensitive;
        $var = new Variable("last_change", $this, 0, false);
        if (!$changesSensitive) {
            $var->delete();
        }
    }

    /**
     * Foi feito para uso interno, é usado para armazenar nas variáveis sem precisar saltar o objeto inteiro
     */
    public function getIdentification(): string {
        return $this->getDatabase()->getIdentification() . "-" . $this->getName();
    }

    /**
     * if true, after a change at the table, when the receptor save, these new data will apply into it
     *
     * @return bool if table is change sensitive
     */
    public function isChangesSensitive(): bool {
        return $this->changesSensitive;
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