<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Variable {

    public static array $VARIABLES = array();

    private readonly string $name;
    private readonly Database $database;
    private readonly mixed $default;
    private readonly bool $serialize;
    private readonly bool $temporary;

    /**
     * @param Database $database The variable's database
     * @param string $name The variable's name
     * @param mixed $default The default value of the new receptors
     * @param bool $serialize If true the values will be save as a serialized string, if false will save using __toString
     * @param bool $temporary If true the variable will not save into the database
     */
    public function __construct(Database $database, string $name, mixed $default, bool $serialize = true, bool $temporary = false) {
        $this->name = $name;
        $this->database = $database;
        $this->default = $default;
        $this->serialize = $serialize;
        $this->temporary = $temporary;

        if (!$this->serialize && !method_exists($default, "__toString")) {
            throw new exception("To use the serialize = false option, the default value object needs to implement the __toString");
        }

        $this->load();

        self::$VARIABLES[$name] = $this;
    }

    protected function load(): void {
        $this->getDatabase()->getDatabaseType()->variableLoad($this);
    }

    public function delete(): void {
        $this->database->getDatabaseType()->variableDelete($this);
        unset(self::$VARIABLES[$this->getName()]);
    }

    public function isSerialize(): bool {
        return $this->serialize;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDatabase(): Database {
        return $this->database;
    }
    
    public function getDefault(): mixed {
        return $this->default;
    }

    public function isTemporary(): bool {
        return $this->temporary;
    }

}