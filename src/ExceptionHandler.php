<?php
    require_once("ExceptionHandler.php");

    function errorHandler(int $errno, string $errstr, string $errfile, int $errline) {
        echo "Erro na API de dados (²)";

        error_log('['.getAPIDate().'] "'. $errno . '" -> '. $errstr . ' ('.$errfile.':'.$errline.')', 3, dirname(__FILE__).'/errors.log');
        error_log(PHP_EOL . "------------=------------" . PHP_EOL, 3, dirname(__FILE__).'/errors.log');

        exit;
    }
    function throwableHandler(Throwable $throwable) {
        echo "Erro na API de dados (¹)";

        error_log('['.getAPIDate().'] '. $throwable, 3, dirname(__FILE__).'/errors.log');
        error_log(PHP_EOL . "------------=------------" . PHP_EOL, 3, dirname(__FILE__).'/errors.log');

        exit;
    }

    set_error_handler('errorHandler');
    set_exception_handler('throwableHandler');