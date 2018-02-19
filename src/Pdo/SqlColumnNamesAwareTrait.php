<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Common functionality for objects that can provide SQL columns names from a field-column map.
 *
 * @since [*next-version*]
 */
trait SqlColumnNamesAwareTrait
{
    /**
     * Retrieves the names of the columns used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of column names.
     */
    protected function _getSqlColumnNames()
    {
        return array_values($this->_getSqlFieldColumnMap());
    }

    /**
     * Retrieves the mapping of field names to table columns.
     *
     * @since [*next-version*]
     *
     * @return EntityFieldInterface[] A map of field names mapping to entity field instances.
     */
    abstract protected function _getSqlFieldColumnMap();
}
