<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Pdo\SqlExpressionTemplateAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlExpressionTemplateAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlExpressionTemplateAwareTrait';

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
     * Creates a new mock template instance.
     *
     * @since [*next-version*]
     *
     * @param string $output Optional template output string.
     *
     * @return MockObject The created instance.
     */
    public function createTemplate($output = '')
    {
        $mock = $this->getMockBuilder('Dhii\Output\TemplateInterface')
                     ->setMethods(['render'])
                     ->getMockForAbstractClass();

        $mock->method('render')->willReturn($output);

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
    public function testGetSetSqlExpressionTemplate()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = $this->createTemplate();

        $reflect->_setSqlExpressionTemplate($input);

        $this->assertSame($input, $reflect->_getSqlExpressionTemplate(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlExpressionTemplateNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = null;

        $reflect->_setSqlExpressionTemplate($input);

        $this->assertSame($input, $reflect->_getSqlExpressionTemplate(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlExpressionTemplateInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlExpressionTemplate($input);
    }
}
