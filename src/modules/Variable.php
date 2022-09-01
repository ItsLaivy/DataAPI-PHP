<?php
namespace ItsLaivy\DataAPI\System;

use Exception;

class Variable {
    private readonly string $name;
    private readonly Table $table;
    private readonly mixed $default;
    private readonly bool $temporary;

    public function __construct(string $name, Table $table, mixed $default, bool $temporary) {
        $this->name = $name;
        $this->table = $table;
        $this->default = $default;
        $this->temporary = $temporary;

        if (isset($_SESSION['dataapi']['variables'][$table->getIdentification()][$name])) {
            if (EXISTS_ERROR) throw new exception("Já existe uma variável carregada com esse nome nessa tabela");
            return;
        }

        $table->getDatabase()->getDatabaseType()->variableLoad($table->getDatabase(), $this);

        $_SESSION['dataapi']['variables'][$table->getIdentification()][$name] = serialize($this);
        $_SESSION['dataapi']['log']['created']['variables'] += 1;
    }

    public function delete(): void {
        unset($_SESSION['dataapi']['variables'][$this->getTable()->getIdentification()][$this->getName()]);
        $this->getTable()->getDatabase()->getDatabaseType()->variableDelete($this->getTable()->getDatabase(), $this);
    }

    /**
     * @return string o nome da variável
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Table a tabela da variável
     */
    public function getTable(): Table {
        return $this->table;
    }

    /**
     * @return mixed o valor padrão da variável
     */
    public function getDefault(): mixed {
        return $this->default;
    }

    /**
     * @return bool se uma variável for temporária, ela não será salva no banco de dados, e ficará apenas na memória do servidor
     */
    public function isTemporary(): bool {
        return $this->temporary;
    }

}