<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
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
            function ($input) {
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
     * Creates a testing database environment.
     *
     * @since [*next-version*]
     *
     * @return PDO
     */
    public function createDatabase()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE TABLE IF NOT EXISTS `users` (`id` INTEGER PRIMARY_KEY, `name` TEXT, `age` INTEGER)');
        $pdo->exec('INSERT INTO `users` (`id`, `name`, `age`) VALUES (1, "foo", 15)');
        $pdo->exec('INSERT INTO `users` (`id`, `name`, `age`) VALUES (2, "bar", 22)');
        $pdo->exec('INSERT INTO `users` (`id`, `name`, `age`) VALUES (4, "test", 19)');

        $pdo->exec('CREATE TABLE `msgs` (`id` INTEGER PRIMARY KEY, `user_id` INTEGER, `content` TEXT)');
        $pdo->exec('INSERT INTO `msgs` (`id`, `user_id`, `content`) VALUES (1, 2, "hello world")');
        $pdo->exec('INSERT INTO `msgs` (`id`, `user_id`, `content`) VALUES (5, 2, "a message!")');
        $pdo->exec('INSERT INTO `msgs` (`id`, `user_id`, `content`) VALUES (6, 4, "tree(3)")');

        return $pdo;
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
        $pdo = $this->createDatabase();

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

        $subject->method('_executePdoQuery')
                ->willReturnCallback(
                    function ($query) use ($pdo) {
                        $statement = $pdo->prepare($query);
                        $statement->execute();

                        return $statement;
                    }
                );

        $result = $reflect->_select();
        $expected = [
            ['id' => '1', 'name' => 'foo'],
            ['id' => '2', 'name' => 'bar'],
            ['id' => '4', 'name' => 'test'],
        ];

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
        $pdo = $this->createDatabase();

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

        $subject->method('_executePdoQuery')
                ->willReturnCallback(
                    function ($query) use ($pdo) {
                        $statement = $pdo->prepare($query);
                        $statement->execute();

                        return $statement;
                    }
                );

        $result = $reflect->_select($condition);
        $expected = [
            ['id' => '2', 'name' => 'bar'],
            ['id' => '4', 'name' => 'test'],
        ];

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
        $pdo = $this->createDatabase();

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

        $subject->method('_executePdoQuery')
                ->willReturnCallback(
                    function ($query) use ($pdo) {
                        $statement = $pdo->prepare($query);
                        $statement->execute();

                        return $statement;
                    }
                );

        $result = $reflect->_select();
        $expected = [
            ['id' => '1', 'content' => 'hello world', 'name' => 'bar'],
            ['id' => '5', 'content' => 'a message!', 'name' => 'bar'],
            ['id' => '6', 'content' => 'tree(3)', 'name' => 'test'],
        ];

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
        $pdo = $this->createDatabase();

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

        $subject->method('_executePdoQuery')
                ->willReturnCallback(
                    function ($query) use ($pdo) {
                        $statement = $pdo->prepare($query);
                        $statement->execute();

                        return $statement;
                    }
                );

        $result = $reflect->_select($condition);
        $expected = [
            ['id' => '1', 'content' => 'hello world', 'name' => 'bar'],
            ['id' => '5', 'content' => 'a message!', 'name' => 'bar'],
        ];

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
