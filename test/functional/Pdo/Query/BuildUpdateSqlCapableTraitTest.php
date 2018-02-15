<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\BuildUpdateSqlCapableTrait}.
 *
 * @since [*next-version*]
 */
class BuildUpdateSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\BuildUpdateSqlCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods Optional additional mock methods.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            array_merge(
                                $methods,
                                [
                                    '_escapeSqlReference',
                                    '_renderSqlExpression',
                                    '_buildSqlWhereClause',
                                    '_normalizeString',
                                    '_countIterable',
                                    '_createInvalidArgumentException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_escapeSqlReference')->willReturnArgument(0);
        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_countIterable')->willReturnCallback(
            function($i) {
                return count($i);
            }
        );
        $mock->method('_normalizeString')->willReturnArgument(0);

        return $mock;
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface The created expression instance.
     */
    public function createExpression($type, $terms)
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->new();
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated Optional negation flag.
     *
     * @return LogicalExpressionInterface The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        return $this->mock('Dhii\Expression\LogicalExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->isNegated($negated)
                    ->new();
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the UPDATE SQL build method.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlUpdateSet()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $changeSet = [
            'age'  => $cExpr1 = $this->createExpression('plus', ['age', 1]),
            'name' => $cExpr2 = $this->createExpression('string', ['foobar']),
        ];
        $valueHashMap = [
            '1'      => ':123',
            'foobar' => ':456',
        ];
        $subject->expects($this->exactly(2))
                ->method('_renderSqlExpression')
                ->withConsecutive([$cExpr1, $valueHashMap], [$cExpr2, $valueHashMap])
                ->willReturnOnConsecutiveCalls(
                    'age + 1',
                    '"foobar"'
                );
        $expected = 'SET `age` = age + 1, `name` = "foobar"';

        $this->assertEquals(
            $expected,
            $reflect->_buildSqlUpdateSet($changeSet, $valueHashMap),
            'Expected and retrieved query portions do not match.'
        );
    }

    /**
     * Tests the UPDATE SQL build method with a change set of expressions.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSqlExpressions()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = 'my_table';
        $changeSet = [
            'age'  => $cExpr1 = $this->createExpression('plus', ['age', 1]),
            'name' => $cExpr2 = $this->createExpression('string', ['foobar']),
        ];
        $valueHashMap = [
            '1'      => ':123',
            'foobar' => ':456',
        ];
        $subject->expects($this->exactly(2))
                ->method('_renderSqlExpression')
                ->withConsecutive([$cExpr1, $valueHashMap], [$cExpr2, $valueHashMap])
                ->willReturnOnConsecutiveCalls(
                    'age + 1',
                    '"foobar"'
                );
        $set = 'SET `age` = age + 1, `name` = "foobar"';

        $condition = $this->createLogicalExpression('equal', ['name', 'foo']);
        $where = 'WHERE name = "foo"';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);

        $expected = "UPDATE $table $set $where;";

        $this->assertEquals(
            $expected,
            $reflect->_buildUpdateSql($table, $changeSet, $condition, $valueHashMap),
            'Expected and retrieved UPDATE queries do not match.'
        );
    }

    /**
     * Tests the UPDATE SQL build method with a change set of values.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSqlValues()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = 'my_table';
        $changeSet = [
            'name'    => 'foo',
            'surname' => 'bar',
        ];
        $valueHashMap = [
            '10'  => ':123',
            'foo' => ':456',
        ];
        $set = 'SET `name` = :456, `surname` = "bar"';

        $condition = $this->createLogicalExpression('equal', ['age', 10]);
        $where = 'WHERE age = :123';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);

        $expected = "UPDATE $table $set $where;";

        $this->assertEquals(
            $expected,
            $reflect->_buildUpdateSql($table, $changeSet, $condition, $valueHashMap),
            'Expected and retrieved UPDATE queries do not match.'
        );
    }

    /**
     * Tests the UPDATE SQL build method without a condition.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSqlNoCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = 'my_table';
        $changeSet = [
            'age'  => $cExpr1 = $this->createExpression('plus', ['age', 1]),
            'name' => $cExpr2 = $this->createExpression('string', ['foobar']),
        ];
        $valueHashMap = [
            '1'      => ':123',
            'foobar' => ':456',
        ];
        $subject->expects($this->exactly(2))
                ->method('_renderSqlExpression')
                ->withConsecutive([$cExpr1, $valueHashMap], [$cExpr2, $valueHashMap])
                ->willReturnOnConsecutiveCalls(
                    'age + 1',
                    '"foobar"'
                );
        $set = 'SET `age` = age + 1, `name` = "foobar"';

        $condition = null;
        $where = '';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);

        $expected = "UPDATE $table $set;";

        $this->assertEquals(
            $expected,
            $reflect->_buildUpdateSql($table, $changeSet, $condition, $valueHashMap),
            'Expected and retrieved UPDATE queries do not match.'
        );
    }

    /**
     * Tests the UPDATE SQL build method with an empty change set to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testBuildUpdateSqlNoChangeSet()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_buildUpdateSql('my_table', []);
    }
}
