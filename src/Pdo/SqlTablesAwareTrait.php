<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of SQL table names.
 *
 * @since [*next-version*]
 */
trait SqlTablesAwareTrait
{
    /**
     * An array of table names.
     *
     * @since [*next-version*]
     *
     * @var string[]|Stringable[]
     */
    protected $sqlTables;

    /**
     * Retrieves the SQL table names associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] The SQL tables names.
     */
    protected function _getSqlTables()
    {
        return $this->sqlTables;
    }

    /**
     * Sets the SQL table names for this instance.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[] $tables The SQL tables names.
     */
    protected function _setSqlTables($tables)
    {
        $array = $this->_normalizeArray($tables);

        foreach ($array as $_value) {
            if (!is_string($_value) && !($_value instanceof Stringable)) {
                throw $this->_createInvalidArgumentException(
                    $this->__('Argument contains a non-string/non-stringable value'),
                    null,
                    null,
                    $tables
                );
            }
        }

        $this->sqlTables = $array;
    }

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);

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
