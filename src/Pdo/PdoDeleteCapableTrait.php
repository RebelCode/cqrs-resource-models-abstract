<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use PDOStatement;

/**
 * Common functionality for objects that can delete records in a database via PDO.
 *
 * @since [*next-version*]
 */
trait PdoDeleteCapableTrait
{
    /**
     * Executes a DELETE SQL query, deleting records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition Optional condition that records must satisfy to be deleted.
     *
     * @return PDOStatement The executed PDO statement.
     */
    protected function _delete(LogicalExpressionInterface $condition = null)
    {
        $fieldNames = $this->_getSqlDeleteFieldNames();
        $valueHashMap = ($condition !== null)
            ? $this->_getPdoExpressionHashMap($condition, $fieldNames)
            : [];

        $query = $this->_buildDeleteSql(
            $this->_getSqlDeleteTable(),
            $condition,
            $valueHashMap
        );

        $statement = $this->_executePdoQuery($query, $valueHashMap);

        return $statement;
    }

    /**
     * Builds a DELETE SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable               $table        The name of the table to delete from.
     * @param LogicalExpressionInterface|null $condition    Optional condition that records must satisfy to be deleted.
     * @param string[]|Stringable[]           $valueHashMap Optional mapping of term names to their hashes
     *
     * @return string The built DELETE query.
     */
    abstract protected function _buildDeleteSql(
        $table,
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names related to this resource model.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlDeleteTable();

    /**
     * Retrieves the SQL DELETE query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlDeleteFieldNames();

    /**
     * Retrieves the expression value hash map for a given SQL condition, for use in PDO parameter binding.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface   $condition The condition instance.
     * @param string[]|Stringable[] $ignore    A list of term names to ignore, typically column names.
     *
     * @return array A map of value names to their respective hashes.
     */
    abstract protected function _getPdoExpressionHashMap(ExpressionInterface $condition, array $ignore = []);

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
    abstract protected function _executePdoQuery($query, array $inputArgs = []);
}
