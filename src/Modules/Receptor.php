<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Receptor {

    public static array $RECEPTORS = array();

    private readonly Database $database;

    private string $name;
    private string $bruteId;

    protected int $id;
    protected bool $new = false;

    protected bool $autoSaveWhenSet = false;

    public array $activeVariables = array();
    public array $inactiveVariables = array();

    /**
     * @throws exception caso já haja um receptor criado com o bruteId informado
     */
    public function __construct(Database $database, string $name, string $bruteId) {
        $this->database = $database;
        $this->name = $name;
        $this->bruteId = $bruteId;

        $this->database->getDatabaseType()->receptorLoad($this);

        Receptor::$RECEPTORS[$bruteId] = $this;
    }

    public function unload(bool $save): void {
        if ($save) $this->save();
        unset(Receptor::$RECEPTORS[$this->getBruteId()]);
    }
    public function delete(): void {
        $this->unload(false);
        $this->database->getDatabaseType()->receptorDelete($this);
    }

    /**
     * @return Database
     */
    public function getDatabase(): Database {
        return $this->database;
    }

    /**
     * @return array The active variables array
     */
    public function &getActiveVariables(): array {
        return $this->activeVariables;
    }

    /**
     * @return array The inactive variables array
     */
    public function &getInactiveVariables(): array {
        return $this->inactiveVariables;
    }

    /**
     * @throws exception Caso a variável não seja encontrada
     */
    public function get(string $name): mixed {
        if (!array_key_exists($name, $this->getActiveVariables())) {
            throw new exception("Cannot find a active variable named '".$name."' at receptor '".$this->getBruteId()."'");
        }
        return $this->getActiveVariables()[$name]->getData();
    }

    /**
     * @throws exception Caso a variável não seja encontrada
     */
    public function set(string $name, mixed $object): void {
        if (!array_key_exists($name, $this->getActiveVariables())) {
            throw new exception("Cannot find a active variable named '".$name."' at receptor '".$this->getBruteId()."'");
        }
        
        $this->getActiveVariables()[$name]->setData($object);
        if ($this->isAutoSaveWhenSet()) $this->save();
    }

    /**
     * Sempre que uma variável é redefinida usando o método set() ele será salvo automaticamente se for true
     */
    public function isAutoSaveWhenSet(): bool {
        return $this->autoSaveWhenSet;
    }

    /**
     * @param bool $autoSaveWhenSet Se true, ele salvará sempre que houver uma alteração pelo método set()
     */
    public function setAutoSaveWhenSet(bool $autoSaveWhenSet): void {
        $this->autoSaveWhenSet = $autoSaveWhenSet;
    }

    public function save(): void {
        $this->database->getDatabaseType()->save($this);
    }

    /**
     * @return string nome do receptor
     */
    public function getName(): string {
        return $this->name;
    }
    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string bruteId do receptor
     */
    public function getBruteId(): string {
        return $this->bruteId;
    }
    /**
     * @param string $bruteId
     */
    public function setBruteId(string $bruteId): void {
        $this->bruteId = $bruteId;
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
