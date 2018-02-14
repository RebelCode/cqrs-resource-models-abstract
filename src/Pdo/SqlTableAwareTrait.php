<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Exception as RootException;

/**
 * Common functionality for objects that are aware of an SQL table name.
 *
 * @since [*next-version*]
 */
trait SqlTableAwareTrait
{
    /**
     * The table name.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $sqlTable;

    /**
     * Retrieves the SQL table associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The table name.
     */
    protected function _getSqlTable()
    {
        return $this->sqlTable;
    }

    /**
     * Set the SQL table for this instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $sqlTable The SQL table name.
     *
     * @throws InvalidArgumentException If the argument is not a string or stringable object.
     */
    protected function _setSqlTable($sqlTable)
    {
        if (!is_string($sqlTable) && !($sqlTable instanceof Stringable)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a string or stringable object'),
                null,
                null,
                $sqlTable
            );
        }

        $this->sqlTable = $sqlTable;
    }

    /**
     * Creates a new invalid argument exception.
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
