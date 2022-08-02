<?php
namespace DataAPI\System;

use Exception;
use Throwable;

abstract class DatabaseType {

    private readonly string $name;

    public function __construct(string $name) {
        $this->name = $name;
        $_SESSION['dataapi']['databases'][$name] = array();
    }

    /**
     * @throws Throwable caso o erro não esteja categorizado em commonErrors()
     */
    public function throws(Throwable $throwable): void {
        $this->throwsDirectly($throwable->getCode(), $throwable->getMessage());
    }
    public function throwsDirectly(int $tCode, string $tMessage): void {
        $throws = true;
        if (DEBUG) echo "Possível erro de código: '".$tCode."' - '".$tMessage."'<br>";
        foreach ($this->suppressedErrors() as $code) {
            if ($code === $tCode) $throws = false;
        } if ($throws) throw new exception($tMessage, $tCode);
    }

    public abstract function suppressedErrors(): array;

    public function getName(): string {
        return $this->name;
    }

    /**
     * É usado para pegar os dados de um receptor no banco de dados
     *
     * @return array deve retornar uma array com o (key = nome da variável) e (value = valor serializado)
     */
    public abstract function data(Database $database, Receptor $receptor): array;

    /**
     * É chamado quando um receptor é carregado/criado
     */
    public abstract function receptorLoad(Database $database, Receptor $receptor): void;
    /**
     * É chamado quando um receptor é deletado
     */
    public abstract function receptorDelete(Database $database, Receptor $receptor): void;

    /**
     * É chamado sempre que um receptor precisa ser salvo
     */
    public abstract function save(Database $database, Receptor $receptor): void;

    // Tabelas

    /**
     * É chamado sempre que uma tabela é carregada/criada
     */
    public abstract function tableLoad(Database $database, Table $table): void;
    /**
     * É chamado quando uma tabela é deletada
     */
    public abstract function tableDelete(Database $database, Table $table): void;

    // Variáveis

    /**
     * É chamado quando uma variável é carregada/criada
     */
    public abstract function variableLoad(Database $database, Variable $variable): void;
    /**
     * É chamado quando uma variável é deletada
     */
    public abstract function variableDelete(Database $database, Variable $variable): void;

    // Banco de dados

    /**
     * É chamado quando um banco de dados é carregado/criado
     */
    public abstract function databaseLoad(Database $database): void;
    /**
     * É chamado quando um banco de dados é deletado
     */
    public abstract function databaseDelete(Database $database): void;

}