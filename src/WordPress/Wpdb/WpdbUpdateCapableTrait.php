<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use PDOStatement;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Traversable;

/**
 * Common functionality for objects that can update records in a database using WPDB.
 *
 * @since [*next-version*]
 */
trait WpdbUpdateCapableTrait
{
    /**
     * Executes an UPDATE SQL query, updating records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface|Traversable $changeSet A map of field names mapping to the values to change.
     *                                                        If a container, it must implement `Countable`.
     * @param LogicalExpressionInterface|null      $condition Optional condition that records must satisfy to be
     *                                                        updated.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    protected function _update($changeSet, LogicalExpressionInterface $condition = null)
    {
        if ($this->_countIterable($changeSet) === 0) {
            throw $this->_createInvalidArgumentException(
                $this->__('Update set cannot be empty'),
                null,
                null,
                $changeSet
            );
        }

        // Hash map for the condition
        $valueHashMap = ($condition !== null)
            ? $this->_getWpdbExpressionHashMap($condition, $this->_getSqlSelectFieldNames())
            : [];
        // Fields to columns in change set, and hashes for values in change set
        $this->_preProcessChangeSet($changeSet, $valueHashMap);

        $query = $this->_buildUpdateSql(
            $this->_getSqlUpdateTable(),
            $changeSet,
            $condition,
            $valueHashMap
        );

        $this->_executeWpdbQuery($query, array_keys($valueHashMap));
    }

    /**
     * Extracts and retrieves the data hash map from the change set data.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $changeSet    An associative array mapping field names to their changed values.
     * @param array             $valueHashMap The value hash map to populate with new hashes.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     *
     * @return array The change set, with the keys changed from field names to column names.
     */
    protected function _preProcessChangeSet(array $changeSet, array &$valueHashMap)
    {
        $newChangeSet = [];

        foreach ($this->_getSqlUpdateFieldColumnMap() as $_field => $_column) {
            if (!$this->_containerHas($changeSet, $_field)) {
                continue;
            }

            $_value = $this->_containerGet($changeSet, $_field);
            $_valueStr = $this->_normalizeString($_value);
            $_hash = $this->_getWpdbValueHashString($_value, count($valueHashMap));

            $valueHashMap[$_valueStr] = $_hash;
            $newChangeSet[$_column] = $_value;
        }

        return $newChangeSet;
    }

    /**
     * Retrieves an entry from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $container The container or array to retrieve from.
     * @param string|Stringable        $key       The key of the value to retrieve.
     *
     * @return mixed The value mapped to by the key.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface If the key was not found in the container.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Checks if a container or data set has a specific entry, by key.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $container The container or array to search.
     * @param string|Stringable        $key       The key to search for.
     *
     * @return bool True if the key was found in the container, false if not.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    abstract protected function _containerHas($container, $key);

    /**
     * Hashes a query value for use in WPDB queries when argument interpolating.
     *
     * @since [*next-version*]
     *
     * @param string $value    The value to hash.
     * @param int    $position The position of the value in the hash map.
     *
     * @return string The string hash.
     */
    abstract protected function _getWpdbValueHashString($value, $position);

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
     * Retrieves the SQL SELECT query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlSelectFieldNames();

    /**
     * Retrieves the fields-to-columns mapping for use in UPDATE SQL queries.
     *
     * @since [*next-version*]
     *
     * @return array A map containing the field names as keys and the matching column names as values.
     */
    abstract protected function _getSqlUpdateFieldColumnMap();

    /**
     * Retrieves the expression value hash map for a given WPDB SQL condition, for use in WPDB args interpolation.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface   $condition The condition instance.
     * @param string[]|Stringable[] $ignore    A list of term names to ignore, typically column names.
     *
     * @return array A map of value names to their respective hashes.
     */
    abstract protected function _getWpdbExpressionHashMap(ExpressionInterface $condition, array $ignore = []);

    /**
     * Executes a query using wpdb.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $query     The query to execute.
     * @param array             $inputArgs An array of arguments to use for interpolating placeholders in the query.
     *
     * @return array A list of associative arrays, each representing a single record.
     */
    abstract protected function _executeWpdbQuery($query, array $inputArgs = []);

    /**
     * Counts the elements in an iterable.
     *
     * Is optimized to retrieve count from values that support it.
     * - If array, will count in regular way using count();
     * - If {@see Countable}, will do the same;
     * - If {@see IteratorAggregate}, will drill down into internal iterators
     * until the first {@see Countable} is encountered, in which case the same
     * as above will be done.
     * - In any other case, will apply {@see iterator_count()}, which means
     * that it will iterate over the whole traversable to determine the count.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $iterable The iterable to count. Must be finite.
     *
     * @return int The amount of elements.
     */
    abstract protected function _countIterable($iterable);

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

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
