<?php

namespace RebelCode\Storage\Resource\WordPress\FuncTest;

use RebelCode\Storage\Resource\WordPress\WpInsertCapableTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpInsertCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\WpInsertCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '__',
                '_wpInsertPost',
                '_getWpInsertPostFieldKeys',
                '_getWpInsertPostMetaKey',
                '_containerGet',
                '_containerHas',
                '_normalizeArray',
                '_normalizeString',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_normalizeArray')->willReturnArgument(0);
        $mock->method('_normalizeString')->willReturnCallback(
            function($s) {
                return strval($s);
            }
        );
        $mock->method('_containerGet')->willReturnCallback(
            function($c, $k) {
                return $c[$k];
            }
        );
        $mock->method('_containerHas')->willReturnCallback(
            function($c, $k) {
                return isset($c[$k]);
            }
        );

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
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
     * Tests the insert method to assert whether the internal WordPress insertion wrapper method is called with the
     * correct transformed post data.
     *
     * @since [*next-version*]
     */
    public function testInsert()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // Keys considered as "post fields"
        $fields = [
            $f1 = uniqid('field-'),
            $f2 = uniqid('field-'),
            $f3 = uniqid('field-'),
            $f4 = uniqid('field-'),
        ];
        $subject->method('_getWpInsertPostFieldKeys')->willReturn($fields);

        $metaKey = uniqid('metadata-');
        $subject->method('_getWpInsertPostMetaKey')->willReturn($metaKey);

        // Custom meta keys
        $m1 = uniqid('meta-');
        $m2 = uniqid('meta-');
        $m3 = uniqid('meta-');
        // Input array of post data
        $input = [
            [
                $f1 => uniqid('value-'),
                $f3 => uniqid('value-'),
                $m2 => uniqid('value-'),
            ],
            [
                $m1 => uniqid('value-'),
                $f4 => uniqid('value-'),
                $f2 => uniqid('value-'),
            ],
            [
                $m1 => uniqid('value-'),
                $m3 => uniqid('value-'),
                $f1 => uniqid('value-'),
            ],
        ];
        $expected = [
            [
                $f1      => $input[0][$f1],
                $f3      => $input[0][$f3],
                $metaKey => [
                    $m2 => $input[0][$m2],
                ],
            ],
            [
                $f4      => $input[1][$f4],
                $f2      => $input[1][$f2],
                $metaKey => [
                    $m1 => $input[1][$m1],
                ],
            ],
            [
                $f1      => $input[2][$f1],
                $metaKey => [
                    $m1 => $input[2][$m1],
                    $m3 => $input[2][$m3],
                ],
            ],
        ];

        $subject->expects($this->exactly(count($expected)))
                ->method('_wpInsertPost')
                ->withConsecutive([$expected[0]], [$expected[1]], [$expected[2]]);

        $reflect->_insert($input);
    }
}
