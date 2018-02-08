<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Expression\TermInterface;
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
     * @param TermInterface         $condition    The condition instance.
     * @param string[]|Stringable[] $ignore       A list of term names to ignore, typically column names.
     * @param array                 $valueHashMap The value hash map reference to write to.
     *
     * @return array A map of value names to their respective hashes.
     */
    protected function _getPdoExpressionHashMap(TermInterface $condition, array $ignore = [], array &$valueHashMap = [])
    {
        if ($valueHashMap === null) {
            $valueHashMap = [];
        }

        if ($condition instanceof LiteralTermInterface) {
            $value = $condition->getValue();
            $value = $this->_normalizeString($value);

            if (!in_array($value, $ignore)) {
                $valueHashMap[$value] = $this->_getPdoValueHashString($value);
            }
        }

        if ($condition instanceof ExpressionInterface) {
            foreach ($condition->getTerms() as $_idx => $_term) {
                $this->_getPdoExpressionHashMap($_term, $ignore, $valueHashMap);
            }
        }

        return $valueHashMap;
    }

    /**
     * Hashes a query value for use in PDO queries when parameter binding.
     *
     * @since [*next-version*]
     *
     * @param string $value The value to hash.
     *
     * @return string The string hash.
     */
    abstract protected function _getPdoValueHashString($value);

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
