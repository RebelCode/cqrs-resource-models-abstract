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
     * @param array|Traversable $postData The post data.
     *
     * @return array The prepared post data.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    protected function _normalizeWpPostDataArray($postData)
    {
        $fields = $this->_getWpPostDataFieldsToKeysMap();

        // Separate post data from meta data
        $data = [];
        $meta = [];
        foreach ($postData as $_key => $_value) {
            // If key unknown, treat as meta
            if (!isset($fields[$_key])) {
                $meta[$_key] = $_value;
                continue;
            }
            // De-alias field to key and add to data
            $_realKey = $this->_normalizeString($fields[$_key]);
            $data[$_realKey] = $_value;
        }

        // Add meta to post data
        $metaField = $this->_getWpPostDataMetaFieldKey();
        $data[$metaField] = $meta;

        return $data;
    }

    /**
     * Retrieves a map of string field names corresponding to known post data keys.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of post field key strings.
     */
    abstract protected function _getWpPostDataFieldsToKeysMap();

    /**
     * Retrieves the key where meta data is found in post data arrays.
     *
     * @since [*next-version*]
     *
     * @return string The post meta key.
     */
    abstract protected function _getWpPostDataMetaFieldKey();

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
