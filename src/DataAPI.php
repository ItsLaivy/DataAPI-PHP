<?php

    const DEBUG = false;

    // Início da Sessão (onde salvará os receptores, bancos, variáveis, tabelas, etc...)
    session_start();
    session_unset();
    if (!isset($_SESSION['dataapi'])) {
        $_SESSION['dataapi'] = array();

        /**
         * Retorna uma lista de módulos criados
         */
        $_SESSION['dataapi']['databases'] = array(); // NOME DO TIPO DO BANCO (DatabaseType#getName()) - BANCO DE DADOS
        $_SESSION['dataapi']['tables'] = array(); // BANCO DE DADOS - TABELA
        $_SESSION['dataapi']['variables'] = array(); // TABELA - VARIÁVEL
        $_SESSION['dataapi']['receptors'] = array(); // TABELA - RECEPTOR

        /**
         * Ambos retornam as variáveis ativas/inativas de um receptor
         * 
         * Sempre que um receptor é carregado/criado, ele pegará todas as colunas do banco de dados (variáveis),
         * e verificará... Se houverem variáveis respectivas àquela coluna, será transformado em uma
         * variável ativa, caso contrário, uma inativa. Uma variável inativa é transformada em ativa novamente
         * caso uma variável respectiva àquela variável inativa seja carregada/criada
         * 
         */
        $_SESSION['dataapi']['inactive_variables'] = array(); // RECEPTOR - VARIÁVEL I
        $_SESSION['dataapi']['active_variables'] = array(); // RECEPTOR - VARIÁVEL A

        /**
         * Para controle interno, fique a vontade para usar. Inseri pensando em sistemas de proteção que evitam a execução de muitas chamadas ao banco de dados (possíveis ataques)
         */
        $_SESSION['dataapi']['log'] = array();
        $_SESSION['dataapi']['log']['queries'] = array(); // Número de queries executados em um banco de dados
        $_SESSION['dataapi']['log']['start_time'] = time(); // Início da execução

        /**
         * Valor de quantos modulos foram criados/carregados
         */
        $_SESSION['dataapi']['log']['created'] = array();
        $_SESSION['dataapi']['log']['created']['databases'] = 0;
        $_SESSION['dataapi']['log']['created']['tables'] = 0;
        $_SESSION['dataapi']['log']['created']['variables'] = 0;
        $_SESSION['dataapi']['log']['created']['receptors'] = 0;

        $_SESSION['dataapi']['log']['created']['inactive_variables'] = 0;
        $_SESSION['dataapi']['log']['created']['active_variables'] = 0;
    }

    // Evita que os erros sejam exibidos ao cliente final para não haver exposição de dados sigilosos
    require_once("ExceptionHandler.php");
    //

    // Importes padrões
    require_once("modules/sql/query/DataResult.php");
    require_once("modules/sql/query/DataStatement.php");

    require_once("modules/Database.php");
    require_once("modules/Table.php");
    require_once("modules/Variable.php");
    require_once("modules/Receptor.php");

    require_once("modules/variables/ActiveVariable.php");
    require_once("modules/variables/InactiveVariable.php");
    //

    /**
     * @throws exception se nenhum banco de dados com as informações for encontrado
     */
    function getDatabase(DatabaseType $type, string $name) {
        if (isset($_SESSION['dataapi']['databases'][$type->getName()][$name])) {
            return $_SESSION['dataapi']['databases'][$type->getName()][$name];
        }
        throw new exception("Não foi possível encontrar nenhum banco de dados do tipo '". $type->getName() ."' de nome '". $name ."'");
    }
    /**
     * @throws exception se nenhuma tabela com as informações for encontrado
     */
    function getTable(Database $database, string $name) {
        if (isset($_SESSION['dataapi']['tables'][$database->getIdentification()][$name])) {
            return $_SESSION['dataapi']['tables'][$database->getIdentification()][$name];
        }
        throw new exception("Não foi possível encontrar nenhuma tabela no banco de dados '". $database->getName() ." ('". $database->getDatabaseType()->getName() ."')' de nome '". $name ."'");
    }
    /**
     * @throws exception se nenhum receptor com as informações for encontrado
     */
    function getReceptor(Table $table, string $bruteId) {
        if (isset($_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId])) {
            return $_SESSION['dataapi']['receptors'][$table->getIdentification()][$bruteId];
        }
        throw new exception("Não foi possível encontrar nenhum receptor na tabela '". $table->getName() ." ('". $table->getDatabase()->getName() ."')' de ID '". $bruteId ."'");
    }

    function getAPIDate(): string {
        $dt = new DateTime("now", new DateTimeZone('America/Sao_Paulo'));
        $dt->setTimestamp(time());
        $date = $dt->format('d/m/y H:i:s');

        return $date;
    }