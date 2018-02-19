<?php

namespace RebelCode\Storage\Resource\Pdo\Query\UnitTest;

use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Storage\Resource\Pdo\Query\GetPdoValueHashStringCapableTrait}.
 *
 * @since [*next-version*]
 */
class GetPdoValueHashStringCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\Query\GetPdoValueHashStringCapableTrait';

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
                                    '_normalizeString',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function ($input) {
                return (string) $input;
            }
        );

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
     * Tests the PDO value hash method with a string input.
     *
     * @since [*next-version*]
     */
    public function testGetPdoValueHashString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = uniqid('string-');
        $hash = $reflect->_getPdoValueHashString($value);

        $this->assertInternalType('string', $hash, 'Hash is not a string.');
        $this->assertStringStartsWith(':', $hash, 'Hash prefix is incorrect.');
    }

    /**
     * Tests the PDO value hash method with an integer input.
     *
     * @since [*next-version*]
     */
    public function testGetPdoValueHashStringInteger()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = rand(0, 1000);
        $hash = $reflect->_getPdoValueHashString($value);

        $this->assertInternalType('string', $hash, 'Hash is not a string.');
        $this->assertStringStartsWith(':', $hash, 'Hash prefix is incorrect.');
    }

    /**
     * Tests the PDO value hash method with a float input.
     *
     * @since [*next-version*]
     */
    public function testGetPdoValueHashStringFloat()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = rand(0, 1000) / ((float) rand(1, 1000));
        $hash = $reflect->_getPdoValueHashString($value);

        $this->assertInternalType('string', $hash, 'Hash is not a string.');
        $this->assertStringStartsWith(':', $hash, 'Hash prefix is incorrect.');
    }

    /**
     * Tests the PDO value hash method with a boolean input.
     *
     * @since [*next-version*]
     */
    public function testGetPdoValueHashStringBool()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = (bool) rand(0, 1);
        $hash = $reflect->_getPdoValueHashString($value);

        $this->assertInternalType('string', $hash, 'Hash is not a string.');
        $this->assertStringStartsWith(':', $hash, 'Hash prefix is incorrect.');
    }

    /**
     * Tests the PDO value hash method with a Stringable input.
     *
     * @since [*next-version*]
     */
    public function testGetPdoValueHashStringStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = $this->mock('Dhii\Util\String\StringableInterface')
                      ->__toString(uniqid('string-'))
                      ->new();
        $hash = $reflect->_getPdoValueHashString($value);

        $this->assertInternalType('string', $hash, 'Hash is not a string.');
        $this->assertStringStartsWith(':', $hash, 'Hash prefix is incorrect.');
    }

    /**
     * Tests the PDO value hash method with an invalid input.
     *
     * @since [*next-version*]
     */
    public function testGetPdoValueHashStringInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = new stdClass();
        $this->setExpectedException('InvalidArgumentException');

        $subject->method('_normalizeString')->willThrowException(new InvalidArgumentException());
        $reflect->_getPdoValueHashString($value);
    }
}
