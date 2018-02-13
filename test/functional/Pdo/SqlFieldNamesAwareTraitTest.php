<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Pdo\SqlFieldNamesAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlFieldNamesAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlFieldNamesAwareTrait';

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
                     ->setMethods(['_getSqlFieldColumnMap'])
                     ->getMockForTrait();

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
     * Tests the getter method to ensure that the correct values are retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSqlFieldNames()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $map = [
            $field1 = uniqid('field-') => uniqid('column-'),
            $field2 = uniqid('field-') => uniqid('column-'),
            $field3 = uniqid('field-') => uniqid('column-'),
            $field4 = uniqid('field-') => uniqid('column-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_getSqlFieldColumnMap')
                ->willReturn($map);

        $expected = [$field1, $field2, $field3, $field4];

        $this->assertSame($expected, $reflect->_getSqlFieldNames(), 'Expected and retrieved value are not the same.');
    }
}
