<?php

namespace RebelCode\Storage\Resource\Pdo;

use ArrayAccess;
use Dhii\Util\String\StringableInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use PDOStatement;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can insert records into a database using PDO.
 *
 * @since [*next-version*]
 */
trait PdoInsertCapableTrait
{
    /**
     * Executes an INSERT SQL query, inserting records into the database.
     *
     * @since [*next-version*]
     *
     * @param array[]|ArrayAccess[]|stdClass[]|ContainerInterface[] $records A list of records to insert.
     *
     * @return PDOStatement The executed PDO statement.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from a record's container.
     */
    protected function _insert($records)
    {
        $processedRecords = $this->_preProcessRecords($records, $hashValueMap);
        $valueHashMap = array_flip($hashValueMap);

        $query = $this->_buildInsertSql(
            $this->_getSqlInsertTable(),
            $this->_getSqlInsertColumnNames(),
            $processedRecords,
            $valueHashMap
        );

        $statement = $this->_executePdoQuery(
            $query,
            $hashValueMap
        );

        return $statement;
    }

    /**
     * Pre-processes the list of records.
     *
     * @since [*next-version*]
     *
     * @param array[]|ArrayAccess[]|stdClass[]|ContainerInterface[] $records      A list of records.
     * @param array                                                 $valueHashMap A hash-to-value map reference to
     *                                                                            which new hash-value pairs are
     *                                                                            written.
     *
     * @return array The pre-processed record data list, as an array of record data associative sub-arrays.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from a record's container.
     */
    protected function _preProcessRecords($records, &$valueHashMap = [])
    {
        // Initialize variable, in case it was declared implicitly during the method call
        if ($valueHashMap === null) {
            $valueHashMap = [];
        }

        $newRecords = [];

        foreach ($records as $_idx => $_record) {
            $newRecords[$_idx] = $this->_extractRecordData($_record, $valueHashMap);
        }

        return $newRecords;
    }

    /**
     * Extracts record's data from the container and into an array.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $record       The record data container.
     * @param array                                         $hashValueMap A hash-to-value map reference to which new
     *                                                                    hash-value pairs are written.
     *
     * @return array The extracted record data as an associative array.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the record container.
     */
    protected function _extractRecordData($record, array &$hashValueMap = [])
    {
        $result = [];

        foreach ($this->_getSqlInsertFieldColumnMap() as $_field => $_column) {
            try {
                $_value = $this->_containerGet($record, $_field);
                // Calculate hash for value
                $_valueStr = $this->_normalizeString($_value);
                $_valueHash = $this->_getPdoValueHashString($_valueStr);
                // Add hash-to-value entry to map
                $hashValueMap[$_valueHash] = $_valueStr;
                // Add column-to-value entry to record data
                $result[$_column] = $_value;
            } catch (NotFoundExceptionInterface $nfe) {
                continue;
            }
        }

        return $result;
    }

    /**
     * Retrieves an entry from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface   $container The container or array to retrieve from.
     * @param string|StringableInterface $key       The key of the value to retrieve.
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
     * @param array|ContainerInterface   $container The container or array to search.
     * @param string|StringableInterface $key       The key to search for.
     *
     * @return bool True if the key was found in the container, false if not.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    abstract protected function _containerHas($container, $key);

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
     * Builds an INSERT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable     $table        The name of the table to insert into.
     * @param string[]|Stringable[] $columns      A list of columns names. The order is preserved in the built query.
     * @param array                 $rowSet       The record data as a map of column names to values.
     * @param array                 $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The built INSERT query.
     */
    abstract protected function _buildInsertSql(
        $table,
        array $columns,
        array $rowSet,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table name for use in SQL INSERT queries.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The table.
     */
    abstract protected function _getSqlInsertTable();

    /**
     * Retrieves the names of the columns for use in SQL INSERT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of column names.
     */
    abstract protected function _getSqlInsertColumnNames();

    /**
     * Retrieves the fields-to-columns mapping for use in INSERT SQL queries.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable A map containing the field names as keys and the matching column names as values.
     */
    abstract protected function _getSqlInsertFieldColumnMap();

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
}
