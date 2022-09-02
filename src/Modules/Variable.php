<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;
use ItsLaivy\DataAPI\Modules\SQL\SQLTable;

class Variable {
    private readonly string $name;
    private readonly SQLTable $table;
    private readonly mixed $default;
    private readonly bool $temporary;

<<<<<<< Updated upstream
    public function __construct(string $name, Table $table, mixed $default, bool $temporary) {
=======
    /**
     * @throws Exception
     */
    public function __construct(string $name, SQLTable $table, mixed $default, bool $temporary) {
>>>>>>> Stashed changes
        $this->name = $name;
        $this->table = $table;
        $this->default = $default;
        $this->temporary = $temporary;

<<<<<<< Updated upstream
        if (isset($_SESSION['dataapi']['Variables'][$table->getIdentification()][$name])) {
            if (EXISTS_ERROR) throw new exception("Já existe uma variável carregada com esse nome nessa tabela");
            return;
        }

        $table->getDatabase()->getDatabaseType()->variableLoad($table->getDatabase(), $this);
=======
        $table->getDatabase()->getDatabaseType()->variableLoad($this);
>>>>>>> Stashed changes

        $this->table->getVariables()[$name] = $this;
    }

    public function delete(): void {
        unset($_SESSION['dataapi']['Variables'][$this->getTable()->getIdentification()][$this->getName()]);
        $this->getTable()->getDatabase()->getDatabaseType()->variableDelete($this->getTable()->getDatabase(), $this);
    }

    /**
     * @return string o nome da variável
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return SQLTable a tabela da variável
     */
    public function getTable(): SQLTable {
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