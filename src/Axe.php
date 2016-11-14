<?php

declare(strict_types = 1);

namespace Unite\Axe;

use Unite\Axe\Transformation\HTML;
use Unite\Axe\Transformation\Transformation;
use Unite\Axe\Transformation\XML;

/**
 * Class Axe
 * @package Unite\Axe
 */
class Axe
{
    /**
     * Stores all initialized transformations.
     * @var Transformation[]
     */
    private static $transformationsCache = [];

    /**
     * Transforms an array to XHTML attribute.
     *
     * @param string[] $attributes Attributes to format.
     *
     * @return string
     */
    public static function attributes(array $attributes): string
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
                $attributesResult[] = '"' . htmlspecialchars((string) $attributeValue) . '"';
                continue;
            }

            $attributesResult[] = htmlspecialchars($attributeKey) . '="' . htmlspecialchars((string) $attributeValue) . '"';
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
    public static function html(...$args): string
    {
        return static::transform($args, self::getTransformation(HTML::class));
    }

    /**
     * Transforms an array of elements based on a Transformation.
     * If a Transformation is not defined, then it'll use a default XML Transformation.
     *
     * @param string[]              $elements            Elements to transforms.
     * @param string|Transformation $transformationClass Transformation class reference.
     *
     * @return string
     */
    public static function transform(array $elements, $transformationClass = null): string
    {
        $result         = '';
        $transformation = $transformationClass instanceof Transformation
            ? $transformationClass
            : self::getTransformation($transformationClass ?: XML::class);

        /** @var mixed[]|string|mixed $element */
        foreach ($elements as $element) {
            // Consider an array as a transformable object.
            if (is_array($element)) {
                // Ignore empty elements.
                if (!count($element)) {
                    continue;
                }

                // If the first element is an array, then it is a elements container.
                if (is_array($element[0])) {
                    $result .= static::transform($element, $transformation);
                    continue;
                }

                // The first element value is the tag description.
                unset ($tagName, $tagId, $tagClass);

                $tagAttributes = [];

                if ($transformation->advancedParsing) {
                    $tagDescription = array_shift($element);
                    $tagName        = $transformation->tagFallback;

                    if ($tagDescription !== null) {
                        static::parseTag($tagDescription, $tagName, $tagId, $tagClass);

                        // Add description attributes.
                        if ($tagId !== null) {
                            $tagAttributes['id'] = $tagId;
                        }

                        if ($tagClass !== null) {
                            $tagAttributes['class'] = $tagClass;
                        }
                    }
                }
                else {
                    $tagName = array_shift($element) ?: $transformation->tagFallback;
                }

                // Force lowercase to tagnames.
                if ($transformation->forcedLowercase === true) {
                    $tagName = strtolower($tagName);
                }

                // Capture additional tags.
                while (!empty($element[0]) &&
                       static::isAssociative($element[0])) {
                    // Force lowercase to attributes names.
                    if ($transformation->forcedLowercase === true) {
                        $element[0] = array_combine(
                            array_map('strtolower', array_keys($element[0])),
                            array_values($element[0])
                        );
                    }

                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    // Greedy replace is need here to avoid workaround with boolean.
                    $tagAttributes = array_replace($tagAttributes, array_shift($element));
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
                    $transformValue = static::transform($element, $transformation);
                    if ($transformValue || $transformValue === '0') {
                        $result .= ">{$transformValue}</{$tagName}>";
                        continue;
                    }
                }

                // Close when forced or is a void element.
                if ($transformation->closeElements === true ||
                    in_array($tagName, $transformation->voidElements, true)
                ) {
                    $result .= $transformation->questionTagAllowed &&
                               strpos($tagName, '?') === 0
                        ? ' ?>'
                        : ' />';
                    continue;
                }

                $result .= "></{$tagName}>";
                continue;
            }

            // If not a array, then should be scalar or null.
            // For scalar, just append it as literal.
            assert(is_scalar($element) || $element === null);
            $result .= $element;
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
    public static function xml(...$args): string
    {
        return static::transform($args, self::getTransformation(XML::class));
    }

    /**
     * Returns a cached version of a Transformation class.
     *
     * @param string $transformationClass Transformation class name.
     *
     * @return Transformation
     */
    private static function getTransformation(string $transformationClass): Transformation
    {
        assert(is_subclass_of($transformationClass, Transformation::class));

        if (!array_key_exists($transformationClass, self::$transformationsCache)) {
            self::$transformationsCache[$transformationClass] = new $transformationClass;
        }

        return self::$transformationsCache[$transformationClass];
    }

    /**
     * Check if a given object is an associative array.
     *
     * @param mixed $object Object to checks.
     *
     * @return boolean
     */
    private static function isAssociative($object): bool
    {
        if (!is_array($object)) {
            return false;
        }

        return array_values($object) !== $object;
    }

    /**
     * Parses a tag description, capturing the tag name, id and classes.
     *
     * @param string $description Tag description.
     * @param string $name        Tag name.
     * @param string $id          Tag id.
     * @param string $classes     Tag classes.
     */
    private static function parseTag(string $description, &$name, &$id, &$classes)
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
