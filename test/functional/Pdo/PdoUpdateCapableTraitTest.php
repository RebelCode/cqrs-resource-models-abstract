<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\PdoUpdateCapableTrait}.
 *
 * @since [*next-version*]
 */
class PdoUpdateCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\PdoUpdateCapableTrait';

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
                                    '_getPdoValueHashString',
                                    '_buildUpdateSql',
                                    '_getSqlUpdateTable',
                                    '_getSqlUpdateFieldColumnMap',
                                    '_executePdoQuery',
                                    '_normalizeString',
                                    '_createInvalidArgumentException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function ($input) {
                return strval($input);
            }
        );
        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function ($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_getPdoValueHashString')->willReturnCallback(
            function ($input) {
                return ':'.hash('crc32b', strval($input));
            }
        );

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
     * Tests the PDO UPDATE query method.
     *
     * @since [*next-version*]
     */
    public function testUpdate()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $changeSet = [
            'lastName' => 'bar',
            'age' => 21,
        ];
        $condition = $this->createLogicalExpression('equal', ['id', 5]);

        $subject->method('_getSqlUpdateTable')->willReturn($table = 'users');
        $subject->method('_getSqlUpdateFieldColumnMap')->willReturn(
            $map = [
                'id' => 'id',
                'firstName' => 'name',
                'lastName' => 'surname',
                'age' => 'age',
            ]
        );
        $subject->expects($this->once())
                ->method('_getPdoExpressionHashMap')
                ->with($condition, array_keys($map))
                ->willReturn([]);
        $subject->expects($this->once())
                ->method('_buildUpdateSql')
                ->with($table, $changeSet, $condition, $this->anything())
                ->willReturn('UPDATE `users` SET `surname` = "bar", `age` = 21 WHERE `id` = 5');

        $statement = $this->getMockBuilder('PDOStatement')
                          ->setMethods(['execute'])
                          ->getMockForAbstractClass();
        $subject->method('_executePdoQuery')->willReturn($statement);

        $result = $reflect->_update($changeSet, $condition);

        $this->assertSame($statement, $result, 'Retrieved result is not the PDO statement instance.');
    }

    /**
     * Tests the PDO UPDATE query method without a condition.
     *
     * @since [*next-version*]
     */
    public function testUpdateNoCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = null;
        $changeSet = [
            'lastName' => 'bar',
            'age' => 21,
        ];

        $subject->method('_getSqlUpdateTable')->willReturn($table = 'users');
        $subject->method('_getSqlUpdateFieldColumnMap')->willReturn(
            $map = [
                'id' => 'id',
                'firstName' => 'name',
                'lastName' => 'surname',
                'age' => 'age',
            ]
        );
        $subject->expects($this->once())
                ->method('_buildUpdateSql')
                ->with($table, $changeSet, $condition, $this->anything())
                ->willReturn('UPDATE `users` SET `surname` = "bar", `age` = 21');

        $statement = $this->getMockBuilder('PDOStatement')
                          ->setMethods(['execute'])
                          ->getMockForAbstractClass();
        $subject->method('_executePdoQuery')->willReturn($statement);

        $result = $reflect->_update($changeSet);

        $this->assertSame($statement, $result, 'Retrieved result is not the PDO statement instance.');
    }

    /**
     * Tests the PDO UPDATE query method with an empty change set.
     *
     * @since [*next-version*]
     */
    public function testUpdateNoChangeSet()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $changeSet = [];

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_update($changeSet);
    }
}
