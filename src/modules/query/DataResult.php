<?php
namespace ItsLaivy\DataAPI\Query;

abstract class DataResult {

    public abstract function columns(): int;
    public abstract function results(): array;

}

