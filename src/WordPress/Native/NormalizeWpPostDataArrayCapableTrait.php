<?php

namespace RebelCode\Storage\Resource\WordPress\Native;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Traversable;

/**
 * Common functionality for objects that can normalize WordPress post data arrays, for use in WordPress' native post
 * CRUD functions.
 *
 * @since [*next-version*]
 */
trait NormalizeWpPostDataArrayCapableTrait
{
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
    protected function _normalizeWpPostDataArray($postData)
    {
        $data = [];

        foreach ($this->_getWpPostDataFieldKeys() as $_field) {
            $_field = $this->_normalizeString($_field);

            if ($this->_containerHas($postData, $_field)) {
                $data[$_field] = $this->_containerGet($postData, $_field);
            }
        }

        try {
            $metaInputKey = $this->_getWpPostDataMetaFieldName();
            $data[$metaInputKey] = $this->_getWpInsertPostMeta($postData);
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
     *
     * @throws InvalidArgumentException If the argument is not an array or traversable.
     */
    protected function _getWpInsertPostMeta($post)
    {
        $post = $this->_normalizeArray($post);
        $fields = array_flip($this->_getWpPostDataFieldKeys());
        $meta = array_diff_key($post, $fields);

        return $meta;
    }

    /**
     * Retrieves a list of strings that represent the known post field keys.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of post field key strings.
     */
    abstract protected function _getWpPostDataFieldKeys();

    /**
     * Retrieves the key where meta data is found in post data arrays.
     *
     * @since [*next-version*]
     *
     * @return string The post meta key.
     */
    abstract protected function _getWpPostDataMetaFieldName();

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
