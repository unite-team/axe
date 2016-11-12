<?php

namespace Unite\Axe;

/**
 * Class Transformation
 * @package Unite\Axe
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
     * Advanced parsing will parse tag#id.class to a valid tag with attribute id and class filled.
     * @var bool
     */
    public $advancedParsing = false;

    /**
     * When enabled, empty elements will be closed, like in <node />.
     * It'll be allowed only if the element haven't contents or it's an empty void element.
     * @var bool
     */
    public $closeElements = true;

    /**
     * Force tag names and attributes to be lowercased.
     * By default, names and attributes are renderized as is.
     *
     * Caution: if conflicts are detected, last definition will be prioritized when this option is enabled.
     * @var bool
     */
    public $forcedLowercase = false;

    /**
     * When enabled, it'll override "/>" by "?>" when tagname is started by "?" (like "?xml").
     * @var bool
     */
    public $questionTagAllowed = true;

    /**
     * Tag name that is used when it is not declared on element tag.
     * @var string
     */
    public $tagFallback = 'node';

    /**
     * Stores all allowed void elements.
     * This elements will be writed as "<br />".
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
                $typeInstance->advancedParsing    = true;
                $typeInstance->closeElements      = false;
                $typeInstance->forcedLowercase    = true;
                $typeInstance->questionTagAllowed = false;
                $typeInstance->tagFallback        = 'div';
                $typeInstance->voidElements       = [
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
