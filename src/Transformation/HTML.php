<?php

namespace Unite\Axe\Transformation;

/**
 * Class HTML
 * @package Unite\Axe\Transformation
 */
class HTML extends Transformation
{
    /**
     * @inheritdoc
     */
    public $advancedParsing = true;

    /**
     * @inheritdoc
     */
    public $closeElements = false;

    /**
     * @inheritdoc
     */
    public $forcedLowercase = true;

    /**
     * @inheritdoc
     */
    public $questionTagAllowed = false;

    /**
     * @inheritdoc
     */
    public $tagFallback = 'div';

    /**
     * @inheritdoc
     */
    public $voidElements = [
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
