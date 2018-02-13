<?php

namespace RebelCode\Storage\Resource\Pdo\FuncTest;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\Pdo\SqlColumnNamesAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SqlColumnNamesAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\SqlColumnNamesAwareTrait';

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
     * Tests the getter method to ensure that the correct values are retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNamesAllStrings()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $map = [
            uniqid('field-') => $column1 = uniqid('column-'),
            uniqid('field-') => $column2 = uniqid('column-'),
            uniqid('field-') => $column3 = uniqid('column-'),
            uniqid('field-') => $column4 = uniqid('column-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_getSqlFieldColumnMap')
                ->willReturn($map);

        $expected = [$column1, $column2, $column3, $column4];

        $this->assertSame($expected, $reflect->_getSqlColumnNames(), 'Expected and retrieved value are not the same.');
    }

    /**
     * Tests the getter method to ensure that the correct values are retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNamesAllStringables()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $map = [
            uniqid('field-') => $column1 = $this->createStringable(uniqid('column-')),
            uniqid('field-') => $column2 = $this->createStringable(uniqid('column-')),
            uniqid('field-') => $column3 = $this->createStringable(uniqid('column-')),
            uniqid('field-') => $column4 = $this->createStringable(uniqid('column-')),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_getSqlFieldColumnMap')
                ->willReturn($map);

        $expected = [$column1, $column2, $column3, $column4];

        $this->assertSame($expected, $reflect->_getSqlColumnNames(), 'Expected and retrieved value are not the same.');
    }

    /**
     * Tests the getter method to ensure that the correct values are retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSqlColumnNamesMixed()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $map = [
            uniqid('field-') => $column1 = $this->createStringable(uniqid('column-')),
            uniqid('field-') => $column2 = uniqid('column-'),
            uniqid('field-') => $column3 = $this->createStringable(uniqid('column-')),
            uniqid('field-') => $column4 = uniqid('column-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_getSqlFieldColumnMap')
                ->willReturn($map);

        $expected = [$column1, $column2, $column3, $column4];

        $this->assertSame($expected, $reflect->_getSqlColumnNames(), 'Expected and retrieved value are not the same.');
    }
}
