<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use Exception;

class SQLTable {

    private readonly SQLDatabase $database;
    private readonly string $name;

    private readonly array $receptors;
    private readonly array $variables;

    /**
     * @throws Exception throws in case of already have this table loaded.
     */
    public function __construct(SQLDatabase $database, string $name) {
        $this->database = $database;
        $this->name = $name;
        
        if (array_key_exists($name, $this->database->getTables())) {
            throw new Exception("This database '". $database->getName() ."' already contains a table named '".$name."'");
        }

        $this->database->getDatabaseType()->tableLoad($this);

        $this->receptors = array();
        $this->variables = array();

        $this->database->getTables()[$name] = $this;
    }

    /**
     * @return array
     */
    public function getReceptors(): array {
        return $this->receptors;
    }

    /**
     * @return array
     */
    public function getVariables(): array {
        return $this->variables;
    }

    /**
     * Created for internal use purposes.
     */
    public function getIdentification(): string {
        return $this->getDatabase()->getIdentification() . "-" . $this->getName();
    }

    public function delete(): void {
        // Unload receptors
        foreach ($this->getReceptors() as $name => $receptor) {
            $receptor->unload(false);
        }
        // Unload Variables
        foreach ($this->getVariables() as $name => $variable) {
            $variable->delete();
        }

        $this->database->getDatabaseType()->tableDelete($this);
    }
    
    public function getDatabase(): SQLDatabase {
        return $this->database;
    }
    
    public function getName(): string {
        return $this->name;
    }

}