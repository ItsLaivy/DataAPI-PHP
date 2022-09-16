<?php
namespace ItsLaivy\DataAPI\Modules\SQL;

use Exception;

class SQLTable {

    private readonly SQLDatabase $database;
    private readonly string $name;

    private readonly array $receptors;
    private readonly array $variables;

    /**
     * @throws Exception Caso já exista uma tabela com esses valores no banco de dados
     */
    public function __construct(SQLDatabase $database, string $name) {
        $this->database = $database;
        $this->name = $name;

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
     * Foi feito para uso interno, é usado para armazenar nas variáveis sem precisar saltar o objeto inteiro
     */
    public function getIdentification(): string {
        return $this->getDatabase()->getIdentification()."-".$this->getName();
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

    /**
     * @return SQLDatabase banco de dados da tabela
     */
    public function getDatabase(): SQLDatabase {
        return $this->database;
    }

    /**
     * @return string nome da tabela
     */
    public function getName(): string {
        return $this->name;
    }

}