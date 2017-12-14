<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Common functionality for objects that can escape SQL table and column references.
 *
 * @since [*next-version*]
 */
trait EscapeSqlReferenceCapableTrait
{
    /**
     * Escapes a reference string for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $reference The reference string to escape.
     *
     * @return string The escaped reference string.
     */
    protected function _escapeSqlReference($reference)
    {
        return (strlen($reference) > 0)
            ? sprintf('`%s`', $reference)
            : '';
    }

    /**
     * Escapes an array of reference strings into a comma separated string list for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[] $array The array of strings to transform.
     *
     * @return string The comma separated string list.
     */
    protected function _escapeSqlReferenceArray(array $array)
    {
        if (count($array) === 0) {
            return '';
        }

        $commaList = implode('`, `', $array);
        $escaped = sprintf('`%s`', $commaList);

        return $escaped;
    }
}
