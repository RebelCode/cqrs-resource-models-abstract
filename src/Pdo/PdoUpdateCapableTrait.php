<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use PDOStatement;

/**
 * Common functionality for objects that can update records in a database using PDO.
 *
 * @since [*next-version*]
 */
trait PdoUpdateCapableTrait
{
    /**
     * Executes an UPDATE SQL query, updating records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param array                           $changeSet A map of field names mapping to the values to change.
     * @param LogicalExpressionInterface|null $condition Optional condition that records must satisfy to be updated.
     *
     * @return PDOStatement The executed PDO statement.
     */
    protected function _update(array $changeSet, LogicalExpressionInterface $condition = null)
    {
        if (count($changeSet) === 0) {
            throw $this->_createInvalidArgumentException(
                $this->__('Update set cannot be empty'),
                null,
                null,
                $changeSet
            );
        }

        $changeSetHashMap = $this->_getSqlUpdateValueHashMap($changeSet);

        $fields       = array_keys($this->_getSqlUpdateFieldColumnMap());
        $valueHashMap = ($condition !== null)
            ? $this->_getPdoExpressionHashMap($condition, $fields)
            : [];

        $fullHashMap = array_merge($changeSetHashMap, $valueHashMap);

        $query = $this->_buildUpdateSql(
            $this->_getSqlUpdateTable(),
            $changeSet,
            $condition,
            $fullHashMap
        );

        $statement = $this->_executePdoQuery($query, $changeSetHashMap);

        return $statement;
    }

    /**
     * Extracts and retrieves the data hash map from the change set data.
     *
     * @since [*next-version*]
     *
     * @param array $changeSet An associative array mapping field names to their changed values.
     *
     * @return array The generated value hash map, mapping values to their respective hashes.
     */
    protected function _getSqlUpdateValueHashMap(array $changeSet)
    {
        $map = [];

        foreach ($this->_getSqlUpdateFieldColumnMap() as $_field => $_column) {
            if (!isset($changeSet[$_field])) {
                continue;
            }

            $_value = $changeSet[$_field];
            $_hash  = $this->_getPdoValueHashString($_value);

            $map[$_column] = $_hash;
        }

        return $map;
    }

    /**
     * Hashes a query value for use in PDO queries when parameter binding.
     *
     * @since [*next-version*]
     *
     * @param string $value The value to hash.
     *
     * @return string The string hash.
     */
    abstract protected function _getPdoValueHashString($value);

    /**
     * Builds a INSERT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable               $table        The name of the table to insert into.
     * @param ExpressionInterface[]           $changeSet    The changes as a map of field names to expression values.
     * @param LogicalExpressionInterface|null $condition    Optional condition that records must satisfy to be updated.
     * @param array                           $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If the change set is empty.
     *
     * @return string The built INSERT query.
     */
    abstract protected function _buildUpdateSql(
        $table,
        array $changeSet,
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
    abstract protected function _getSqlUpdateTable();

    /**
     * Retrieves the fields-to-columns mapping for use in UPDATE SQL queries.
     *
     * @since [*next-version*]
     *
     * @return array A map containing the field names as keys and the matching column names as values.
     */
    abstract protected function _getSqlUpdateFieldColumnMap();

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

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
