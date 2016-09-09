<?php

namespace Rentalhost\VanillaAxe;

use PHPUnit_Framework_TestCase;

/**
 * Class TransformationTest
 * @package Rentalhost\VanillaAxe
 */
class TransformationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test getDefault() method.
     *
     * @covers \Rentalhost\VanillaAxe\Transformation::getDefault
     */
    public function testGetDefault()
    {
        foreach ([ 'xml', 'html' ] as $transformType) {
            $this->clearCachedOptions();

            self::assertInstanceOf(Transformation::class, Transformation::getDefault($transformType));
            self::assertInstanceOf(Transformation::class, Transformation::getDefault($transformType));
        }
    }

    /**
     * Clear cached options.
     */
    private function clearCachedOptions()
    {
        $reflection = new \ReflectionProperty(Transformation::class, 'cachedOptions');
        $reflection->setAccessible(true);
        $reflection->setValue([]);
    }
}
