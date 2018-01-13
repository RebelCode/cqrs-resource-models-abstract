<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use InvalidArgumentException;
use PDO;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\PdoAwareTrait}.
 *
 * @since [*next-version*]
 */
class PdoAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\PdoAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(['__', '_createInvalidArgumentException'])
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function ($msg = '', $code = 0, $prev = null) {
                return new InvalidArgumentException($msg, $code, $prev);
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
     * Tests the getter and setter methods to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetPdo()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new PDO('sqlite::memory:');

        $reflect->_setPdo($input);

        $this->assertSame($input, $reflect->_getPdo(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with a null PDO instance.
     *
     * @since [*next-version*]
     */
    public function testGetSetPdoNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = null;

        $reflect->_setPdo($input);

        $this->assertSame($input, $reflect->_getPdo(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetPdoInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setPdo($input);
    }
}
