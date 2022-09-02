<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Receptor {

    public static array $RECEPTORS = array();

    private readonly Database $database;
    private readonly string $name;
    private readonly string $bruteId;

    private int $id;
    private bool $new = false;

    private bool $autoSaveWhenSet = false;

    private array $variables = array();

    /**
     * @throws exception caso já haja um receptor criado com o bruteId informado
     */
    public function __construct(Database $database, string $name, string $bruteId) {
        $this->database = $database;
        $this->name = $name;
        $this->bruteId = $bruteId;

<<<<<<< Updated upstream
        if (isset($_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId])) {
            throw new exception("Já existe um receptor carregado com esse ID nessa tabela");
        }

        $_SESSION['dataapi']['inactive_variables'][$bruteId] = array();
        $_SESSION['dataapi']['active_variables'][$bruteId] = array();

        $this->getTable()->getDatabase()->getDatabaseType()->receptorLoad($this->getTable()->getDatabase(), $this);
=======
        $this->database->getDatabaseType()->receptorLoad($this);
>>>>>>> Stashed changes

        Receptor::$RECEPTORS[$bruteId] = $this;
    }

    public function unload(bool $save): void {
        if ($save) $this->save();
    }
    public function delete() {
        $this->unload(false);
<<<<<<< Updated upstream
        $this->getTable()->getDatabase()->getDatabaseType()->receptorDelete($this->getTable()->getDatabase(), $this);
    }

    public function getInactiveVariables() : array {
        return $_SESSION['dataapi']['inactive_variables'][$this->getBruteId()];
    } public function getActiveVariables() : array {
        return $_SESSION['dataapi']['active_variables'][$this->getBruteId()];
=======
        $this->database->getDatabaseType()->receptorDelete($this);
>>>>>>> Stashed changes
    }

    /**
     * @return array
     */
    public function getVariables(): array {
        return $this->variables;
    }

    /**
     * @throws exception caso a variável não seja encontrada
     */
    public function get(string $name): mixed {
        return $this->variables[$name];
    }

    /**
     * @throws exception caso a variável não seja encontrada
     */
    public function set(string $name, mixed $object): void {
        $this->variables[$name] = $object;
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
<<<<<<< Updated upstream
        $this->getTable()->getDatabase()->getDatabaseType()->save($this->getTable()->getDatabase(), $this);
    }

    /**
     * @return Table tabela do receptor
     */
    public function getTable(): Table {
        return $this->table;
=======
        $this->database->getDatabaseType()->save($this);
>>>>>>> Stashed changes
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

    /**
     * @return bool
     */
    public function isNew(): bool {
        return $this->new;
    }

    /**
     * @param bool $new
     */
    public function setNew(bool $new): void {
        $this->new = $new;
    }

}
