<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Common functionality for objects that can provide SQL field names from a field-column map.
 *
 * @since [*next-version*]
 */
trait SqlFieldNamesAwareTrait
{
    /**
     * Retrieves the SQL query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    protected function _getSqlFieldNames()
    {
        return array_keys($this->_getSqlFieldColumnMap());
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
