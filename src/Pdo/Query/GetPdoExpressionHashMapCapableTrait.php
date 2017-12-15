<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Expression\ExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Common functionality for objects that can generate an expression value hash map for use in PDO parameter binding.
 *
 * @since [*next-version*]
 */
trait GetPdoExpressionHashMapCapableTrait
{
    /**
     * Retrieves the expression value hash map for a given SQL condition, for use in PDO parameter binding.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface   $condition The condition instance.
     * @param string[]|Stringable[] $ignore    A list of term names to ignore, typically column names.
     *
     * @return array A map of value names to their respective hashes.
     */
    protected function _getPdoExpressionHashMap(ExpressionInterface $condition, array $ignore = [])
    {
        $values = [];

        foreach ($condition->getTerms() as $_idx => $_term) {
            if ($_term instanceof ExpressionInterface) {
                $values = array_merge($values, $this->_getPdoExpressionHashMap($_term, $ignore));

                continue;
            }

            $_termStr = $this->_normalizeString($_term);

            if (!in_array($_termStr, $ignore)) {
                $values[$_termStr] = $this->_getPdoTermHash($_termStr);
            }
        }

        return $values;
    }

    /**
     * Hashes an expression term in PDO parameter format.
     *
     * @since [*next-version*]
     *
     * @param string $term The term name to hash.
     *
     * @return string The string hash.
     */
    protected function _getPdoTermHash($term)
    {
        return ':' . hash('crc32b', $term);
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);
}
