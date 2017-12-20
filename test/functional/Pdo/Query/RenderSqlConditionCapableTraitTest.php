<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\String\StringableInterface as Stringable;
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
                    ->getEntityName($entity)
                    ->getFieldName($field)
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
     * Tests the SQL condition render method to assert whether the result is the output of the template renderer.
     *
     * @since [*next-version*]
     */
    public function testRenderSqlCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // Method args
        $condition = $this->createLogicalExpression('test', []);
        $valueHashMap = [
            'e' => ':123',
            'f' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_getSqlFieldColumnMap')
                ->willReturn(
                    $columnMap = [
                        'a' => $this->createEntityField('t', 'col_a'),
                        'c' => $this->createEntityField('t', 'col_c'),
                    ]
                );

        $output = uniqid('output-');
        $template = $this->createTemplate();
        $template->expects($this->once())
                 ->method('render')
                 ->with([$condition, $columnMap, $valueHashMap])
                 ->willReturn($output);

        $subject->expects($this->once())
                ->method('_getSqlConditionTemplate')
                ->with($condition)
                ->willReturn($template);

        $result = $reflect->_renderSqlCondition($condition, $valueHashMap);

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

        // Method args
        $condition = $this->createLogicalExpression('test', []);
        $valueHashMap = [
            'e' => ':123',
            'f' => ':456',
        ];

        $subject->expects($this->once())
                ->method('_getSqlConditionTemplate')
                ->with($condition)
                ->willReturn(null);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_renderSqlCondition($condition, $valueHashMap);
    }
}
