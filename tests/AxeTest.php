<?php

declare(strict_types = 1);

namespace Unite\Axe;

use PHPUnit_Framework_TestCase;

/**
 * Class AxeTest
 * @package Unite\Axe
 */
class AxeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function dataAttributesMethod(): array
    {
        return [
            [ [], '' ],
            [ [ 1 ], '"1"' ],
            [ [ 1, 2, 3 ], '"1" "2" "3"' ],
            [ [ 'a' => 'b' ], 'a="b"' ],
            [ [ 'data-name' => 'quote"value' ], 'data-name="quote&quot;value"' ],
            [ [ 'a' => 'b', 'c' => 'd' ], 'a="b" c="d"' ],
            [ [ 'a' => true ], 'a' ],
            [ [ 'a' => '' ], 'a' ],
            [ [ 'a' => 0 ], 'a="0"' ],
            [ [ 'a' => false ], '' ],
            [ [ 'a' => true, 'b' => false ], 'a' ],
            [ [ 'a' => '', 'b' => '' ], 'a b' ],
            [ [ 'a' => 0, 'b' => true ], 'a="0" b' ],
            [ [ 'a' => false, 'b' => '' ], 'b' ],
        ];
    }

    /**
     * @return array
     */
    public function dataTransformsMethods(): array
    {
        return [
            // HTML: Literal String.
            100000 =>
                [ 'html', [], '' ],
            [ 'html', [ 'Hello World' ], 'Hello World' ],

            // HTML: Simple Tags.
            100100 =>
                [ 'html', [ [] ], '' ],
            [ 'html', [ [ '' ] ], '<div></div>' ],
            [ 'html', [ [ null ] ], '<div></div>' ],
            [ 'html', [ [ 'br' ] ], '<br />' ],
            [ 'html', [ [ 'div' ] ], '<div></div>' ],

            // HTML: Description Tags.
            100200 =>
                [ 'html', [ [ '#hello' ] ], '<div id="hello"></div>' ],
            [ 'html', [ [ '.hello' ] ], '<div class="hello"></div>' ],
            [ 'html', [ [ '#he.llo.world' ] ], '<div id="he" class="llo world"></div>' ],

            // HTML: Attributes.
            100300 =>
                [ 'html', [ [ 'div', [ 'id' => 'hello' ] ] ], '<div id="hello"></div>' ],
            [ 'html', [ [ 'div#hello', [ 'id' => 'world' ] ] ], '<div id="world"></div>' ],
            [ 'html', [ [ 'div', [ 'class' => 0 ] ] ], '<div class="0"></div>' ],

            // HTML: Content.
            100400 =>
                [ 'html', [ [ 'div', 'hello' ] ], '<div>hello</div>' ],
            [ 'html', [ [ 'div#hello', 'world' ] ], '<div id="hello">world</div>' ],
            [ 'html', [ [ 'div#hello', 'world', [ 'br' ] ] ], '<div id="hello">world<br /></div>' ],
            [ 'html', [ [ 'div#hello', 'world', [ 'br' ], [ 'br' ] ] ], '<div id="hello">world<br /><br /></div>' ],

            // HTML: Force void to allow contents (why?).
            100500 =>
                [ 'html', [ [ 'br', 'hello' ] ], '<br>hello</br>' ],

            // HTML: Avoid reference copy.
            100600 =>
                [
                    'html',
                    [ [ 'div', [ 'span#id.class' ], [ null ] ] ],
                    '<div><span id="id" class="class"></span><div></div></div>',
                ],

            // HTML: Simple values.
            100700 =>
                [ 'html', [ [ 'div', 1 ] ], '<div>1</div>' ],
            [ 'html', [ [ 'div', 1.5 ] ], '<div>1.5</div>' ],
            [ 'html', [ [ 'div', true ] ], '<div>1</div>' ],
            [ 'html', [ [ 'div', true, true ] ], '<div>11</div>' ],
            [ 'html', [ [ 'div', true, false, true ] ], '<div>11</div>' ],
            [ 'html', [ [ 'div', false ] ], '<div></div>' ],
            [ 'html', [ [ 'div', null ] ], '<div></div>' ],
            [ 'html', [ [ 'div', '' ] ], '<div></div>' ],
            [ 'html', [ [ 'div', 0 ] ], '<div>0</div>' ],
            [ 'html', [ [ 'div', 0.0 ] ], '<div>0</div>' ],
            [ 'html', [ [ 'div', 0.1 ] ], '<div>0.1</div>' ],
            [ 'html', [ [ 'div', '0.0' ] ], '<div>0.0</div>' ],
            [ 'html', [ [ 'div', '0' ] ], '<div>0</div>' ],
            [ 'html', [ [ 'div', '00' ] ], '<div>00</div>' ],
            [ 'html', [ [ 'div', '-0' ] ], '<div>-0</div>' ],

            // HTML: Unescaped values.
            100800 =>
                [ 'html', [ [ 'div', '<br />' ] ], '<div><br /></div>' ],

            // HTML: Elements container.
            100900 =>
                [ 'html', [ [ 'div', [ [ 'div' ] ] ] ], '<div><div></div></div>' ],
            [ 'html', [ [ 'div', [ [ 'div' ], [ 'div' ] ] ] ], '<div><div></div><div></div></div>' ],
            [ 'html', [ [ 'div', [ [ 'div' ], [ 'div' ], 'br' ] ] ], '<div><div></div><div></div>br</div>' ],

            // HTML: Empty attributes support.
            101000 =>
                [ 'html', [ [ 'input', [ 'checked' => true ] ] ], '<input checked />' ],
            [ 'html', [ [ 'input', [ 'checked' => '' ] ] ], '<input checked />' ],
            [ 'html', [ [ 'input', [ 'value' => 0 ] ] ], '<input value="0" />' ],
            [ 'html', [ [ 'input', [ 'checked' => false ] ] ], '<input />' ],

            // HTML: elements and attributes should be lowercased.
            101100 =>
                [ 'html', [ [ 'BR' ] ], '<br />' ],
            [ 'html', [ [ 'Link' ] ], '<link />' ],
            [ 'html', [ [ 'DIV' ] ], '<div></div>' ],
            [ 'html', [ [ 'INPUT', [ 'CHECKED' => true ] ] ], '<input checked />' ],
            [ 'html', [ [ 'INPUT', [ 'Value' => 123 ] ] ], '<input value="123" />' ],
            [ 'html', [ [ 'INPUT', [ 'Value' => 'Value' ] ] ], '<input value="Value" />' ],
            [ 'html', [ [ 'INPUT', [ 'VALUE' => '1', 'Value' => '2' ] ] ], '<input value="2" />' ],

            // HTML: DOCTYPE support.
            101200 =>
                [ 'html', [ [ '!doctype' ] ], '<!doctype />' ],
            [ 'html', [ [ '!DOCTYPE' ] ], '<!doctype />' ],
            [ 'html', [ [ '!doctype', [ 'html' => true ] ] ], '<!doctype html />' ],
            [ 'html', [ [ '!doctype', [ 'html' => true, 'public' => true ] ] ], '<!doctype html public />' ],
            [
                'html',
                [
                    [
                        '!doctype',
                        [ 'html' => true, 'public' => true, '-//W3C//DTD HTML 4.01//EN' ]
                    ]
                ],
                '<!doctype html public "-//W3C//DTD HTML 4.01//EN" />',
            ],
            [
                'html',
                [
                    [
                        '!doctype',
                        [ 'html' => true, 'public' => true, '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/html4/strict.dtd' ]
                    ]
                ],
                '<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" />'
            ],

            // HTML: XML header should not be parsed as valid - so it'll use fallback (div).
            101300 =>
                [ 'html', [ [ '?xml', [ 'data-version' => '1.0' ] ] ], '<div data-version="1.0"></div>' ],
            [ 'html', [ [ '?test', [ 'data-version' => '1.0' ] ] ], '<div data-version="1.0"></div>' ],
            [ 'html', [ [ '?xml', [ 'data-version' => '1.0' ], 'Hello' ] ], '<div data-version="1.0">Hello</div>' ],
            [ 'html', [ [ '?xml', 'Hello' ] ], '<div>Hello</div>' ],

            // HTML: more attributes definition to element.
            101400 =>
                [ 'html', [ [ 'input', [ 'checked' => true ], [ 'readonly' => true ] ] ], '<input checked readonly />' ],
            [ 'html', [ [ 'input', [ 'value' => '1' ], [ 'value' => '2' ] ] ], '<input value="2" />' ],
            [
                'html',
                [ [ 'div', [ 'data-a' => '1' ], [ 'data-b' => '2' ], 'content' ] ],
                '<div data-a="1" data-b="2">content</div>'
            ],
            [ 'html', [ [ 'input.test1', [ 'class' => 'test2' ] ] ], '<input class="test2" />' ],
            [ 'html', [ [ 'input.test1', [ 'class' => 'test2' ], [ 'class' => 'test3' ] ] ], '<input class="test3" />' ],

            // HTML: empty array.
            101500 =>
                [ 'html', [], '' ],
            [ 'html', [ [] ], '' ],
            [ 'html', [ [ 'div', [] ] ], '<div></div>' ],
            [ 'html', [ [ 'div', [], [] ] ], '<div></div>' ],
            [ 'html', [ [ 'div', [], 'content' ] ], '<div>content</div>' ],
            [ 'html', [ [ 'div', [], 'content', [] ] ], '<div>content</div>' ],

            // XML: Literal String.
            200000 =>
                [ 'xml', [ 'Hello World' ], 'Hello World' ],

            // XML: Simple Tags.
            200100 =>
                [ 'xml', [ [] ], '' ],
            [ 'xml', [ [ '' ] ], '<node />' ],
            [ 'xml', [ [ null ] ], '<node />' ],
            [ 'xml', [ [ 'br' ] ], '<br />' ],
            [ 'xml', [ [ 'div' ] ], '<div />' ],

            // XML: Advanced Parsing is not supported for XML.
            200200 =>
                [ 'xml', [ [ '#hello' ] ], '<#hello />' ],
            [ 'xml', [ [ '.hello' ] ], '<.hello />' ],
            [ 'xml', [ [ '#he.llo.world' ] ], '<#he.llo.world />' ],

            // XML: Attributes.
            200300 =>
                [ 'xml', [ [ 'div', [ 'id' => 'hello' ] ] ], '<div id="hello" />' ],
            [ 'xml', [ [ 'div#hello', [ 'id' => 'world' ] ] ], '<div#hello id="world" />' ],
            [ 'xml', [ [ 'div', [ 'class' => 0 ] ] ], '<div class="0" />' ],

            // XML: Content.
            200400 =>
                [ 'xml', [ [ 'div', 'hello' ] ], '<div>hello</div>' ],
            [ 'xml', [ [ 'div#hello', 'world' ] ], '<div#hello>world</div#hello>' ],
            [ 'xml', [ [ 'div#hello', 'world', [ 'br' ] ] ], '<div#hello>world<br /></div#hello>' ],

            // XML: Avoid reference copy.
            200600 =>
                [
                    'xml',
                    [ [ 'div', [ 'span#id.class' ], [ null ] ] ],
                    '<div><span#id.class /><node /></div>',
                ],

            // XML: Simple values.
            200700 =>
                [ 'xml', [ [ 'node', 1 ] ], '<node>1</node>' ],
            [ 'xml', [ [ 'node', 1.5 ] ], '<node>1.5</node>' ],
            [ 'xml', [ [ 'node', true ] ], '<node>1</node>' ],
            [ 'xml', [ [ 'node', true, true ] ], '<node>11</node>' ],
            [ 'xml', [ [ 'node', true, false, true ] ], '<node>11</node>' ],
            [ 'xml', [ [ 'node', false ] ], '<node />' ],
            [ 'xml', [ [ 'node', null ] ], '<node />' ],
            [ 'xml', [ [ 'node', '' ] ], '<node />' ],
            [ 'xml', [ [ 'node', 0 ] ], '<node>0</node>' ],

            // XML: Unescaped values.
            200800 =>
                [ 'xml', [ [ 'div', '<br />' ] ], '<div><br /></div>' ],

            // XML: Elements container.
            200900 =>
                [ 'xml', [ [ 'div', [ [ 'div' ] ] ] ], '<div><div /></div>' ],
            [ 'xml', [ [ 'div', [ [ 'div' ], [ 'div' ] ] ] ], '<div><div /><div /></div>' ],
            [ 'xml', [ [ 'div', [ [ 'div' ], [ 'div' ], 'br' ] ] ], '<div><div /><div />br</div>' ],

            // XML: Empty attributes support.
            201000 =>
                [ 'xml', [ [ 'input', [ 'checked' => true ] ] ], '<input checked />' ],
            [ 'xml', [ [ 'input', [ 'checked' => '' ] ] ], '<input checked />' ],
            [ 'xml', [ [ 'input', [ 'value' => 0 ] ] ], '<input value="0" />' ],
            [ 'xml', [ [ 'input', [ 'checked' => false ] ] ], '<input />' ],

            // XML: elements and attributes should not be lowercased.
            201100 =>
                [ 'xml', [ [ 'BR' ] ], '<BR />' ],
            [ 'xml', [ [ 'Link' ] ], '<Link />' ],
            [ 'xml', [ [ 'Node' ] ], '<Node />' ],
            [ 'xml', [ [ 'Node', [ 'STYLE' => true ] ] ], '<Node STYLE />' ],
            [ 'xml', [ [ 'Node', [ 'Value' => 123 ] ] ], '<Node Value="123" />' ],
            [ 'xml', [ [ 'Node', [ 'Value' => 'Value' ] ] ], '<Node Value="Value" />' ],
            [ 'xml', [ [ 'Node', [ 'VALUE' => '1', 'Value' => '2' ] ] ], '<Node VALUE="1" Value="2" />' ],

            // XML: DOCTYPE support.
            201200 =>
                [ 'xml', [ [ '!doctype' ] ], '<!doctype />' ],
            [ 'xml', [ [ '!DOCTYPE' ] ], '<!DOCTYPE />' ],
            [ 'xml', [ [ '!doctype', [ 'html' => true ] ] ], '<!doctype html />' ],
            [ 'xml', [ [ '!doctype', [ 'html' => true, 'public' => true ] ] ], '<!doctype html public />' ],
            [
                'xml',
                [
                    [
                        '!doctype',
                        [ 'html' => true, 'public' => true, '-//W3C//DTD HTML 4.01//EN' ]
                    ]
                ],
                '<!doctype html public "-//W3C//DTD HTML 4.01//EN" />',
            ],
            [
                'xml',
                [
                    [
                        '!doctype',
                        [ 'html' => true, 'public' => true, '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/html4/strict.dtd' ]
                    ]
                ],
                '<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" />'
            ],

            // XML: supports XML header - except if it have contents.
            201300 =>
                [ 'xml', [ [ '?xml', [ 'version' => '1.0' ] ] ], '<?xml version="1.0" ?>' ],
            [ 'xml', [ [ '?test', [ 'version' => '1.0' ] ] ], '<?test version="1.0" ?>' ],
            [ 'xml', [ [ '?xml', [ 'version' => '1.0' ], 'Hello' ] ], '<?xml version="1.0">Hello</?xml>' ],
            [ 'xml', [ [ '?xml', 'Hello' ] ], '<?xml>Hello</?xml>' ],

            // XML: more attributes definition to element.
            201400 =>
                [ 'xml', [ [ 'input', [ 'checked' => true ], [ 'readonly' => true ] ] ], '<input checked readonly />' ],
            [ 'xml', [ [ 'input', [ 'value' => '1' ], [ 'value' => '2' ] ] ], '<input value="2" />' ],
            [
                'xml',
                [ [ 'node', [ 'data-a' => '1' ], [ 'data-b' => '2' ], 'content' ] ],
                '<node data-a="1" data-b="2">content</node>'
            ],
            [ 'xml', [ [ 'input.test1', [ 'class' => 'test2' ] ] ], '<input.test1 class="test2" />' ],
            [ 'xml', [ [ 'input.test1', [ 'class' => 'test2' ], [ 'class' => 'test3' ] ] ], '<input.test1 class="test3" />' ],

            // HTML: empty array.
            201500 =>
                [ 'html', [], '' ],
            [ 'xml', [ [] ], '' ],
            [ 'xml', [ [ 'node', [] ] ], '<node />' ],
            [ 'xml', [ [ 'node', [], [] ] ], '<node />' ],
            [ 'xml', [ [ 'node', [], 'content' ] ], '<node>content</node>' ],
            [ 'xml', [ [ 'node', [], 'content', [] ] ], '<node>content</node>' ],
        ];
    }

    /**
     * Test attributes method.
     * @covers        \Unite\Axe\Axe::attributes
     * @dataProvider  dataAttributesMethod
     *
     * @param array  $attributes     Attributes.
     * @param string $expectedResult Expected result.
     */
    public function testAttributesMethod(array $attributes, string $expectedResult)
    {
        static::assertSame($expectedResult, Axe::attributes($attributes));
    }

    /**
     * Test basic methods.
     * @covers \Unite\Axe\Axe::html
     */
    public function testBasicHTML()
    {
        static::assertSame('hello', Axe::html('hello'));
        static::assertSame('1', Axe::html(true));
        static::assertSame('', Axe::html(false));
        static::assertSame('<div></div>', Axe::html([ 'div' ]));
        static::assertSame('<div></div>hello', Axe::html([ 'div' ], 'hello'));
        static::assertSame('<div></div><br />', Axe::html([ 'div' ], [ 'br' ]));
    }

    /**
     * Test basic methods.
     * @covers \Unite\Axe\Axe::xml
     */
    public function testBasicXML()
    {
        static::assertSame('hello', Axe::xml('hello'));
        static::assertSame('<div />', Axe::xml([ 'div' ]));
        static::assertSame('<div />hello', Axe::xml([ 'div' ], 'hello'));
        static::assertSame('<div /><br />', Axe::xml([ 'div' ], [ 'br' ]));
    }

    /**
     * Test transforms methods.
     * @covers        \Unite\Axe\Axe::html
     * @covers        \Unite\Axe\Axe::xml
     * @covers        \Unite\Axe\Axe::transform
     * @covers        \Unite\Axe\Axe::parseTag
     * @covers        \Unite\Axe\Axe::isAssociative
     *
     * @dataProvider  dataTransformsMethods
     *
     * @param string $method         Method of Axe to use.
     * @param array  $args           Method definition.
     * @param string $expectedResult Expected result.
     */
    public function testTransformsMethods(string $method, array $args, string $expectedResult)
    {
        static::assertSame($expectedResult, call_user_func_array([ Axe::class, $method ], $args));
    }
}
