<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\ExpressionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\GetPdoExpressionHashMapCapableTrait}.
 *
 * @since [*next-version*]
 */
class GetPdoExpressionHashMapCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\GetPdoExpressionHashMapCapableTrait';

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
                        ->setMethods(['_normalizeString', '_getPdoValueHashString']);

        $mock = $builder->getMockForTrait();
        $mock->method('_getPdoValueHashString')->willReturnArgument(0);
        $mock->method('_normalizeString')->willReturnCallback(
            function ($arg) {
                return strval($arg);
            }
        );

        return $mock;
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface The created expression instance.
     */
    public function createExpression($type, $terms)
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
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
     * Tests thee expression value hash map getter method to assert whether the retrieved hash map contains the
     * correct value to hash mappings.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMap()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            'plus',
            [
                $this->createExpression('mult', ['a', 'b']),
                $this->createExpression('mult', ['c', 'd']),
            ]
        );

        $result = $reflect->_getPdoExpressionHashMap($expression, []);

        $this->assertArrayHasKey('a', $result, 'Retrieved hash map does not contain hash for "a".');
        $this->assertArrayHasKey('b', $result, 'Retrieved hash map does not contain hash for "b".');
        $this->assertArrayHasKey('c', $result, 'Retrieved hash map does not contain hash for "c".');
        $this->assertArrayHasKey('d', $result, 'Retrieved hash map does not contain hash for "d".');
    }

    /**
     * Tests thee expression value hash map getter method with an ignore list to assert whether the retrieved hash map
     * does not contain mappings for the ignored values.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMapIgnore()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            'plus',
            [
                $this->createExpression('mult', ['a', 'b']),
                $this->createExpression('mult', ['c', 'd']),
            ]
        );
        $ignore = ['b', 'd'];

        $result = $reflect->_getPdoExpressionHashMap($expression, $ignore);

        $this->assertArrayHasKey('a', $result, 'Retrieved hash map does not contain hash for "a".');
        $this->assertArrayHasKey('c', $result, 'Retrieved hash map does not contain hash for "c".');

        $this->assertArrayNotHasKey('b', $result, 'Retrieved hash contains hash for "b".');
        $this->assertArrayNotHasKey('d', $result, 'Retrieved hash contains hash for "d".');
    }
}
