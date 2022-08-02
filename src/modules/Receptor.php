<?php
namespace DataAPI\System;

use Exception;

class Receptor {
    private readonly Table $table;
    private readonly string $name;
    private readonly string $bruteId;

    private int $id;

    private bool $autoSaveWhenSet = false;

    /**
     * @throws exception caso já haja um receptor criado com o bruteId informado
     */
    public function __construct(Table $table, string $name, string $bruteId) {
        $this->table = $table;
        $this->name = $name;
        $this->bruteId = $bruteId;

        if (isset($_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId])) {
            throw new exception("Já existe um receptor carregado com esse ID nessa tabela");
        }

        $_SESSION['dataapi']['inactive_variables'][$bruteId] = array();
        $_SESSION['dataapi']['active_variables'][$bruteId] = array();

        $this->getTable()->getDatabase()->getDatabaseType()->receptorLoad($this->getTable()->getDatabase(), $this);

        $_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId] = $this;
        $_SESSION['dataapi']['log']['created']['receptors'] += 1;
    }

    public function unload(bool $save): void {
        unset($_SESSION['dataapi']['receptors'][$this->getTable()->getIdentification()][$this->bruteId]);
        unset($_SESSION['dataapi']['active_variables'][$this->bruteId]);
        unset($_SESSION['dataapi']['inactive_variables'][$this->bruteId]);

        if ($save) $this->save();
    }
    public function delete() {
        $this->unload(false);
        $this->getTable()->getDatabase()->getDatabaseType()->receptorDelete($this->getTable()->getDatabase(), $this);
    }

    public function getInactiveVariables() : array {
        return $_SESSION['dataapi']['inactive_variables'][$this->getBruteId()];
    } public function getActiveVariables() : array {
    return $_SESSION['dataapi']['active_variables'][$this->getBruteId()];
}

    /**
     * @throws exception caso a variável não seja encontrada
     */
    public function getInactiveVariable(string $name) : InactiveVariable {
        if (!isset($_SESSION['dataapi']['inactive_variables'][$this->getBruteId()][$name])) {
            throw new exception("Não existe nenhuma variável com o nome '".$name."' na tabela '".$this->getTable()->getName()."' do banco de dados '".$this->getTable()->getDatabase()->getName()."'");
        }
        return $_SESSION['dataapi']['inactive_variables'][$this->getBruteId()][$name];
    }
    /**
     * @throws exception caso a variável não seja encontrada
     */
    public function getActiveVariable(string $name) : ActiveVariable {
        if (!isset($_SESSION['dataapi']['active_variables'][$this->getBruteId()][$name])) {
            throw new exception("Não existe nenhuma variável com o nome '".$name."' na tabela '".$this->getTable()->getName()."' do banco de dados '".$this->getTable()->getDatabase()->getName()."'");
        }
        return $_SESSION['dataapi']['active_variables'][$this->getBruteId()][$name];
    }

    /**
     * @throws exception caso a variável não seja encontrada
     */
    public function get(string $name): mixed {
        return $this->getActiveVariable($name)->getData();
    }

    /**
     * @throws exception caso a variável não seja encontrada
     */
    public function set(string $name, mixed $object): void {
        $this->getActiveVariable($name)->setData($object);
        if ($this->isAutoSaveWhenSet()) $this->save();
    }

    /**
     * Sempre que uma variável é redefinida usando o método set() ele será salvo automaticamente se for true
     */
    public function isAutoSaveWhenSet(): bool {
        return $this->autoSaveWhenSet;
    }

    /**
     * @param bool $autoSaveWhenSet se true, ele salvará sempre que houver uma alteração pelo método set()
     */
    public function setAutoSaveWhenSet(bool $autoSaveWhenSet): void {
        $this->autoSaveWhenSet = $autoSaveWhenSet;
    }

    public function save(): void {
        $this->getTable()->getDatabase()->getDatabaseType()->save($this->getTable()->getDatabase(), $this);
    }

    /**
     * @return Table tabela do receptor
     */
    public function getTable(): Table {
        return $this->table;
    }

    /**
     * @return string nome do receptor
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string bruteId do receptor
     */
    public function getBruteId(): string {
        return $this->bruteId;
    }

    /**
     * @return int ID no banco de dados
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id ID no banco de dados
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

}
