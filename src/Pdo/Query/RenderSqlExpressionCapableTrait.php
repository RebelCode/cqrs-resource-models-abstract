<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Expression\TermInterface;
use Dhii\Output\Exception\RendererExceptionInterface;
use Dhii\Output\Exception\TemplateRenderExceptionInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\Sql\Expression\SqlExpressionContextInterface as SqlCtx;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Common simple functionality for rendering SQL expressions by delegating to a template renderer.
 *
 * @since [*next-version*]
 */
trait RenderSqlExpressionCapableTrait
{
    /**
     * Renders an SQL expression.
     *
     * @since [*next-version*]
     *
     * @param TermInterface         $expression   The expression to render.
     * @param string[]|Stringable[] $valueHashMap Optional mapping of term names to their hashes.
     *
     * @throws RendererExceptionInterface       If an error occurred while rendering.
     * @throws TemplateRenderExceptionInterface If the renderer failed to render the expression and context.
     *
     * @return string|Stringable The rendered expression.
     */
    protected function _renderSqlExpression(TermInterface $expression, array $valueHashMap = [])
    {
        $template = $this->_getTemplateForSqlExpression($expression);

        if ($template === null) {
            throw $this->_createInvalidArgumentException(
                $this->__('Could not get a template renderer to render given expression'),
                null,
                null,
                $expression
            );
        }

        $columnMap = $this->_getSqlFieldColumnMap();
        $aliases = array_merge($columnMap, $valueHashMap);

        $context = [
            SqlCtx::K_EXPRESSION  => $expression,
            SqlCtx::K_ALIASES_MAP => $aliases,
        ];

        return $template->render($context);
    }

    /**
     * Retrieves a template renderer instance that can renderer the given expression.
     *
     * @since [*next-version*]
     *
     * @param TermInterface $expression The expression to render.
     *
     * @return TemplateInterface|null The template renderer instance, or null if a template renderer could not be
     *                                resolved for the given expression.
     */
    abstract protected function _getTemplateForSqlExpression(TermInterface $expression);

    /**
     * Retrieves the mapping of field names to table columns.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A map of field names mapping to entity field instances.
     */
    abstract protected function _getSqlFieldColumnMap();

    /**
     * Creates a new Dhii invalid argument exception.
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
