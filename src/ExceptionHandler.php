<?php
namespace ItsLaivy\DataAPI\System;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

#[NoReturn] function errorHandler(int $errno, string $errstr, string $errfile, int $errline): void {
    echo "Erro na API de dados";

    error_log('['.getAPIDate().'] "'. $errno . '" -> '. $errstr . ' ('.$errfile.':'.$errline.')', 3, dirname(__FILE__) . '/errors.log');
    error_log(PHP_EOL . "------------=------------" . PHP_EOL, 3, dirname(__FILE__) . '/errors.log');

    exit;
}
#[NoReturn] function throwableHandler(Throwable $throwable): void {
    echo "Erro na API de dados";

    error_log('['.getAPIDate().'] ('.$throwable->getCode().') '. $throwable, 3, dirname(__FILE__) . '/errors.log');
    error_log(PHP_EOL . "------------=------------" . PHP_EOL, 3, dirname(__FILE__) . '/errors.log');

    exit;
}

set_error_handler('DataAPI\System\errorHandler');
set_exception_handler('DataAPI\System\throwableHandler');