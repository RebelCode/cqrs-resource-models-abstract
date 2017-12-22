<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

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
            [
                'userId' => 5, 'userName' => 'foo', 'userAge' => 22,
                'userId' => 11, 'userName' => 'bar', 'userAge' => 19,
            ],
        ];

        $subject->method('_getSqlInsertTable')->willReturn($table = 'users');
        $subject->method('_getSqlInsertColumnNames')->willReturn($cols = ['id', 'name', 'age']);
        $subject->method('_getSqlInsertFieldColumnMap')->willReturn(
            $map = [
                'userId' => 'id',
                'userName' => 'name',
                'userAge' => 'age',
            ]
        );
        $statement = $this->getMockBuilder('PDOStatement')
            ->setMethods(['execute'])
            ->getMockForAbstractClass();
        $subject->method('_executePdoQuery')->willReturn($statement);

        $subject->expects($this->once())
                ->method('_buildInsertSql')
                ->with($table, $cols, $rowSet, $this->anything())
                ->willReturn('INSERT INTO `users` (`id`, `name`, `age`) VALUES (5, "foo", 22), (11, "bar", 19)');

        $result = $reflect->_insert($rowSet);

        $this->assertSame($statement, $result, 'Retrieved result is not the PDO statement instance.');
    }
}
