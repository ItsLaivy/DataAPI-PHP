<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Receptor {

    public static array $RECEPTORS = array();

    private readonly Database $database;
    private readonly string $name;
    private readonly string $bruteId;

    protected int $id;
    protected bool $new = false;

    protected bool $autoSaveWhenSet = false;

    public array $activeVariables = array();
    public array $inactiveVariables = array();

    /**
     * @throws exception if a receptor with that bruteid already exists
     */
    public function __construct(Database $database, string $name, string $bruteId) {
        $this->database = $database;
        $this->name = $name;
        $this->bruteId = $bruteId;

        if (array_key_exists($bruteId, self::$RECEPTORS)) {
            throw new Exception("A receptor with bruteid '".$bruteId."' already exists on database '".$database->getName()."'");
        }
        
        $this->database->getDatabaseType()->receptorLoad($this);
    
        self::$RECEPTORS[$bruteId] = $this;
    }

    public function unload(bool $save): void {
        if ($save) $this->save();
    }
    public function delete(): void {
        $this->unload(false);
        $this->database->getDatabaseType()->receptorDelete($this);
    }

    public function &getActiveVariables(): array {
        return $this->activeVariables;
    }

    public function &getInactiveVariables(): array {
        return $this->inactiveVariables;
    }

    public function get(string $name): mixed {
        return $this->getActiveVariables()[$name]->getData();
    }

    public function set(string $name, mixed $object): void {
        $this->getActiveVariables()[$name]->setData($object);
        if ($this->isAutoSaveWhenSet()) $this->save();
    }

    public function isAutoSaveWhenSet(): bool {
        return $this->autoSaveWhenSet;
    }

    public function setAutoSaveWhenSet(bool $autoSaveWhenSet): void {
        $this->autoSaveWhenSet = $autoSaveWhenSet;
    }

    public function save(): void {
        $this->database->getDatabaseType()->save($this);
    }
    
    public function getName(): string {
        return $this->name;
    }

    public function getBruteId(): string {
        return $this->bruteId;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }
    
    public function isNew(): bool {
        return $this->new;
    }

    public function setNew(bool $new): void {
        $this->new = $new;
    }

}
