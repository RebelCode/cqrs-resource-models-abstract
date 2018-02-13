<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Pdo\SqlTablesAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlTablesAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlTablesAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject
     */
    public function createInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(
                         [
                             '_normalizeArray',
                             '_createInvalidArgumentException',
                             '__',
                         ]
                     )
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($msg = '', $code = 0, $prev = null) {
                return new InvalidArgumentException($msg, $code, $prev);
            }
        );

        return $mock;
    }

    /**
     * Creates a new mock stringable instance.
     *
     * @since [*next-version*]
     *
     * @param string $output The output.
     *
     * @return MockObject
     */
    public function createStringable($output = '')
    {
        $mock = $this->getMockBuilder('Dhii\Util\String\StringableInterface')
                     ->setMethods(['__toString'])
                     ->getMockForAbstractClass();

        $mock->method('__toString')->willReturn($output);

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
     * Tests the getter and setter methods with strings to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTablesAllStrings()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('table-'),
            uniqid('table-'),
            uniqid('table-'),
            uniqid('table-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlTables($input);

        $this->assertSame($input, $reflect->_getSqlTables(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with stringable objects to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTablesAllStringables()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            $this->createStringable(uniqid('table-')),
            $this->createStringable(uniqid('table-')),
            $this->createStringable(uniqid('table-')),
            $this->createStringable(uniqid('table-')),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlTables($input);

        $this->assertSame($input, $reflect->_getSqlTables(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with strings to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTablesMixed()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('table-'),
            $this->createStringable(uniqid('table-')),
            uniqid('table-'),
            $this->createStringable(uniqid('table-')),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlTables($input);

        $this->assertSame($input, $reflect->_getSqlTables(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTablesInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willThrowException(new InvalidArgumentException());

        $reflect->_setSqlTables($input);
    }
}
