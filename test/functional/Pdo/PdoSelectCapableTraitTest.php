<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\PdoSelectCapableTrait}.
 *
 * @since [*next-version*]
 */
class PdoSelectCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\PdoSelectCapableTrait';

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
                                    '_buildSelectSql',
                                    '_getSqlSelectTables',
                                    '_getSqlSelectColumns',
                                    '_getSqlSelectFieldNames',
                                    '_getSqlSelectJoinConditions',
                                    '_getPdoExpressionHashMap',
                                    '_processSelectedRecord',
                                    '_executePdoQuery',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturn(
            function($input) {
                return strval($input);
            }
        );
        $mock->method('_processSelectedRecord')->willReturnArgument(0);

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
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  the field name.
     *
     * @return EntityFieldInterface
     */
    public function createEntityField($entity, $field)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\EntityFieldInterface')
                    ->getEntityName($entity)
                    ->getFieldName($field)
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
     * Tests the SELECT SQL method in its simplest form: without a condition and without joins.
     *
     * @since [*next-version*]
     */
    public function testSelectNoConditionNoJoins()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = null;
        $joins = [];

        $subject->method('_getSqlSelectColumns')->willReturn($cols = ['id', 'name']);
        $subject->method('_getSqlSelectTables')->willReturn($tables = ['users']);
        $subject->method('_getSqlSelectJoinConditions')->willReturn($joins);
        $subject->method('_getSqlSelectFieldNames')->willReturn($fields = []);
        $subject->method('_getPdoExpressionHashMap')->willReturn($vhm = []);

        $subject->expects($this->once())
                ->method('_buildSelectSql')
                ->with($cols, $tables, $joins, $condition, $vhm)
                ->willReturn('SELECT `id`, `name` FROM `users`');

        $expected = [
            ['id' => '1', 'name' => 'foo'],
            ['id' => '2', 'name' => 'bar'],
            ['id' => '4', 'name' => 'test'],
        ];

        $statement = $this->getMockBuilder('\PDOStatement')
                          ->setMethods(['execute', 'fetchAll'])
                          ->getMock();

        $statement->method('fetchAll')->willReturn($expected);
        $subject->method('_executePdoQuery')->willReturn($statement);

        $result = $reflect->_select();

        $this->assertEquals($expected, $result, 'Expected and retrieved results do not match');
    }

    /**
     * Tests the SELECT SQL method with a WHERE condition.
     *
     * @since [*next-version*]
     */
    public function testSelectNoJoins()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression('greater', ['age', 18]);
        $joins = [];

        $subject->method('_getSqlSelectColumns')->willReturn($cols = ['id', 'name']);
        $subject->method('_getSqlSelectTables')->willReturn($tables = ['users']);
        $subject->method('_getSqlSelectJoinConditions')->willReturn($joins);
        $subject->method('_getSqlSelectFieldNames')->willReturn($fields = []);
        $subject->method('_getPdoExpressionHashMap')->willReturn($vhm = []);

        $subject->expects($this->once())
                ->method('_buildSelectSql')
                ->with($cols, $tables, $joins, $condition, $vhm)
                ->willReturn('SELECT `id`, `name` FROM `users` WHERE `age` > 18');

        $expected = [
            ['id' => '2', 'name' => 'bar'],
            ['id' => '4', 'name' => 'test'],
        ];
        $statement = $this->getMockBuilder('\PDOStatement')
                          ->setMethods(['execute', 'fetchAll'])
                          ->getMock();

        $statement->method('fetchAll')->willReturn($expected);
        $subject->method('_executePdoQuery')->willReturn($statement);

        $result = $reflect->_select($condition);

        $this->assertEquals(
            $expected,
            $result,
            'Expected and retrieved results do not match',
            0,
            10,
            true
        );
    }

    /**
     * Tests the SELECT SQL method with JOIN conditions.
     *
     * @since [*next-version*]
     */
    public function testSelectNoCondition()
    {
        $subject = $this->createInstance([], [], ['users'], [], []);
        $reflect = $this->reflect($subject);

        $condition = null;
        $joins = [
            'users' => $this->createLogicalExpression(
                'equals',
                [
                    $this->createEntityField('user', 'id'),
                    $this->createEntityField('msgs', 'user_id'),
                ]
            ),
        ];

        $subject->method('_getSqlSelectColumns')->willReturn($cols = ['id', 'name']);
        $subject->method('_getSqlSelectTables')->willReturn($tables = ['users']);
        $subject->method('_getSqlSelectJoinConditions')->willReturn($joins);
        $subject->method('_getSqlSelectFieldNames')->willReturn($fields = []);
        $subject->method('_getPdoExpressionHashMap')->willReturn($vhm = []);

        $subject->expects($this->once())
                ->method('_buildSelectSql')
                ->with($cols, $tables, $joins, $condition, $vhm)
                ->willReturn(
                    'SELECT `msgs`.`id`, `msgs`.`content`, `users`.`name`
                          FROM `msgs`
                          JOIN `users` ON `users`.`id` = `msgs`.`user_id`'
                );

        $expected = [
            ['id' => '1', 'content' => 'hello world', 'name' => 'bar'],
            ['id' => '5', 'content' => 'a message!', 'name' => 'bar'],
            ['id' => '6', 'content' => 'tree(3)', 'name' => 'test'],
        ];
        $statement = $this->getMockBuilder('\PDOStatement')
                          ->setMethods(['execute', 'fetchAll'])
                          ->getMock();

        $statement->method('fetchAll')->willReturn($expected);
        $subject->method('_executePdoQuery')->willReturn($statement);

        $result = $reflect->_select();

        $this->assertEquals(
            $expected,
            $result,
            'Expected and retrieved results do not match',
            0,
            10,
            true
        );
    }

    /**
     * Tests the SELECT SQL method with a WHERE condition and JOIN conditions.
     *
     * @since [*next-version*]
     */
    public function testSelect()
    {
        $subject = $this->createInstance([], [], ['users'], [], []);
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(
            'greater',
            [
                $this->createEntityField('user', 'age'),
                20,
            ]
        );
        $joins = [
            'users' => $this->createLogicalExpression(
                'equals',
                [
                    $this->createEntityField('user', 'id'),
                    $this->createEntityField('msgs', 'user_id'),
                ]
            ),
        ];

        $subject->method('_getSqlSelectColumns')->willReturn($cols = ['id', 'name']);
        $subject->method('_getSqlSelectTables')->willReturn($tables = ['users']);
        $subject->method('_getSqlSelectJoinConditions')->willReturn($joins);
        $subject->method('_getSqlSelectFieldNames')->willReturn($fields = []);
        $subject->method('_getPdoExpressionHashMap')->willReturn($vhm = []);

        $subject->expects($this->once())
                ->method('_buildSelectSql')
                ->with($cols, $tables, $joins, $condition, $vhm)
                ->willReturn(
                    'SELECT `msgs`.`id`, `msgs`.`content`, `users`.`name`
                          FROM `msgs`
                          JOIN `users` ON `users`.`id` = `msgs`.`user_id`
                          WHERE `users`.`age` > 20'
                );

        $expected = [
            ['id' => '1', 'content' => 'hello world', 'name' => 'bar'],
            ['id' => '5', 'content' => 'a message!', 'name' => 'bar'],
        ];
        $statement = $this->getMockBuilder('\PDOStatement')
                          ->setMethods(['execute', 'fetchAll'])
                          ->getMock();

        $statement->method('fetchAll')->willReturn($expected);
        $subject->method('_executePdoQuery')->willReturn($statement);

        $result = $reflect->_select($condition);

        $this->assertEquals(
            $expected,
            $result,
            'Expected and retrieved results do not match',
            0,
            10,
            true
        );
    }
}
