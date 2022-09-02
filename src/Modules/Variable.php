<?php
namespace ItsLaivy\DataAPI\Modules;

abstract class Variable {
    private readonly string $name;
    private readonly Database $database;
    private readonly mixed $default;
    private readonly bool $temporary;

    public function __construct(Database $database, string $name, mixed $default, bool $temporary) {
        $this->name = $name;
        $this->database = $database;
        $this->default = $default;
        $this->temporary = $temporary;

        $database->getDatabaseType()->variableLoad($this);
    }

    public function delete(): void {
        unset($_SESSION['dataapi']['Variables'][$this->database->getIdentification()][$this->getName()]);
        $this->database->getDatabaseType()->variableDelete($this);
    }

    /**
     * @return string o nome da variável
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Database
     */
    public function getDatabase(): Database {
        return $this->database;
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