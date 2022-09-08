<?php
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use ItsLaivy\DataAPI\Modules\Query\DataResult;
use mysqli_result;

class MySQLResult extends DataResult {

    private readonly mysqli_result $result;

    public function __construct(null|mysqli_result $result) {
        if ($result != null) {
            $this->result = $result;
        }
    }

    public function columns(): int {
        if (isset($this->result)) {
            return $this->result->num_rows;
        } else {
            return 0;
        }
    }

    public function results(): array {
        if (isset($this->result)) {
            $result = array();

            while ($row = $this->result->fetch_row()) {
                $result[] = $row;
            }

            return $result;
        }
        return array();
    }

}