<?php

namespace RebelCode\Storage\Resource\Pdo\Query;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Common functionality for objects that can build UPDATE SQL queries.
 *
 * @since [*next-version*]
 */
trait BuildUpdateSqlCapableTrait
{
    /**
     * Builds a UPDATE SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable               $table        The name of the table to insert into.
     * @param ExpressionInterface[]           $changeSet    The changes as a map of field names to expression values.
     * @param LogicalExpressionInterface|null $condition    Optional condition that records must satisfy to be updated.
     * @param array                           $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If the change set is empty.
     *
     * @return string The built UPDATE query.
     */
    protected function _buildUpdateSql(
        $table,
        array $changeSet,
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = null
    ) {
        if (count($changeSet) === 0) {
            throw $this->_createInvalidArgumentException(
                $this->__('Change set cannot be empty'),
                null,
                null,
                $changeSet
            );
        }

        $tableName = $this->_escapeSqlReference($table);
        $updateSet = $this->_buildSqlUpdateSet($changeSet, $valueHashMap);
        $where     = $this->_buildSqlWhereClause($condition, $valueHashMap);

        $query = sprintf(
            'UPDATE %1$s %2$s %3$s',
            $tableName,
            $updateSet,
            $where
        );

        return sprintf('%s;', trim($query));
    }

    /**
     * Builds the SQL UPDATE SET query string portion.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface[] $changeSet    The changes as a map of field names to expression values.
     * @param array                 $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The built SQL UPDATE SET portion string.
     */
    protected function _buildSqlUpdateSet(array $changeSet, array $valueHashMap)
    {
        $_changes = [];

        foreach ($changeSet as $_field => $_expression) {
            $_rExpression = $this->_renderSqlExpression($_expression, $valueHashMap);
            $_changes[]   = sprintf('%1$s = %2$s', $_field, $_rExpression);
        }

        $changeStr  = implode(', ', $_changes);
        $setPortion = sprintf('SET %s', $changeStr);

        return $setPortion;
    }

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
     * Renders an SQL expression.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface $expression   The expression to render.
     * @param array               $valueHashMap Optional map of value names and their hashes.
     *
     * @return string|Stringable The rendered expression.
     */
    abstract protected function _renderSqlExpression(ExpressionInterface $expression, array $valueHashMap = []);

    /**
     * Builds the SQL WHERE clause query string portion.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition    Optional condition instance.
     * @param array                           $valueHashMap Optional map of value names and their hashes.
     *
     * @return string The SQL WHERE clause query portion.
     */
    abstract protected function _buildSqlWhereClause(
        LogicalExpressionInterface $condition = null,
        array $valueHashMap = []
    );

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
