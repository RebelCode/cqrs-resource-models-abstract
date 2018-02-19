<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of an SQL field-to-column map.
 *
 * @since [*next-version*]
 */
trait SqlFieldColumnMapAwareTrait
{
    /**
     * The field to column map, with field names as keys and column names as values.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable[]
     */
    protected $fieldColumnMap;

    /**
     * Retrieves the field-column map associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable[] A map of field names mapping to entity field instances.
     */
    protected function _getSqlFieldColumnMap()
    {
        return $this->fieldColumnMap;
    }

    /**
     * Sets the field-column map for this instance.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $map The field-to-column map.
     *
     * @throws InvalidArgumentException If the argument is not a valid map.
     */
    protected function _setSqlFieldColumnMap($map)
    {
        $array = $this->_normalizeArray($map);

        foreach ($array as $_key => $_value) {
            if (!is_string($_value) && !($_value instanceof Stringable)) {
                throw $this->_createInvalidArgumentException(
                    $this->__('Argument contains a non-string/non-stringable value'),
                    null,
                    null,
                    $map
                );
            }
            if (!is_string($_key)) {
                throw $this->_createInvalidArgumentException(
                    $this->__('Argument contains a non-string key'),
                    null,
                    null,
                    $map
                );
            }
        }

        $this->fieldColumnMap = $array;
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
