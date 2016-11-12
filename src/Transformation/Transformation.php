<?php

namespace Unite\Axe\Transformation;

/**
 * Class Transformation
 * @package Unite\Axe\Transformation
 */
abstract class Transformation
{
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
}
