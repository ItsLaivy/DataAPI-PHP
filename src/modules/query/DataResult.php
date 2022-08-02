<?php
namespace DataAPI\System;

abstract class DataResult {

    public abstract function columns(): int;
    public abstract function results(): array;

}

