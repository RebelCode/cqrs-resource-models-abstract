<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Pdo\SqlTableAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlTableAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlTableAwareTrait';

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
                     ->setMethods(['__', '_createInvalidArgumentException'])
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
     * Creates a new stringable mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $string The string.
     *
     * @return MockObject
     */
    public function createStringable($string = '')
    {
        $mock = $this->getMockBuilder('Dhii\Util\String\StringableInterface')
                     ->setMethods(['__toString'])
                     ->getMockForAbstractClass();

        $mock->method('__toString')->willReturn($string);

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
     * Tests the getter and setter methods with a string table name to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTableString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = uniqid('table-');

        $reflect->_setSqlTable($input);

        $this->assertSame($input, $reflect->_getSqlTable(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with a stringable table name to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTableStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = $this->createStringable(uniqid('table-'));

        $reflect->_setSqlTable($input);

        $this->assertSame($input, $reflect->_getSqlTable(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlTableInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlTable($input);
    }
}
