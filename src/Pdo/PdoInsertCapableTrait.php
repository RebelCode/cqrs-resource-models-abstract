<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Util\String\StringableInterface as Stringable;
use PDOStatement;

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
     * @param array $records A list of containers, each containing the record data to insert.
     *
     * @return PDOStatement The executed PDO statement.
     */
    protected function _insert(array $records)
    {
        $valueHashMap = array_map([$this, '_getSqlInsertValueHashMap'], $records);

        $query = $this->_buildInsertSql(
            $this->_getSqlInsertTable(),
            $this->_getSqlInsertColumnNames(),
            $records,
            $valueHashMap
        );

        $statement = $this->_executePdoQuery($query);

        return $statement;
    }

    /**
     * Extracts and retrieves the data hash map from record data.
     *
     * @since [*next-version*]
     *
     * @param array $record An associative array mapping field names to their values for a single record.
     *
     * @return array The generated value hash map, mapping values to their respective hashes.
     */
    protected function _getSqlInsertValueHashMap(array $record)
    {
        $map = [];

        foreach ($this->_getSqlInsertFieldColumnMap() as $_field => $_column) {
            if (!isset($record[$_field])) {
                continue;
            }

            $_value = $record[$_field];
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
     * @return array A map containing the field names as keys and the matching column names as values.
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
}
