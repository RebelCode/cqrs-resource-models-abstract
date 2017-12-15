<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\BuildSelectSqlCapableTrait}.
 *
 * @since [*next-version*]
 */
class BuildSelectSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\BuildSelectSqlCapableTrait';

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
                                    '_buildSqlJoinConditions',
                                    '_buildSqlWhereClause',
                                    '_escapeSqlReferenceArray',
                                    '_createInvalidArgumentException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_escapeSqlReferenceArray')->willReturnCallback(
            function($array) {
                return implode(', ', $array);
            }
        );
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('__')->willReturnArgument(0);

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
     * Tests the SELECT SQL build method to assert whether the built query reflects the given arguments.
     *
     * @since [*next-version*]
     */
    public function testBuildSelectSql()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $tables = ['users', 'profiles'];
        $tablesList = 'users, profiles';
        $columns = ['id', 'name', 'age'];
        $columnsList = 'id, name, age';

        $columnMap = [
            'authorId'  => 'author_id',
            'countryId' => 'country_id',
            'age'       => 'user_age',
            'verified'  => 'acc_verified',
        ];
        $valueHashMap = [
            '18'    => ':12345',
            'false' => ':56789',
        ];

        $condition = $this->createLogicalExpression(
            'and',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $where = 'WHERE user_age < :12345 AND acc_verified = :56789';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $columnMap, $valueHashMap)
                ->willReturn($where);

        $joinConditions = [
            'posts'     => $this->createLogicalExpression(
                'equals',
                [
                    $this->createLogicalExpression('table_column', ['test', 'id']),
                    $this->createLogicalExpression('table_column', ['posts', 'authorId']),
                ]
            ),
            'countries' => $this->createLogicalExpression(
                'equals',
                [
                    $this->createLogicalExpression('table_column', ['test', 'countryId']),
                    $this->createLogicalExpression('table_column', ['countries', 'id']),
                ]
            ),
        ];
        $joins = 'JOIN posts ON test.id = posts.author_id JOIN posts ON test.country_id = countries.id';
        $subject->expects($this->once())
                ->method('_buildSqlJoins')
                ->with($joinConditions, $columnMap, $valueHashMap)
                ->willReturn($joins);

        $expected = "SELECT $columnsList FROM $tablesList $joins $where;";
        $result = $reflect->_buildSelectSql($columns, $tables, $joinConditions, $condition, $columnMap, $valueHashMap);

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the SELECT SQL build method without join conditions to assert whether the JOIN portions are omitted from
     * the query.
     *
     * @since [*next-version*]
     */
    public function testBuildSelectSqlNoJoins()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $tables = ['users', 'profiles'];
        $tablesList = 'users, profiles';
        $columns = ['id', 'name', 'age'];
        $columnsList = 'id, name, age';

        $columnMap = [
            'age'      => 'user_age',
            'verified' => 'acc_verified',
        ];
        $valueHashMap = [
            '18'    => ':12345',
            'false' => ':56789',
        ];

        $condition = $this->createLogicalExpression(
            'and',
            [
                $this->createLogicalExpression('smaller', ['age', 18]),
                $this->createLogicalExpression('equals', ['verified', false]),
            ]
        );
        $where = 'WHERE user_age < :12345 AND acc_verified = :56789';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $columnMap, $valueHashMap)
                ->willReturn($where);

        $joinConditions = [];
        $joins = '';
        $subject->expects($this->once())
                ->method('_buildSqlJoins')
                ->with($joinConditions, $columnMap, $valueHashMap)
                ->willReturn($joins);

        $expected = "SELECT $columnsList FROM $tablesList $joins $where;";
        $result = $reflect->_buildSelectSql($columns, $tables, $joinConditions, $condition, $columnMap, $valueHashMap);

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the SELECT SQL build method without conditions to assert whether the WHERE clause is omitted from the
     * query.
     *
     * @since [*next-version*]
     */
    public function testBuildSelectSqlNoConditions()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $tables = ['users', 'profiles'];
        $tablesList = 'users, profiles';
        $columns = ['id', 'name', 'age'];
        $columnsList = 'id, name, age';

        $columnMap = [
            'authorId'  => 'author_id',
            'countryId' => 'country_id',
        ];
        $valueHashMap = [
            '18'    => ':12345',
            'false' => ':56789',
        ];

        $condition = null;
        $where = '';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $columnMap, $valueHashMap)
                ->willReturn($where);

        $joinConditions = [
            'posts'     => $this->createLogicalExpression(
                'equals',
                [
                    $this->createLogicalExpression('table_column', ['test', 'id']),
                    $this->createLogicalExpression('table_column', ['posts', 'authorId']),
                ]
            ),
            'countries' => $this->createLogicalExpression(
                'equals',
                [
                    $this->createLogicalExpression('table_column', ['test', 'countryId']),
                    $this->createLogicalExpression('table_column', ['countries', 'id']),
                ]
            ),
        ];
        $joins = 'JOIN posts ON test.id = posts.author_id JOIN posts ON test.country_id = countries.id';
        $subject->expects($this->once())
                ->method('_buildSqlJoins')
                ->with($joinConditions, $columnMap, $valueHashMap)
                ->willReturn($joins);

        $expected = "SELECT $columnsList FROM $tablesList $joins $where;";
        $result = $reflect->_buildSelectSql($columns, $tables, $joinConditions, $condition, $columnMap, $valueHashMap);

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the SELECT SQL build method without joins and conditions to assert whether they are omitted from the query.
     *
     * @since [*next-version*]
     */
    public function testBuildSelectSqlNoJoinsNoConditions()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $tables = ['users', 'profiles'];
        $tablesList = 'users, profiles';
        $columns = ['id', 'name', 'age'];
        $columnsList = 'id, name, age';

        $columnMap = [];
        $valueHashMap = [];

        $condition = null;
        $where = '';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $columnMap, $valueHashMap)
                ->willReturn($where);

        $joinConditions = [];
        $joins = '';
        $subject->expects($this->once())
                ->method('_buildSqlJoins')
                ->with($joinConditions, $columnMap, $valueHashMap)
                ->willReturn($joins);

        $expected = "SELECT $columnsList FROM $tablesList $joins $where;";
        $result = $reflect->_buildSelectSql($columns, $tables, $joinConditions, $condition, $columnMap, $valueHashMap);

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the SELECT SQL build method without columns to assert that an asterisk is used instead.
     *
     * @since [*next-version*]
     */
    public function testBuildSelectSqlNoColumns()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $tables = ['users', 'profiles'];
        $tablesList = 'users, profiles';
        $columns = [];
        $columnsList = '*';

        $columnMap = [];
        $valueHashMap = [];

        $condition = null;
        $where = '';
        $subject->expects($this->once())
                ->method('_buildSqlWhereClause')
                ->with($condition, $columnMap, $valueHashMap)
                ->willReturn($where);

        $joinConditions = [];
        $joins = '';
        $subject->expects($this->once())
                ->method('_buildSqlJoins')
                ->with($joinConditions, $columnMap, $valueHashMap)
                ->willReturn($joins);

        $expected = "SELECT $columnsList FROM $tablesList $joins $where;";
        $result = $reflect->_buildSelectSql($columns, $tables, $joinConditions, $condition, $columnMap, $valueHashMap);

        $this->assertEquals($expected, $result, 'Expected and retrieved queries do not match.');
    }

    /**
     * Tests the SELECT SQL build method with not tables to assert that an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testBuildSelectSqlNoTables()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $columns = ['id', 'name', 'age'];
        $tables = [];
        $condition = $this->createLogicalExpression('equals', ['age', 18]);
        $joinConditions = [
            'profiles' => $this->createLogicalExpression('equals', ['user.profId', 'profile.id']),
        ];
        $columnMap = [
            'profId' => 'prof_id',
        ];
        $valueHashMap = [
            '18' => ':12345',
        ];

        $this->setExpectedException('InvalidArgumentException');
        $result = $reflect->_buildSelectSql($columns, $tables, $joinConditions, $condition, $columnMap, $valueHashMap);
    }
}
