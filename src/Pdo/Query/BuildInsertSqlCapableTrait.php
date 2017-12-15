<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Common functionality for objects that can built INSERT SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildInsertSqlCapableTrait
{
    /**
     * Builds a INSERT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable     $table   The name of the table to insert into.
     * @param string[]|Stringable[] $columns A list of columns names. The order is preserved in the built query.
     * @param array                 $rowSet  A list containing record data maps, mapping column names to row values.
     *
     * @return string The built INSERT query.
     */
    protected function _buildInsertSql($table, array $columns, array $rowSet)
    {
        $tableName = $this->_escapeSqlReference($table);
        $columnsList = $this->_escapeSqlReferenceArray($columns);
        $values = $this->_buildSqlValuesList($columns, $rowSet);

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
     * @param string[]|Stringable[] $columns A list of columns names. The order is preserved in the built query.
     * @param array                 $rowSet  A list containing record data maps, mapping column names to row values.
     *
     * @return string The built VALUES list or an empty string if the row set has no entries.
     */
    protected function _buildSqlValuesList(array $columns, array $rowSet)
    {
        if (count($rowSet) === 0) {
            return '';
        }

        $values = [];

        foreach ($rowSet as $_rowData) {
            $values[] = $this->_buildSqlRowValues($columns, $_rowData);
        }

        return sprintf('VALUES %s', implode(', ', $values));
    }

    /**
     * Builds the values for a single row.
     *
     * @since [*next-version*]
     *
     * @param array $columns The list of columns, used to sort exclude non-database row data.
     * @param array $rowData The row data, as a map of column names to row values.
     *
     * @return string The build row values as a comma separated list in parenthesis.
     */
    protected function _buildSqlRowValues(array $columns, array $rowData)
    {
        $data = [];

        foreach ($columns as $_column) {
            if (!isset($rowData[$_column])) {
                continue;
            }

            $_value = $rowData[$_column];

            $data[] = (is_string($_value) || $_value instanceof Stringable)
                ? sprintf('"%s"', $_value)
                : $_value;
        }

        $commaList = implode(', ', $data);

        return sprintf('(%s)', $commaList);
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
}
