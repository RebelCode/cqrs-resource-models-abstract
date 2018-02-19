<?php

namespace RebelCode\Storage\Resource\Pdo\UnitTest;

use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Pdo\SqlJoinConditionsAwareTrait as TestSubject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlJoinConditionsAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlJoinConditionsAwareTrait';

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
     * Creates a new mock logical expression instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated The expression negation; true if negated, false if not.
     *
     * @return MockObject
     */
    public function createLogicalExpression($type = '', $terms = [], $negated = false)
    {
        $mock = $this->getMockBuilder('Dhii\Expression\LogicalExpressionInterface')
                     ->setMethods(['getType', 'getTerms', 'isNegated'])
                     ->getMockForAbstractClass();

        $mock->method('getType')->willReturn($type);
        $mock->method('getTerms')->willReturn($terms);
        $mock->method('isNegated')->willReturn($negated);

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
    public function testGetSetSqlJoinConditions()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('table-') => $this->createLogicalExpression(),
            uniqid('table-') => $this->createLogicalExpression(),
            uniqid('table-') => $this->createLogicalExpression(),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $reflect->_setSqlJoinConditions($input);

        $this->assertSame($input, $reflect->_getSqlJoinConditions(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlJoinConditionsInvalidValue()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('table-') => $this->createLogicalExpression(),
            uniqid('table-') => new stdClass(),
            uniqid('table-') => $this->createLogicalExpression(),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlJoinConditions($input);
    }

    /**
     * Tests the getter and setter methods with an invalid (numeric) key to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlJoinConditionsInvalidKey()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = [
            uniqid('table-') => $this->createLogicalExpression(),
            uniqid('table-') => $this->createLogicalExpression(),
            $this->createLogicalExpression(),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willReturn($input);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlJoinConditions($input);
    }

    /**
     * Tests the getter and setter methods with an invalid argument to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetSqlJoinConditionsInvalidArg()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = uniqid('invalid-');

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeArray')
                ->with($input)
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setSqlJoinConditions($input);
    }
}
