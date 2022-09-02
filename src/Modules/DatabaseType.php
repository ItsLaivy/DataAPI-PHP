<?php
namespace ItsLaivy\DataAPI\Modules;

use DateTime;
use DateTimeZone;
use Exception;
use Throwable;

abstract class DatabaseType {

    private readonly string $name;

    private readonly array $DATABASES;

    public function __construct(string $name) {
        $this->name = $name;
        $this->DATABASES = array();
    }

    /**
     * @return array
     */
    public function getDatabases(): array {
        return $this->DATABASES;
    }

    /**
     * @throws Throwable caso o erro não esteja categorizado em commonErrors()
     */
    public function throws(Throwable $throwable): void {
        $this->throwsDirectly($throwable->getCode(), $throwable->getMessage());
    }

    function getAPIDate(): string {
        $dt = new DateTime("now", new DateTimeZone('America/Sao_Paulo'));
        $dt->setTimestamp(time());
        return $dt->format('d/m/y H:i:s');
    }

    /**
     * @throws Exception
     */
    public function throwsDirectly(int $tCode, string $tMessage): void {
        $throws = true;
        foreach ($this->suppressedErrors() as $code) {
            if ($code === $tCode) $throws = false;
        }

        if ($throws) {
            throw new exception($tMessage, $tCode);
        }
    }

    public abstract function suppressedErrors(): array;

    public function getName(): string {
        return $this->name;
    }

    public abstract function open(): void;
    public abstract function close(): void;

    /**
     * É usado para pegar os dados de um receptor no banco de dados
     *
     * @return array deve retornar uma array com o (key = nome da variável) e (value = valor serializado)
     */
    public abstract function data(Receptor $receptor): array;

    /**
     * É chamado quando um receptor é carregado/criado
     */
    public abstract function receptorLoad(Receptor $receptor): void;
    /**
     * É chamado quando um receptor é deletado
     */
    public abstract function receptorDelete(Receptor $receptor): void;

    /**
     * É chamado sempre que um receptor precisa ser salvo
     */
    public abstract function save(Receptor $receptor): void;

    // Variáveis

    /**
     * É chamado quando uma variável é carregada/criada
     */
    public abstract function variableLoad(Variable $variable): void;
    /**
     * É chamado quando uma variável é deletada
     */
    public abstract function variableDelete(Variable $variable): void;

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