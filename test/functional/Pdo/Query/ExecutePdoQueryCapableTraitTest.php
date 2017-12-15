<?php

namespace RebelCode\Storage\Resource\FuncTest;

use PDO;
use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\ExecutePdoQueryCapableTrait}.
 *
 * @since [*next-version*]
 */
class ExecutePdoQueryCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\ExecutePdoQueryCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param PDO|null $pdo Optional PDO instance.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(PDO $pdo = null)
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(['_getPdo']);

        $mock = $builder->getMockForTrait();
        $mock->method('_getPdo')->willReturn($pdo);

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
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the query execution method.
     *
     * @since [*next-version*]
     */
    public function testExecutePdoQuery()
    {
        $query = uniqid('query-');
        $args = [
            uniqid('var-') => uniqid('value-'),
            uniqid('var-') => uniqid('value-'),
            uniqid('var-') => uniqid('value-'),
        ];

        $statement = $this->mock('PDOStatement')
            ->execute([$args], null, $this->once())
            ->new();

        $pdo = $this->mock('PDO')
            ->prepare([$query], $statement, $this->once())
            ->new();

        $subject = $this->createInstance($pdo);
        $reflect = $this->reflect($subject);

        $this->assertSame(
            $statement,
            $reflect->_executePdoQuery($query, $args),
            'Expected and retrieved statements are not the same.'
        );
    }
}
