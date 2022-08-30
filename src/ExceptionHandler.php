<?php
namespace DataAPI\System;

use Throwable;

function errorHandler(int $errno, string $errstr, string $errfile, int $errline) {
    echo "Erro na API de dados";

    error_log('['.getAPIDate().'] "'. $errno . '" -> '. $errstr . ' ('.$errfile.':'.$errline.')', 3, dirname(__FILE__) . '/errors.log');
    error_log(PHP_EOL . "------------=------------" . PHP_EOL, 3, dirname(__FILE__) . '/errors.log');

    exit;
}
function throwableHandler(Throwable $throwable) {
    echo "Erro na API de dados";

    error_log('['.getAPIDate().'] ('.$throwable->getCode().') '. $throwable, 3, dirname(__FILE__) . '/errors.log');
    error_log(PHP_EOL . "------------=------------" . PHP_EOL, 3, dirname(__FILE__) . '/errors.log');

    exit;
}

set_error_handler('DataAPI\System\errorHandler');
set_exception_handler('DataAPI\System\throwableHandler');