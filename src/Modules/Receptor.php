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

        $this->database->getDatabaseType()->receptorLoad($this);

        Receptor::$RECEPTORS[$bruteId] = $this;
    }

    public function unload(bool $save): void {
        if ($save) $this->save();
    }
    public function delete(): void {
        $this->unload(false);
        $this->database->getDatabaseType()->receptorDelete($this);
    }

    /**
     * @return array
     */
    public function getVariables(): array {
        return $this->variables;
    }

    /**
     * @throws exception Caso a variável não seja encontrada
     */
    public function get(string $name): mixed {
        return $this->variables[$name];
    }

    /**
     * @throws exception Caso a variável não seja encontrada
     */
    public function set(string $name, mixed $object): void {
        if (!array_key_exists($name, Variable::$VARIABLES)) {
            throw new exception("Cannot found variable '".$name."'");
        }

        $var = Variable::$VARIABLES[$name];
        if (!$var->isSerialize() && !method_exists($object, "__toString")) {
            throw new exception("Cannot set variable '".$name."' from receptor '".$this->getBruteId()."' because the variable isn't serializable and value cannot be converted as a string!");
        }

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
