<?php
namespace ItsLaivy\DataAPI\Modules\SQL\MySQL;

use ItsLaivy\DataAPI\Modules\Query\DataResult;
use ItsLaivy\DataAPI\Modules\Query\DataStatement;
use mysqli_stmt;
use Throwable;

class MySQLStatement extends DataStatement {

    private readonly mysqli_stmt $statement;

    public function __construct(MySQLDatabase $database, string $query) {
        parent::__construct($database, $query);

        try {
            $this->statement = $database->getDatabaseType()->getTreeConnection(true)->prepare($query);
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    public function execute(): DataResult {
        $result = null;
        try {
            $this->statement->execute();
            $result = $this->statement->get_result();
            if ($result === false) $result = null;
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
        return new MySQLResult($result);
    }

    public function close(): void {
        try {
            $this->statement->close();
        } catch (Throwable $e) {
            $this->getDatabase()->getDatabaseType()->throws($e);
        }
    }

    /**
     * @return mysqli_stmt
     */
    public function getStatement(): mysqli_stmt {
        return $this->statement;
    }

}