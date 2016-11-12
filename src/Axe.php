<?php

namespace Unite\Axe;

/**
 * Class Axe
 * @package Unite\Axe
 */
class Axe
{
    /**
     * Transforms an array to XHTML attribute.
     *
     * @param string[] $attributes Attributes to format.
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

    /**
     * Transforms an array to a HTML.
     *
     * @param string[] ...$args Array to transforms.
     *
     * @return string
     */
    public static function html(...$args)
    {
        return static::transform($args, Transformation::getDefault('html'));
    }

    /**
     * Transforms an array of elements based on a Transformation.
     * If Transformation is not defined, then it'll use a default XML Transformation.
     *
     * @param string[]       $elements Elements to transforms.
     * @param Transformation $options  Transformation object.
     *
     * @return string
     */
    public static function transform($elements, Transformation $options = null)
    {
        $result  = null;
        $options = $options ?: Transformation::getDefault('xml');

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

                $tagAttributes = [];

                if ($options->advancedParsing) {
                    static::parseTag(array_shift($element), $tagName, $tagId, $tagClass);
                    $tagName = $tagName ?: $options->tagFallback;

                    // Add description attributes.
                    if ($tagId !== null) {
                        $tagAttributes['id'] = $tagId;
                    }

                    if ($tagClass !== null) {
                        $tagAttributes['class'] = $tagClass;
                    }
                }
                else {
                    $tagName = array_shift($element) ?: $options->tagFallback;
                }

                // Force lowercase to tagnames.
                if ($options->forcedLowercase === true) {
                    $tagName = strtolower($tagName);
                }

                // Capture additional tags.
                if (!empty($element[0]) &&
                    static::isAssociative($element[0])
                ) {
                    // Force lowercase to attributes names.
                    if ($options->forcedLowercase === true) {
                        $element[0] = array_combine(
                            array_map('strtolower', array_keys($element[0])),
                            array_values($element[0])
                        );
                    }

                    $tagAttributes = array_merge($tagAttributes, $element[0]);
                    array_shift($element);
                }

                // Build tag.
                $result .= "<{$tagName}";

                // Fill tag attributes.
                $tagAttributesString = static::attributes($tagAttributes);
                if ($tagAttributesString) {
                    $result .= " {$tagAttributesString}";
                }

                // Rebuild additional contents.
                if (count($element)) {
                    $transformValue = static::transform($element, $options);
                    if ($transformValue || $transformValue === '0') {
                        $result .= ">{$transformValue}</{$tagName}>";
                        continue;
                    }
                }

                // Close when forced or is a void element.
                if ($options->closeElements === true ||
                    in_array($tagName, $options->voidElements, true)
                ) {
                    $result .= $options->questionTagAllowed &&
                               strpos($tagName, '?') === 0
                        ? ' ?>'
                        : ' />';
                    continue;
                }

                $result .= "></{$tagName}>";
            }
        }

        return $result;
    }

    /**
     * Transforms an array to a XML.
     *
     * @param string[] ...$args Array to transforms.
     *
     * @return string
     */
    public static function xml(...$args)
    {
        return static::transform($args, Transformation::getDefault('xml'));
    }

    /**
     * Check if a given object is an associative array.
     *
     * @param mixed $object Object to checks.
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
     * Parses a tag description, capturing the tag name, id and classes.
     *
     * @param string $description Tag description.
     * @param string $name        Tag name.
     * @param string $id          Tag id.
     * @param string $classes     Tag classes.
     */
    private static function parseTag($description, &$name, &$id, &$classes)
    {
        // Capture tag name.
        if (preg_match('/^[a-z0-9!][a-z0-9-:]*/i', $description, $descriptionMatch)) {
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
