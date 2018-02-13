<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Output\TemplateInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Common functionality for objects that are aware of a template that renders SQL conditions.
 *
 * @since [*next-version*]
 */
trait SqlConditionTemplateAwareTrait
{
    /**
     * The SQL condition template instance.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface|null
     */
    protected $sqlConditionTemplate;

    /**
     * Retrieves the SQL condition template associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return TemplateInterface|null The SQL condition template, if any.
     */
    protected function _getSqlConditionTemplate()
    {
        return $this->sqlConditionTemplate;
    }

    /**
     * Sets the SQL condition template for this instance.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface|null $sqlConditionTemplate The SQL condition template, if any.
     */
    protected function _setSqlConditionTemplate($sqlConditionTemplate)
    {
        if ($sqlConditionTemplate !== null && !($sqlConditionTemplate instanceof TemplateInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a template or null value'),
                null,
                null,
                $sqlConditionTemplate
            );
        }

        $this->sqlConditionTemplate = $sqlConditionTemplate;
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
