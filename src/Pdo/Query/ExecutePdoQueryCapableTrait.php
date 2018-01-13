<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use PDOStatement;

/**
 * Common functionality for objects that can execute queries using PDO.
 *
 * @since [*next-version*]
 */
trait ExecutePdoQueryCapableTrait
{
    /**
     * Executes a given SQL query using PDO.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $query     The query to invoke.
     * @param array             $inputArgs The input arguments to use when executing the query.
     *
     * @return PDOStatement The executed statement.
     */
    protected function _executePdoQuery($query, array $inputArgs = [])
    {
        $statement = $this->_getPdo()->prepare($query);

        $statement->execute($inputArgs);

        return $statement;
    }

    /**
     * Retrieves the PDO instance.
     *
     * @since [*next-version*]
     *
     * @return PDO The pdo instance.
     */
    abstract protected function _getPdo();
}
