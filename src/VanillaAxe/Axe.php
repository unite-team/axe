<?php

namespace Rentalhost\VanillaAxe;

/**
 * Class Axe
 * @package Rentalhost\VanillaAxe
 */
class Axe
{
    /**
     * List of void HTML elements.
     * @var string[]
     */
    private static $HTML_VOID_ELEMENTS = [
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
    ];

    /**
     * Returns a formatted attributes structure.
     *
     * @param array $attributes Attributes to format.
     *
     * @return string
     */
    public static function attributes($attributes)
    {
        $attributesResult = [];

        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === true ||
                $attributeValue === ''
            ) {
                $attributesResult[] = htmlspecialchars($attributeKey);
                continue;
            }

            if ($attributeValue === false) {
                continue;
            }

            if (is_int($attributeKey)) {
                $attributesResult[] = '"' . htmlspecialchars($attributeValue) . '"';
                continue;
            }

            $attributesResult[] = htmlspecialchars($attributeKey) . '="' . htmlspecialchars($attributeValue) . '"';
        }

        return implode(' ', $attributesResult);
    }

    /** @noinspection PhpDocSignatureInspection */
    /**
     * Transforms an array into HTML.
     *
     * @param array|string ...$args Objects to transform.
     *
     * @return string
     */
    public static function html(/** ...$args */)
    {
        return static::transform(func_get_args(), static::normalizeOptions([
            'voidElements'   => static::$HTML_VOID_ELEMENTS,
            'closeElements'  => false,
            'tagNull'        => 'div',
            'forceLowercase' => true
        ]));
    }

    /**
     * Transformation process.
     *
     * @param  array $elements Elements to transforms.
     * @param  array $options  Options.
     *
     * @return string
     */
    public static function transform($elements, $options)
    {
        $result = null;

        /** @var mixed[]|string|mixed $element */
        foreach ($elements as $element) {
            // Consider a scalar as literal result.
            // Note: boolean too is a scalar value, true will turns 1 and false will turns empty.
            if (is_scalar($element)) {
                $result .= $element;
            }

            // Consider an array as a transformable object.
            if (is_array($element)) {
                // Ignore empty elements.
                if (!count($element)) {
                    continue;
                }

                // If the first element is an array, then it is a elements container.
                if (is_array($element[0])) {
                    $result .= static::transform($element, $options);
                    continue;
                }

                // The first element value is the tag description.
                unset ($tagName, $tagId, $tagClass);
                static::parseTag(array_shift($element), $tagName, $tagId, $tagClass);

                // If tag name is empty, so use tag null option.
                $tagName       = $tagName ?: $options['tagNull'];
                $tagAttributes = [];

                // Force lowercase to tagnames.
                if ($options['forceLowercase'] === true) {
                    $tagName = strtolower($tagName);
                }

                // Add description attributes.
                if ($tagId !== null) {
                    $tagAttributes['id'] = $tagId;
                }

                if ($tagClass !== null) {
                    $tagAttributes['class'] = $tagClass;
                }

                // Capture additional tags.
                if (!empty($element[0]) &&
                    static::isAssociative($element[0])
                ) {
                    // Force lowercase to attributes names.
                    if ($options['forceLowercase'] === true) {
                        $element[0] = array_combine(
                            array_map('strtolower', array_keys($element[0])),
                            array_values($element[0])
                        );
                    }

                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $tagAttributes = array_merge($tagAttributes, $element[0]);
                    array_shift($element);
                }

                // Build tag.
                $result .= "<{$tagName}";

                // Fill tag attributes.
                $tagAttributesString = static::attributes($tagAttributes);
                if ($tagAttributesString) {
                    $result .= ' ' . $tagAttributesString;
                }

                // Rebuild additional contents.
                if (count($element)) {
                    $transformValue = static::transform($element, $options);
                    if ($transformValue || $transformValue === '0') {
                        $result .= ">{$transformValue}</{$tagName}>";
                        continue;
                    }
                }

                // If it is a void element, close element.
                if (in_array($tagName, $options['voidElements'], true)) {
                    $result .= ' />';
                    continue;
                }

                // Close element.
                if ($options['closeElements'] === true) {
                    $result .= ' />';
                    continue;
                }

                $result .= "></{$tagName}>";
            }
        }

        return $result;
    }

    /** @noinspection PhpDocSignatureInspection */
    /**
     * Transforms an array into XML.
     *
     * @param array|string ...$args Objects to transform.
     *
     * @return string
     */
    public static function xml(/** ...$args */)
    {
        return static::transform(func_get_args(), static::normalizeOptions());
    }

    /**
     * Check if object is associative.
     *
     * @param  mixed $object Object to check.
     *
     * @return boolean
     */
    private static function isAssociative($object)
    {
        if (!is_array($object)) {
            return false;
        }

        return (bool) count(array_filter(array_keys($object), 'is_string'));
    }

    /**
     * Returns an option array normalized.
     *
     * @param  array $options Options to overwrite.
     *
     * @internal param array   $options["voidElements"]  List all void elements.
     * @internal param boolean $options["closeElements"] If should to close void elements.
     * @internal param string  $options["tagNull"]       Tag name where it isn't defined.
     *
     * @return array
     */
    private static function normalizeOptions($options = null)
    {
        return array_replace([
            'voidElements'   => [],
            'closeElements'  => true,
            'tagNull'        => 'node',
            'forceLowercase' => false
        ], $options ?: []);
    }

    /**
     * Parse a tag description, capturing the tag name, id and classes.
     *
     * @param  string $description Tag description.
     * @param  string &$name       Tag name.
     * @param  string &$id         Tag id.
     * @param  string &$classes    Tag classes.
     */
    private static function parseTag($description, &$name, &$id, &$classes)
    {
        // Capture tag name.
        if (preg_match('/^[a-z0-9][a-z0-9-:]*/i', $description, $descriptionMatch)) {
            $name = $descriptionMatch[0];
        }

        // Capture tag id.
        if (preg_match('/#([\w\d-]+)/', $description, $descriptionMatch)) {
            $id = $descriptionMatch[1];
        }

        // Capture tag classes.
        if (preg_match_all('/\.([\w\d-]+)/', $description, $descriptionMatch)) {
            $classes = implode(' ', $descriptionMatch[1]);
        }
    }
}
