<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Output\TemplateInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\RenderSqlConditionCapableTrait}.
 *
 * @since [*next-version*]
 */
class RenderSqlConditionCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\RenderSqlConditionCapableTrait';

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
                                    '_getSqlConditionTemplate',
                                    '_createInvalidArgumentException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('__')->willReturnArgument(0);

        return $mock;
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated Optional negation flag.
     *
     * @return LogicalExpressionInterface The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        return $this->mock('Dhii\Expression\LogicalExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->isNegated($negated)
                    ->new();
    }

    /**
     * Creates a template mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $render The output to be rendered by the template.
     *
     * @return TemplateInterface The created expression instance.
     */
    public function createTemplate($render)
    {
        return $this->mock('Dhii\Output\TemplateInterface')
                    ->render($render)
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
     * Tests the SQL condition render method to assert whether the result is the output of the template renderer.
     *
     * @since [*next-version*]
     */
    public function testRenderSqlCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression('test', []);
        $columnMap = [
            'a' => 'b',
            'c' => 'd',
        ];
        $valueHashMap = [
            'e' => ':123',
            'f' => ':456',
        ];

        $output = uniqid('output-');
        $renderer = $this->createTemplate($output);

        $subject->expects($this->once())
                ->method('_getSqlConditionTemplate')
                ->with($condition)
                ->willReturn($renderer);

        $result = $reflect->_renderSqlCondition($condition, $columnMap, $valueHashMap);

        $this->assertEquals($result, $output, 'Expected and retrieved outputs are not the same.');
    }

    /**
     * Tests the SQL condition render method when no template render is retrieved for the given condition.
     *
     * @since [*next-version*]
     */
    public function testRenderSqlConditionNoTemplate()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression('test', []);
        $columnMap = [
            'a' => 'b',
            'c' => 'd',
        ];
        $valueHashMap = [
            'e' => ':123',
            'f' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_getSqlConditionTemplate')
                ->with($condition)
                ->willReturn(null);

        $this->setExpectedException('InvalidArgumentException');
        $reflect->_renderSqlCondition($condition, $columnMap, $valueHashMap);
    }
}
