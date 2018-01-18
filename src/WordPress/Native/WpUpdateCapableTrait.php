<?php

namespace RebelCode\Storage\Resource\WordPress\Native;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use WP_Error;

/**
 * Common functionality for objects that can update posts in the WordPress database.
 *
 * @since [*next-version*]
 */
trait WpUpdateCapableTrait
{
    /**
     * Updates posts in the WordPress database.
     *
     * Due to limitations of the WordPress native DB API, the condition for this method is not allowed to be negated
     * and must be a hierarchy of expressions of types "or", "equal_to", "between" or "in". The terms of the
     * expressions are also limited to post IDs.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface        $changeSet A map of post/meta field names mapping to the values to change.
     * @param LogicalExpressionInterface|null $condition Optional condition for post IDs.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    protected function _update($changeSet, LogicalExpressionInterface $condition = null)
    {
        if ($condition !== null && $condition->isNegated()) {
            throw $this->_createInvalidArgumentException(
                $this->__('Negated conditions are not supported for native WordPress updates'),
                null,
                null,
                null
            );
        }

        $postIdField = $this->_getPostIdFieldName();

        if ($condition === null && !$this->_containerHas($changeSet, $postIdField)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Change set or condition must have an ID'),
                null,
                null,
                $condition
            );
        }

        $postIds = ($condition === null)
            ? [$this->_containerGet($changeSet, $postIdField)]
            : $this->_extractPostIdsFromExpression($condition);

        $postData = $this->_normalizeWpPostDataArray($changeSet);

        foreach ($postIds as $_postId) {
            $postData[$postIdField] = $_postId;

            $this->_wpUpdatePost($postData);
        }
    }

    /**
     * Retrieves the post data as an array for use in WordPress' insertion function.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $postData The post data array or container.
     *
     * @return array The prepared post data.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    abstract protected function _normalizeWpPostDataArray($postData);

    /**
     * Extracts post IDs from a logical expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $expression The expression to extract from.
     *
     * @return string[]|Stringable A list of post IDs.
     *
     * @throws InvalidArgumentException If the expression is
     */
    abstract protected function _extractPostIdsFromExpression(LogicalExpressionInterface $expression);

    /**
     * Retrieves the field name used in expressions for post ID terms.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The post ID field name.
     */
    abstract protected function _getPostIdFieldName();

    /**
     * Retrieves an entry from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $container The container or array to retrieve from.
     * @param string|Stringable        $key       The key of the value to retrieve.
     *
     * @return mixed The value mapped to by the key.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface If the key was not found in the container.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Checks if a container or data set has a specific entry, by key.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $container The container or array to search.
     * @param string|Stringable        $key       The key to search for.
     *
     * @return bool True if the key was found in the container, false if not.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    abstract protected function _containerHas($container, $key);

    /**
     * Wrapper method for the native WordPress post update function.
     *
     * @since [*next-version*]
     *
     * @param array $post The post data array, as documented
     *                    {@link https://developer.wordpress.org/reference/functions/wp_update_post/ here}.
     *
     * @return int|WP_Error The inserted ID on success, a zero of a WP_Error instance on failure.
     */
    abstract protected function _wpUpdatePost(array $post);

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
