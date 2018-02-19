<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of SQL JOIN conditions.
 *
 * @since [*next-version*]
 */
trait SqlJoinConditionsAwareTrait
{
    /**
     * The join conditions, with table names as keys.
     *
     * @since [*next-version*]
     *
     * @var LogicalExpressionInterface[]
     */
    protected $joinConditions;

    /**
     * Retrieves the JOIN conditions associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return LogicalExpressionInterface[] The join conditions, keyed by table name.
     */
    protected function _getSqlJoinConditions()
    {
        return $this->joinConditions;
    }

    /**
     * Sets the SQL JOIN conditions for this instance.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface[] $joinConditions The JOIN conditions, keyed by table.
     *
     * @throws InvalidArgumentException If the argument contains an invalid key or value.
     */
    protected function _setSqlJoinConditions($joinConditions)
    {
        $array = $this->_normalizeArray($joinConditions);

        foreach ($array as $_key => $_value) {
            if (!($_value instanceof LogicalExpressionInterface)) {
                throw $this->_createInvalidArgumentException(
                    $this->__('Argument contains an invalid value'),
                    null,
                    null,
                    $joinConditions
                );
            }
            if (!is_string($_key)) {
                throw $this->_createInvalidArgumentException(
                    $this->__('Argument contains an invalid key'),
                    null,
                    null,
                    $joinConditions
                );
            }
        }

        $this->joinConditions = $array;
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
