<?php

declare(strict_types = 1);

namespace Unite\Axe\Transformation;

use PHPUnit_Framework_TestCase;

/**
 * Class TransformationFactoryTest
 * @package Unite\Axe\Transformation
 */
class TransformationFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check if get method returns the correct transformation types.
     * @covers \Unite\Axe\Transformation\TransformationFactory::get
     */
    public function testGetMethod()
    {
        $privateProperty = new \ReflectionProperty(TransformationFactory::class, 'factoryCache');
        $privateProperty->setAccessible(true);
        $privateProperty->setValue([]);

        static::assertInstanceOf(HTML::class, TransformationFactory::get(HTML::class));
        static::assertInstanceOf(XML::class, TransformationFactory::get(XML::class));
    }
}
