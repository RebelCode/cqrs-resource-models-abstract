<?php

namespace RebelCode\Storage\Resource\Pdo\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\PdoDeleteCapableTrait}.
 *
 * @since [*next-version*]
 */
class PdoDeleteCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\PdoDeleteCapableTrait';

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
                                    '_buildDeleteSql',
                                    '_getSqlDeleteTable',
                                    '_getSqlDeleteFieldNames',
                                    '_getPdoExpressionHashMap',
                                    '_executePdoQuery',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();

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
     * Tests the DELETE SQL query method.
     *
     * @since [*next-version*]
     */
    public function testDelete()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'and',
            [
                $this->createLogicalExpression('equals', ['isVerified', 'true']),
                $this->createLogicalExpression('greater_equals', ['userAge', 18]),
            ]
        );

        $subject->method('_getSqlDeleteFieldNames')
                ->willReturn(['isVerified' => 'verified', 'userAge' => 'age']);
        $subject->method('_getSqlDeleteTable')
                ->willReturn($table = 'users');
        $subject->method('_getPdoExpressionHashMap')
                ->willReturn($valueHashMap = ['true' => ':123', '18' => ':456']);

        $subject->expects($this->once())
                ->method('_buildDeleteSql')
                ->with($table, $condition, $valueHashMap)
                ->willReturn('DELETE FROM `users` WHERE `verified` = :123 AND `age` >= :456');

        $statement = $this->getMockBuilder('PDOStatement')
                          ->setMethods(['execute'])
                          ->getMock();

        $subject->method('_executePdoQuery')
                ->willReturn($statement);

        $result = $reflect->_delete($condition);

        $this->assertSame($statement, $result, 'Retrieved result is not the executed statement instance.');
    }

    /**
     * Tests the DELETE SQL query method without a condition.
     *
     * @since [*next-version*]
     */
    public function testDeleteNoCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_getSqlDeleteFieldNames')
                ->willReturn(['isVerified' => 'verified', 'userAge' => 'age']);
        $subject->method('_getSqlDeleteTable')
                ->willReturn($table = 'users');
        $subject->method('_getPdoExpressionHashMap')
                ->willReturn($valueHashMap = []);

        $subject->expects($this->once())
                ->method('_buildDeleteSql')
                ->with($table, null, $valueHashMap)
                ->willReturn('DELETE FROM `users`');

        $statement = $this->getMockBuilder('PDOStatement')
                          ->setMethods(['execute'])
                          ->getMock();

        $subject->method('_executePdoQuery')
                ->willReturn($statement);

        $result = $reflect->_delete();

        $this->assertSame($statement, $result, 'Retrieved result is not the executed statement instance.');
    }
}
