<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Storage\Resource\Sql\Expression\SqlExpressionContextInterface as SqlCtx;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\RenderSqlExpressionCapableTrait}.
 *
 * @since [*next-version*]
 */
class RenderSqlExpressionCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\RenderSqlExpressionCapableTrait';

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
                                    '_getTemplateForSqlExpression',
                                    '_getSqlFieldColumnMap',
                                    '_createInvalidArgumentException',
                                    '__',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function ($m, $c, $p) {
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
     * @param string $type The expression type.
     *
     * @return LogicalExpressionInterface The created expression instance.
     */
    public function createExpression($type)
    {
        return $this->mock('Dhii\Expression\TermInterface')
                    ->getType($type)
                    ->new();
    }

    /**
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  the field name.
     *
     * @return EntityFieldInterface
     */
    public function createEntityField($entity, $field)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\EntityFieldInterface')
                    ->getEntity($entity)
                    ->getField($field)
                    ->new();
    }

    /**
     * Creates a template mock instance.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createTemplate()
    {
        $builder = $this->getMockBuilder('Dhii\Output\TemplateInterface')
                        ->setMethods(['render']);

        $mock = $builder->getMockForAbstractClass();

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
     * Tests the SQL expression render method to assert whether the result is the output of the template renderer.
     *
     * @since [*next-version*]
     */
    public function testRenderSqlExpression()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // Method args
        $expression = $this->createExpression('test');
        $valueHashMap = [
            'e' => ':123',
            'f' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_getSqlFieldColumnMap')
                ->willReturn(
                    $columnMap = [
                        'a' => 'col_a',
                        'c' => 'col_c',
                    ]
                );

        $output = uniqid('output-');
        $template = $this->createTemplate();
        $template->expects($this->once())
                 ->method('render')
                 ->with(
                     [
                         SqlCtx::K_EXPRESSION  => $expression,
                         SqlCtx::K_ALIASES_MAP => array_merge($columnMap, $valueHashMap),
                     ]
                 )
                 ->willReturn($output);

        $subject->expects($this->once())
                ->method('_getTemplateForSqlExpression')
                ->with($expression)
                ->willReturn($template);

        $result = $reflect->_renderSqlExpression($expression, $valueHashMap);

        $this->assertEquals($result, $output, 'Expected and retrieved outputs are not the same.');
    }

    /**
     * Tests the SQL expression render method when no template render is retrieved for the given expression.
     *
     * @since [*next-version*]
     */
    public function testRenderSqlExpressionNoTemplate()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // Method args
        $expression = $this->createExpression('test', []);
        $valueHashMap = [
            'e' => ':123',
            'f' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_getTemplateForSqlExpression')
                ->with($expression)
                ->willReturn(null);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_renderSqlExpression($expression, $valueHashMap);
    }
}
