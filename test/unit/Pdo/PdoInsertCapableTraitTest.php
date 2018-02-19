<?php

namespace RebelCode\Storage\Resource\Pdo\UnitTest;

use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\PdoInsertCapableTrait}.
 *
 * @since [*next-version*]
 */
class PdoInsertCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\PdoInsertCapableTrait';

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
                                    '_containerGet',
                                    '_containerHas',
                                    '_getPdoValueHashString',
                                    '_buildInsertSql',
                                    '_getSqlInsertTable',
                                    '_getSqlInsertColumnNames',
                                    '_getSqlInsertFieldColumnMap',
                                    '_executePdoQuery',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function ($input) {
                return strval($input);
            }
        );
        $mock->method('_getPdoValueHashString')->willReturnCallback(
            function ($input) {
                return ':'.hash('crc32b', strval($input));
            }
        );
        $mock->method('_containerGet')->willReturnCallback(function($c, $k) {
            return $c->get($k);
        });
        $mock->method('_containerHas')->willReturnCallback(function($c, $k) {
            return $c->has($k);
        });

        return $mock;
    }

    /**
     * Creates a container mock instance.
     *
     * @since [*next-version*]
     *
     * @param array $map The data map of the container's contents.
     *
     * @return PHPUnit_Framework_MockObject_MockObject The created container mock instance.
     */
    public function createContainer(array $map = [])
    {
        $builder = $this->getMockBuilder('Psr\Container\ContainerInterface')
            ->setMethods(['get', 'has']);

        $mock = $builder->getMockForAbstractClass();
        $mock->method('get')->willReturnCallback(function($key) use ($map) {
            if (isset($map[$key])) {
                return $map[$key];
            }

            throw $this->mock('Psr\Container\NotFoundExceptionInterface')->new();
        });
        $mock->method('has')->willReturnCallback(function($key) use ($map) {
            return isset($map[$key]);
        });
        return $mock;
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
     * Tests the insert query method.
     *
     * @since [*next-version*]
     */
    public function testInsert()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $rowSet = [
            $this->createContainer(['userId' =>  5, 'userName' => 'foo', 'userAge' => 22]),
            $this->createContainer(['userId' => 11, 'userName' => 'bar', 'userAge' => 19]),
        ];

        $subject->method('_getSqlInsertTable')->willReturn($table = 'users');
        $subject->method('_getSqlInsertColumnNames')->willReturn($cols = ['id', 'name', 'age']);
        $subject->method('_getSqlInsertFieldColumnMap')->willReturn(
            $map = [
                'userId'   => 'id',
                'userName' => 'name',
                'userAge'  => 'age',
            ]
        );

        $expectedRecordData = [
            ['id' =>  5, 'name' => 'foo', 'age' => 22],
            ['id' => 11, 'name' => 'bar', 'age' => 19]
        ];
        $query = 'INSERT INTO `users` (`id`, `name`, `age`) VALUES (5, "foo", 22), (11, "bar", 19)';
        $statement = $this->getMockBuilder('PDOStatement')
                          ->setMethods(['execute'])
                          ->getMockForAbstractClass();

        // Expect the query builder to be given the expected extracted record data
        $subject->expects($this->once())
                ->method('_buildInsertSql')
                ->with($table, $cols, $expectedRecordData, $this->isType('array'))
                ->willReturn($query);
        // Expect query execution method to be called with the same query returned by the query builder
        $subject->expects($this->once())
                ->method('_executePdoQuery')
                ->with($query, $this->isType('array'))
                ->willReturn($statement);

        $result = $reflect->_insert($rowSet);

        $this->assertSame($statement, $result, 'Retrieved result is not the PDO statement instance.');
    }
}
