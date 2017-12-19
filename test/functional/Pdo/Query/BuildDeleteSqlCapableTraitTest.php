<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\BuildDeleteSqlCapableTrait}.
 *
 * @since [*next-version*]
 */
class BuildDeleteSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\BuildDeleteSqlCapableTrait';

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
                                    '_buildSqlWhereClause',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_escapeSqlReference')->willReturnArgument(0);

        return $mock;
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
     * Tests the DELETE SQL build method to assert whether the built query matches the given arguments.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSql()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'or',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $valueHashMap = [
            '18' => ':12345',
            'verified' => ':56789',
        ];
        $where = 'WHERE `user_age` < :12345 OR `acc_verified` = :56789';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $valueHashMap)
                ->willReturn($where);

        $table = uniqid('table');
        $expected = "DELETE FROM $table $where;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the DELETE SQL build method with a null condition to assert whether the WHERE clause is omitted.
     *
     * @since [*next-version*]
     */
    public function testBuildDeleteSqlNullCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = null;
        $columnMap = [];
        $valueHashMap = [];

        $table = uniqid('table');
        $expected = "DELETE FROM $table;";

        $result = $reflect->_buildDeleteSql(
            $table,
            $condition,
            $columnMap,
            $valueHashMap
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }
}
