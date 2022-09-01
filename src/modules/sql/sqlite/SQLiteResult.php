<?php
namespace ItsLaivy\DataAPI\SQLite;

use ItsLaivy\DataAPI\System\DataResult;
use SQLite3Result;

class SQLiteResult extends DataResult {

    private readonly SQLite3Result $result;

    public function __construct(null|SQLite3Result $result) {
        if (isset($result)) {
            $this->result = $result;
        }
    }

    public function columns(): int {
        if (isset($this->result)) {
            return $this->result->numColumns();
        } else {
            return 0;
        }
    }

    public function results(): array {
        if (isset($this->result)) {
            $result = $this->result->fetchArray(SQLITE3_ASSOC);
            if ($result !== false) {
                return $result;
            }
        }
        return array();
    }

}

?>