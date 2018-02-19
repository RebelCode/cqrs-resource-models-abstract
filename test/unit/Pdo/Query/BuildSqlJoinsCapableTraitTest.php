<?php

namespace RebelCode\Storage\Resource\Pdo\Query\UnitTest;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\BuildSqlJoinsCapableTrait}.
 *
 * @since [*next-version*]
 */
class BuildSqlJoinsCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\BuildSqlJoinsCapableTrait';

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
                        ->setMethods(
                            [
                                '_getSqlJoinType',
                                '_renderSqlCondition',
                                '_escapeSqlReference',
                                '_normalizeString',
                            ]
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function ($input) {
                return strval($input);
            }
        );
        $mock->method('_escapeSqlReference')->willReturnCallback(
            function ($input) {
                return sprintf('`%s`', $input);
            }
        );

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
     * Tests the SQL JOIN build method.
     *
     * @since [*next-version*]
     */
    public function testBuildSqlJoins()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $joinConditions = [
            'posts' => $this->createLogicalExpression(
                'equals',
                [
                    $this->createExpression('table_column', ['test', 'id']),
                    $this->createExpression('table_column', ['posts', 'authorId']),
                ]
            ),
            'countries' => $this->createLogicalExpression(
                'equals',
                [
                    $this->createExpression('table_column', ['test', 'countryId']),
                    $this->createExpression('table_column', ['countries', 'id']),
                ]
            ),
        ];
        $valueHashMap = [];

        $expectedCondition1 = '`test`.`id` = `posts`.`author_id`';
        $expectedCondition2 = '`test`.`country_id` = `countries`.`id`';

        $subject->method('_getSqlJoinType')->willReturn('INNER');
        $subject->expects($this->exactly(2))
                ->method('_renderSqlCondition')
                ->withConsecutive($joinConditions['posts'], $joinConditions['countries'])
                ->willReturnOnConsecutiveCalls(
                    $expectedCondition1,
                    $expectedCondition2
                );

        $result = $reflect->_buildSqlJoins($joinConditions, $valueHashMap);
        $expected = sprintf(
            'INNER JOIN `posts` ON %1$s INNER JOIN `countries` ON %2$s',
            $expectedCondition1,
            $expectedCondition2
        );

        $this->assertEquals($expected, $result, 'Expected and retrieved JOIN conditions are not the same.');
    }
}
