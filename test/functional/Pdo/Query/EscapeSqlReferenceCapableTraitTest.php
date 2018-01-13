<?php

namespace RebelCode\Storage\Resource\Pdo\Query\FuncTest;

use PHPUnit_Framework_MockObject_MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EscapeSqlReferenceCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\EscapeSqlReferenceCapableTrait';

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
                        ->setMethods([]);

        $mock = $builder->getMockForTrait();

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
     * Tests the reference escape method to assert whether an input reference string is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReference()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reference = uniqid('ref-');
        $expected = "`$reference`";

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReference($reference),
            'Retrieved and expected escaped references do not match.'
        );
    }

    /**
     * Tests the reference escape method with an empty string to assert whether the output is also empty.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferenceEmptyString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $result = $reflect->_escapeSqlReference('');

        $this->assertEquals(0, strlen($result), 'Result is not empty.');
    }

    /**
     * Tests the reference escape array method to assert whether an input reference list is correctly escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferenceArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $references = [
            $ref1 = uniqid('ref-'),
            $ref2 = uniqid('ref-'),
            $ref3 = uniqid('ref-'),
        ];
        $expected = "`$ref1`, `$ref2`, `$ref3`";

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferenceArray($references),
            'Retrieved and expected escaped reference lists do not match.'
        );
    }

    /**
     * Tests the reference escape array method with an empty array to assert whether the output is also empty.
     * escaped.
     *
     * @since [*next-version*]
     */
    public function testEscapeSqlReferenceArrayEmpty()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $references = [];
        $expected = '';

        $this->assertEquals(
            $expected,
            $reflect->_escapeSqlReferenceArray($references),
            'Retrieved and expected escaped reference lists do not match.'
        );
    }
}
