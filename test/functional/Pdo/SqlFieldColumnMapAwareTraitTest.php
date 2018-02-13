<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Pdo\SqlFieldColumnMapAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlFieldColumnMapAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlFieldColumnMapAwareTrait';

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
     * Tests the getter and setter methods with an array of strings to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetFieldColumnMapAllStrings()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => uniqid('column-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlFieldColumnMap($input);

        $this->assertSame($input, $reflect->_getSqlFieldColumnMap(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an array of strings to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetFieldColumnMapAllStringables()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('field-') => $this->createStringable(uniqid('column-')),
            uniqid('field-') => $this->createStringable(uniqid('column-')),
            uniqid('field-') => $this->createStringable(uniqid('column-')),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlFieldColumnMap($input);

        $this->assertSame($input, $reflect->_getSqlFieldColumnMap(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an array of strings to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetFieldColumnMapMixed()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => $this->createStringable(uniqid('column-')),
            uniqid('field-') => uniqid('column-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlFieldColumnMap($input);

        $this->assertSame($input, $reflect->_getSqlFieldColumnMap(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetFieldColumnMapInvalidValue()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('field-') => uniqid('string-'),
            uniqid('field-') => $this->createStringable(uniqid('string-')),
            uniqid('field-') => new stdClass(),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlFieldColumnMap($input);
    }

    /**
     * Tests the getter and setter methods with an invalid (numeric) key to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetFieldColumnMapInvalidKey()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => $this->createStringable(uniqid('column-')),
            uniqid('column-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlFieldColumnMap($input);
    }

    /**
     * Tests the getter and setter methods with an invalid argument to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetFieldColumnMapInvalidArg()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = uniqid('invalid-');

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlFieldColumnMap($input);
    }
}
