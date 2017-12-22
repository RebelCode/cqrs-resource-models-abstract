<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

trait GetPdoValueHashStringCapableTrait
{
    /**
     * Hashes a query value for use in PDO queries when parameter binding.
     *
     * @since [*next-version*]
     *
     * @param int|float|bool|string|Stringable $value The value to hash.
     *
     * @return string The string hash.
     */
    protected function _getPdoValueHashString($value)
    {
        return ':' . hash('crc32b', $this->_normalizeString($value));
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param int|float|bool|string|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);
}
