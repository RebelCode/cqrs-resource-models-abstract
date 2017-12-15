<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Output\Exception\RendererExceptionInterface;
use Dhii\Output\Exception\TemplateRenderExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Traversable;

/**
 * Common functionality for objects that can build JOIN conditions.
 *
 * @since [*next-version*]
 */
trait BuildSqlJoinsCapableTrait
{
    /**
     * Builds an SQL JOIN clause from a list of join conditions.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface[]|Traversable $joinConditions A list of JOIN conditions, keyed by table name.
     * @param string[]|Stringable[]                    $columnMap      Optional mapping of field names to column names.
     * @param string[]|Stringable[]                    $valueHashMap   Optional mapping of term names to their hashes.
     *
     * @return string|String The built SQL JOIN clause.
     */
    protected function _buildSqlJoins(array $joinConditions, array $columnMap = [], array $valueHashMap = [])
    {
        $joins = [];

        foreach ($joinConditions as $_table => $_condition) {
            $_escTable = $this->_escapeSqlReference($_table);
            $_rCondition = $this->_renderSqlCondition($_condition, $columnMap, $valueHashMap);
            $_joinType = $this->_getSqlJoinType($_condition);
            $joins [] = sprintf('%1$s JOIN %2$s ON %3$s', $_joinType, $_escTable, $_rCondition);
        }

        return implode(' ', $joins);
    }

    /**
     * Retrieves the SQL JOIN type keyword(s) for a given join expression.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface $expression The expression.
     *
     * @return string The SQL join type keyword(s).
     */
    abstract protected function _getSqlJoinType(ExpressionInterface $expression);

    /**
     * Renders an expression as an SQL condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface $condition    The condition to render.
     * @param string[]|Stringable[]      $columnMap    Optional mapping of field names to column names.
     * @param string[]|Stringable[]      $valueHashMap Optional mapping of term names to their hashes.
     *
     * @return string|Stringable The rendered condition.
     *
     * @throws RendererExceptionInterface If an error occurred while rendering.
     * @throws TemplateRenderExceptionInterface If the renderer failed to render the expression and context.
     */
    abstract protected function _renderSqlCondition(
        LogicalExpressionInterface $condition,
        array $columnMap = [],
        array $valueHashMap = []
    );

    /**
     * Escapes a reference string for use in SQL queries.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $reference The reference string to escape.
     *
     * @return string The escaped reference string.
     */
    abstract protected function _escapeSqlReference($reference);

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
