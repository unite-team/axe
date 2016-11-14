<?php

declare(strict_types = 1);

namespace Unite\Axe\Transformation;

/**
 * Class TransformationFactory
 * @package Unite\Axe\Transformation
 */
class TransformationFactory
{
    /**
     * Stores all initialized transformations.
     * @var Transformation[]
     */
    private static $factoryCache = [];

    /**
     * Returns a cached version of a Transformation class.
     *
     * @param string $transformationClass Transformation class name.
     *
     * @return Transformation
     */
    public static function get(string $transformationClass): Transformation
    {
        assert(is_subclass_of($transformationClass, Transformation::class));

        if (!array_key_exists($transformationClass, self::$factoryCache)) {
            self::$factoryCache[$transformationClass] = new $transformationClass;
        }

        return self::$factoryCache[$transformationClass];
    }

}
