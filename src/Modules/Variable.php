<?php
namespace ItsLaivy\DataAPI\Modules;

use Exception;

abstract class Variable {

    /**
     * @var Database[][]
     */
    public static array $VARIABLES = array();

    public static function getVariable(Database $database, string $name): Variable|null {
        if (array_key_exists($database->getIdentification(), self::$VARIABLES)) {
            foreach (self::$VARIABLES[$database->getIdentification()] as $variable) {
                if ($variable->getName() == $name) {
                    return $variable;
                }
            }
        }
        return null;
    }

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
        if ($name == 'id' || $name == 'name' || $name == 'bruteid' || $name == 'last_update') {
            throw new exception("The name '".$name."' cannot be used in a variable because its already in use by the api");
        }

        $this->name = $name;
        $this->database = $database;
        $this->default = $default;
        $this->serialize = $serialize;
        $this->temporary = $temporary;

        if (!$this->serialize && !method_exists($default, "__toString")) {
            throw new exception("To use the serialize = false option, the default value object needs to implement the __toString");
        }

        $this->load();

        if (!array_key_exists($database->getIdentification(), self::$VARIABLES)) {
            self::$VARIABLES[$database->getIdentification()] = array();
        }
        self::$VARIABLES[$database->getIdentification()][] = $this;
    }

    protected function load(): void {
        $this->getDatabase()->getDatabaseType()->variableLoad($this);
    }

    public function delete(): void {
        $this->database->getDatabaseType()->variableDelete($this);
    }

    /**
     * @return bool
     */
    public function isSerialize(): bool {
        return $this->serialize;
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