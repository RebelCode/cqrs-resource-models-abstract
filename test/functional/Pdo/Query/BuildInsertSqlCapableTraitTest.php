<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\BuildInsertSqlCapableTrait}.
 *
 * @since [*next-version*]
 */
class BuildInsertSqlCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\BuildInsertSqlCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance()
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            [
                                '_escapeSqlReference',
                                '_escapeSqlReferenceArray',
                                '_normalizeString',
                            ]
                        );

        $mock = $builder->getMockForTrait();

        // Simple, zero-escaping, mock implementations
        $mock->method('_escapeSqlReference')->willReturnArgument(0);
        $mock->method('_escapeSqlReferenceArray')->willReturnCallback(
            function($input) {
                return implode(', ', $input);
            }
        );
        $mock->method('_normalizeString')->willReturnCallback(
            function($input) {
                return strval($input);
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
     * Tests the INSERT SQL build method to assert whether the built query reflects the arguments given.
     *
     * @since [*next-version*]
     */
    public function testBuildInsertSql()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $result = $reflect->_buildInsertSql(
            $table = 'test',
            $columns = ['id', 'name', 'last_name'],
            $rows = [
                [
                    'id'      => 1,
                    'name'    => 'Miguel',
                    'surname' => 'Muscat',
                ],
                [
                    'id'      => 2,
                    'name'    => 'Anton',
                    'surname' => 'Ukhanev',
                ],
            ],
            $columnMap = [
                'surname' => 'last_name',
            ],
            $valueHashMap = [
                '1'      => ':123',
                '2'      => ':456',
                'Miguel' => ':321',
                'Muscat' => ':654',
            ]
        );

        $this->assertEquals(
            'INSERT INTO test (id, name, last_name) VALUES (:123, :321, :654), (:456, "Anton", "Ukhanev");',
            $result,
            'Retrieved and expected queries do not match.'
        );
    }

    /**
     * Tests the INSERT SQL build method with no rows to assert whether the VALUES portion of the query is omitted.
     *
     * @since [*next-version*]
     */
    public function testBuildInsertSqlNoRows()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $result = $reflect->_buildInsertSql(
            $table = 'test',
            $columns = ['id', 'name', 'surname'],
            $rows = []
        );

        $this->assertEquals(
            'INSERT INTO test (id, name, surname);',
            $result,
            'Retrieved and expected queries do not match.'
        );
    }
}
