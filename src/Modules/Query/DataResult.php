<?php
namespace ItsLaivy\DataAPI\Modules\Query;

abstract class DataResult {

    public abstract function columns(): int;
    public abstract function results(): array;

}

