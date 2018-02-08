<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Config\Exception\ConfigExceptionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use PDOStatement;
use Psr\Container\NotFoundExceptionInterface;
use Traversable;

/**
 * Common functionality for objects that can retrieve records from a database using PDO.
 *
 * @since [*next-version*]
 */
trait PdoSelectCapableTrait
{
    /**
     * Executes a SELECT SQL query, retrieving records from the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition Optional condition that records must satisfy.
     *                                                   If null, all records in the target table are retrieved.
     *
     * @throws ConfigExceptionInterface   If an error occurred while reading from the config.
     * @throws NotFoundExceptionInterface If required configuration was not found in the config.
     *
     * @return array|Traversable A list of retrieved records.
     */
    protected function _select(LogicalExpressionInterface $condition = null)
    {
        $fields       = $this->_getSqlSelectFieldNames();
        $valueHashMap = ($condition !== null)
            ? $this->_getPdoExpressionHashMap($condition, $fields)
            : [];

        $query = $this->_buildSelectSql(
            $this->_getSqlSelectColumns(),
            $this->_getSqlSelectTables(),
            $this->_getSqlSelectJoinConditions(),
            $condition,
            $valueHashMap
        );

        return $this->_executePdoQuery($query, array_flip($valueHashMap))->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Builds a SELECT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]           $columns        A list of names of columns to select.
     * @param array                           $tables         A list of names of tables to select from.
     * @param LogicalExpressionInterface[]    $joinConditions Optional list of JOIN conditions, keyed by table name.
     * @param LogicalExpressionInterface|null $whereCondition Optional WHERE condition.
     * @param array                           $valueHashMap   Optional map of value names and their hashes.
     *
     * @return string The built SQL query string.
     */
    abstract protected function _buildSelectSql(
        array $columns,
        array $tables,
        array $joinConditions = [],
        LogicalExpressionInterface $whereCondition = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @throws ConfigExceptionInterface   If an error occurred while reading from the config.
     * @throws NotFoundExceptionInterface If the table/entity name was not found in the config.
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlSelectTables();

    /**
     * Retrieves the names of the columns used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of column names.
     */
    abstract protected function _getSqlSelectColumns();

    /**
     * Retrieves the SQL SELECT query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlSelectFieldNames();

    /**
     * Retrieves the JOIN conditions used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return LogicalExpressionInterface[] An assoc. array of logical expressions, keyed by the joined table name.
     */
    abstract protected function _getSqlSelectJoinConditions();

    /**
     * Retrieves the expression value hash map for a given SQL condition, for use in PDO parameter binding.
     *
     * @since [*next-version*]
     *
     * @param TermInterface         $condition    The condition instance.
     * @param string[]|Stringable[] $ignore       A list of term names to ignore, typically column names.
     * @param array                 $valueHashMap The value hash map reference to write to.
     *
     * @return array A map of value names to their respective hashes.
     */
    abstract protected function _getPdoExpressionHashMap(
        TermInterface $condition,
        array $ignore = [],
        array &$valueHashMap = []
    );

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
