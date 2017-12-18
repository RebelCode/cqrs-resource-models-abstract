<?php

namespace RebelCode\Storage\Resource\Pdo;

use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use PDO;

/**
 * Common functionality for objects that are aware of a PDO instance.
 *
 * @since [*next-version*]
 */
trait PdoAwareTrait
{
    /**
     * The PDO instance associated with this instance.
     *
     * @since [*next-version*]
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Retrieves the PDO instance associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return PDO The PDO instance.
     */
    protected function _getPdo()
    {
        return $this->pdo;
    }

    /**
     * Sets the PDO instance for this instance.
     *
     * @since [*next-version*]
     *
     * @param PDO|null $pdo The PDO instance, or null.
     */
    protected function _setPdo($pdo)
    {
        if ($pdo !== null && !($pdo instanceof PDO)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a PDO instance'),
                null,
                null,
                $pdo
            );
        }

        $this->pdo = $pdo;
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
