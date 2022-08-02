<?php

require_once(dirname(__FILE__) . '/../../DataAPI.php');

abstract class DataResult {

    public abstract function columns(): int;
    public abstract function results(): array;

}

