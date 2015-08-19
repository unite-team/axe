<?php

namespace Rentalhost\VanillaAxe;

class Axe
{
    /**
     * List of void HTML elements.
     * @var string[]
     */
    private static $HTML_VOID_ELEMENTS = [
        "area",
        "base",
        "br",
        "col",
        "command",
        "embed",
        "hr",
        "img",
        "input",
        "keygen",
        "link",
        "meta",
        "param",
        "source",
        "track",
        "wbr"
    ];

    /**
     * Transforms an array into HTML.
     * @param array|string ...$args Objects to transform.
     * @return string
     */
    public static function html()
    {
        return static::transform(func_get_args(), static::normalizeOptions([
            "voidElements" => static::$HTML_VOID_ELEMENTS,
            "closeElements" => false,
            "tagNull" => "div"
        ]));
    }

    /**
     * Transforms an array into XML.
     * @param array|string ...$args Objects to transform.
     * @return string
     */
    public static function xml()
    {
        return static::transform(func_get_args(), static::normalizeOptions());
    }

    /**
     * Transformation process.
     * @param  array $elements Elements to transforms.
     * @param  array $options  Options.
     * @return string
     */
    public static function transform($elements, $options)
    {
        $result = null;

        foreach ($elements as $element) {
            // Consider a string as literal result.
            if (is_string($element)) {
                $result.= $element;
            }

            // Consider an array as a transformable object.
            if (is_array($element)) {
                // Ignore empty elements.
                if (empty($element)) {
                    continue;
                }

                // The first element value is the tag description.
                static::parseTag(array_shift($element), $tagName, $tagId, $tagClass);

                // If tag name is empty, so use tag null option.
                $tagName = $tagName ?: $options["tagNull"];
                $tagAttributes = [];

                // Add description attributes.
                if (!empty($tagId)) {
                    $tagAttributes["id"] = $tagId;
                }

                if (!empty($tagClass)) {
                    $tagAttributes["class"] = $tagClass;
                }

                // Capture additional tags.
                if (!empty($element[0])
                &&  static::isAssociative($element[0])) {
                    $tagAttributes = array_merge($tagAttributes, $element[0]);
                    array_shift($element);
                }

                // Build tag.
                $result.= "<{$tagName}";

                // Fill tag attributes.
                foreach ($tagAttributes as $attributeKey => $attributeValue) {
                    $result.= " " . htmlspecialchars($attributeKey) . '="' . htmlspecialchars($attributeValue) . '"';
                }

                // Rebuild additional contents.
                if (!empty($element)) {
                    $result.= ">" . static::transform($element, $options) . "</{$tagName}>";
                    continue;
                }

                // If it is a void element, close element.
                if (in_array($tagName, $options["voidElements"])) {
                    $result.= " />";
                    continue;
                }

                // If tag content is empty, and close elements is off,
                // so void it.
                if ($options["closeElements"] === true
                &&  empty($tagContent)) {
                    $result.= " />";
                    continue;
                }

                $result.= "></{$tagName}>";
            }
        }

        return $result;
    }

    /**
     * Returns an option array normalized.
     * @param  array $options                 Options to overwrite.
     * @param  array $options["voidElements"] List all void elements.
     * @return array
     */
    private static function normalizeOptions(array $options = null)
    {
        return array_replace([
            "voidElements" => [],
            "closeElements" => true,
            "tagNull" => "node"
        ], $options ?: []);
    }

    /**
     * Parse a tag description, capturing the tag name, id and classes.
     * @param  string     $description Tag description.
     * @param  reference &$name        Tag name.
     * @param  reference &$id          Tag id.
     * @param  reference &$classes     Tag classes.
     * @return array
     */
    private static function parseTag($description, &$name = null, &$id = null, &$classes = null)
    {
        // Capture tag name.
        if (preg_match('/^[a-z0-9][a-z0-9-:]*/i', $description, $descriptionMatch)) {
            $name = strtolower($descriptionMatch[0]);
        }

        // Capture tag id.
        if (preg_match('/#([\w\d-]+)/', $description, $descriptionMatch)) {
            $id = $descriptionMatch[1];
        }

        // Capture tag classes.
        if (preg_match_all('/\.([\w\d-]+)/', $description, $descriptionMatch)) {
            $classes = join(" ", $descriptionMatch[1]);
        }
    }

    /**
     * Check if object is associative.
     * @param  mixed $object Object to check.
     * @return boolean
     */
    private static function isAssociative($object)
    {
        if (!is_array($object)) {
            return false;
        }

        return (bool) count(array_filter(array_keys($object), "is_string"));
    }
}
