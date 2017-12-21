<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Util\String\StringableInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Common functionality for objects that can build INSERT SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildInsertSqlCapableTrait
{
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
    protected function _buildInsertSql(
        $table,
        array $columns,
        array $rowSet,
        array $valueHashMap = []
    ) {
        $tableName   = $this->_escapeSqlReference($table);
        $columnsList = $this->_escapeSqlReferenceArray($columns);
        $values      = $this->_buildSqlValuesList($columns, $rowSet, $valueHashMap);

        $query = sprintf(
            'INSERT INTO %1$s (%2$s) %3$s',
            $tableName,
            $columnsList,
            $values
        );

        return sprintf('%s;', trim($query));
    }

    /**
     * Builds the VALUES portion of an INSERT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[] $columns      A list of columns names. The order is preserved in the built query.
     * @param array                 $rowSet       A list containing record data maps, mapping column names to row
     *                                            values.
     * @param array                 $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The built VALUES list or an empty string if the row set has no entries.
     */
    protected function _buildSqlValuesList(
        array $columns,
        array $rowSet,
        array $valueHashMap = []
    ) {
        if (count($rowSet) === 0) {
            return '';
        }

        $values = [];

        foreach ($rowSet as $_rowData) {
            $values[] = $this->_buildSqlRowValues($columns, $_rowData, $valueHashMap);
        }

        return sprintf('VALUES %s', implode(', ', $values));
    }

    /**
     * Builds the values for a single row.
     *
     * @since [*next-version*]
     *
     * @param array $columns      The list of columns, used to sort and exclude non-database row data.
     * @param array $rowData      The row data, as a map of column names to row values.
     * @param array $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The build row values as a comma separated list in parenthesis.
     */
    protected function _buildSqlRowValues(
        array $columns,
        array $rowData,
        array $valueHashMap = []
    ) {
        $data = [];

        foreach ($columns as $_columnName) {
            if (!isset($rowData[$_columnName])) {
                continue;
            }

            $_value = $rowData[$_columnName];

            $_valueKey = $this->_normalizeString($_value);

            $_realValue = isset($valueHashMap[$_valueKey])
                ? $valueHashMap[$_valueKey]
                : $this->_sanitizeSqlValue($_value);

            $data[$_columnName] = $_realValue;
        }

        $commaList = implode(', ', $data);

        return sprintf('(%s)', $commaList);
    }

    /**
     * Sanitizes an SQL value and normalizes it into a string for use in queries.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The input value.
     *
     * @return string The output value, sanitized and normalized to a string.
     */
    protected function _sanitizeSqlValue($value)
    {
        return (is_string($value) || $value instanceof StringableInterface)
            ? sprintf('"%s"', $this->_normalizeString($value))
            : $value;
    }

    /**
     * Escapes a reference string for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $reference The reference string to escape.
     *
     * @return string The escaped reference string.
     */
    abstract protected function _escapeSqlReference($reference);

    /**
     * Escapes an array of reference strings into a comma separated string list for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[] $array The array of strings to transform.
     *
     * @return string The comma separated string list.
     */
    abstract protected function _escapeSqlReferenceArray(array $array);

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
