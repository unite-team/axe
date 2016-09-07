<?php

namespace Rentalhost\VanillaAxe;

/**
 * Class Transformation
 * @package Rentalhost\VanillaAxe
 */
class Transformation
{
    /**
     * Stores the cached options.
     * @var Transformation[]
     */
    private static $cachedOptions = [];

    /**
     * When advanced parsing should be executed.
     * @var bool
     */
    public $advancedParsing = false;

    /**
     * When enabled, empty elements will be closed, like in <node />.
     * @var bool
     */
    public $closeElements = true;

    /**
     * Force tag names and attributes to be lowercased.
     * @var bool
     */
    public $forcedLowercase = false;

    /**
     * When tag is not defined on array, then this value is used.
     * @var string
     */
    public $tagFallback = 'node';

    /**
     * Stores all allowed void elements.
     * This elements will be writed as <br />.
     * @var string[]
     */
    public $voidElements = [];

    /**
     * Get the default implementation type for html or xml.
     *
     * @param string $type Type of implementation.
     *
     * @return Transformation
     */
    public static function getDefault($type)
    {
        assert(in_array($type, [ 'html', 'xml' ], true));

        if (!array_key_exists($type, self::$cachedOptions)) {
            $typeInstance = new Transformation();

            if ($type === 'html') {
                $typeInstance->advancedParsing = true;
                $typeInstance->closeElements   = false;
                $typeInstance->forcedLowercase = true;
                $typeInstance->tagFallback     = 'div';
                $typeInstance->voidElements    = [
                    'area',
                    'base',
                    'br',
                    'col',
                    'command',
                    'embed',
                    'hr',
                    'img',
                    'input',
                    'keygen',
                    'link',
                    'meta',
                    'param',
                    'source',
                    'track',
                    'wbr',
                    '!doctype'
                ];
            }

            self::$cachedOptions[$type] = $typeInstance;
        }

        return self::$cachedOptions[$type];
    }
}
