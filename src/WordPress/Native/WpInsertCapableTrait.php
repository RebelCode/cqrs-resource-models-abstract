<?php

namespace RebelCode\Storage\Resource\WordPress\Native;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Traversable;
use WP_Error;

/**
 * Common functionality for objects that can insert posts into the WordPress database.
 *
 * @since [*next-version*]
 */
trait WpInsertCapableTrait
{
    /**
     * Inserts the list of posts into the WordPress database.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface[]|Traversable $posts A list of posts to insert, either as arrays or containers.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from a container posts.
     */
    protected function _insert($posts)
    {
        foreach ($posts as $_post) {
            $this->_wpInsertPost($this->_getWpInsertPostData($_post));
        }
    }

    /**
     * Retrieves the post data as an array for use in WordPress' insertion function.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $post The post array or container.
     *
     * @return array The post insert data array.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from container posts.
     */
    protected function _getWpInsertPostData($post)
    {
        $data = [];

        foreach ($this->_getWpInsertPostFieldKeys() as $_field) {
            $_field = $this->_normalizeString($_field);

            if ($this->_containerHas($post, $_field)) {
                $data[$_field] = $this->_containerGet($post, $_field);
            }
        }

        try {
            $metaInputKey = $this->_getWpInsertPostMetaKey();
            $data[$metaInputKey] = $this->_getWpInsertPostMeta($post);
        } catch (InvalidArgumentException $invalidArgumentException) {
            // do nothing - $post is not a traversable or array, so we cannot read meta data from it
        }

        return $data;
    }

    /**
     * Retrieves the meta data for a given post.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $post The post data array or traversable object.
     *
     * @return array An associative array containing the post meta key-value pairs.
     */
    protected function _getWpInsertPostMeta($post)
    {
        $post = $this->_normalizeArray($post);
        $fields = array_flip($this->_getWpInsertPostFieldKeys());
        $meta = array_diff_key($post, $fields);

        return $meta;
    }

    /**
     * Wrapper method for the native WordPress post insertion function.
     *
     * @since [*next-version*]
     *
     * @param array $post The post data array, as documented
     *                    {@link https://developer.wordpress.org/reference/functions/wp_insert_post/ here}.
     *
     * @return int|WP_Error The inserted ID on success, a zero of a WP_Error instance on failure.
     */
    abstract protected function _wpInsertPost(array $post);

    /**
     * Retrieves a list of strings that represent the known post field keys.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of post field key strings.
     */
    abstract protected function _getWpInsertPostFieldKeys();

    /**
     * Retrieves the key where meta data is found in post data arrays.
     *
     * @since [*next-version*]
     *
     * @return string The post meta key.
     */
    abstract protected function _getWpInsertPostMetaKey();

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
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);
}
