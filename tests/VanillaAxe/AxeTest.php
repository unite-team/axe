<?php

namespace Rentalhost\VanillaAxe;

use PHPUnit_Framework_TestCase;

class AxeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test basic methods.
     * @covers Rentalhost\VanillaAxe\Axe::html
     * @covers Rentalhost\VanillaAxe\Axe::xml
     * @return string
     */
    public function testBasic()
    {
        static::assertEquals('hello', Axe::html('hello'));
        static::assertEquals('<div></div>', Axe::html([ 'div' ]));
        static::assertEquals('<div></div>hello', Axe::html([ 'div' ], 'hello'));
        static::assertEquals('<div></div><br />', Axe::html([ 'div' ], [ 'br' ]));

        static::assertEquals('hello', Axe::xml('hello'));
        static::assertEquals('<div />', Axe::xml([ 'div' ]));
        static::assertEquals('<div />hello', Axe::xml([ 'div' ], 'hello'));
        static::assertEquals('<div /><br />', Axe::xml([ 'div' ], [ 'br' ]));
    }

    /**
     * Test transforms methods.
     * @covers       Rentalhost\VanillaAxe\Axe::html
     * @covers       Rentalhost\VanillaAxe\Axe::xml
     * @covers       Rentalhost\VanillaAxe\Axe::transform
     * @covers       Rentalhost\VanillaAxe\Axe::normalizeOptions
     * @covers       Rentalhost\VanillaAxe\Axe::parseTag
     * @covers       Rentalhost\VanillaAxe\Axe::isAssociative
     * @dataProvider dataTransformsMethods
     *
     * @param string $method         Method of Axe to use.
     * @param array  $args           Method definition.
     * @param string $expectedResult Expected result.
     */
    public function testTransformsMethods($method, $args, $expectedResult)
    {
        static::assertEquals($expectedResult, call_user_func_array([ Axe::class, $method ], $args));
    }

    public function dataTransformsMethods()
    {
        return [
            // HTML: Literal String.
            100000 =>
                [ 'html', [ ], null ],
            [ 'html', [ 'Hello World' ], 'Hello World' ],
            // HTML: Simple Tags.
            100100 =>
                [ 'html', [ [ ] ], null ],
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
                    '<div><span id="id" class="class"></span><div></div></div>'
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
            // XML: Literal String.
            200000 =>
                [ 'xml', [ 'Hello World' ], 'Hello World' ],
            // XML: Simple Tags.
            200100 =>
                [ 'xml', [ [ ] ], null ],
            [ 'xml', [ [ '' ] ], '<node />' ],
            [ 'xml', [ [ null ] ], '<node />' ],
            [ 'xml', [ [ 'br' ] ], '<br />' ],
            [ 'xml', [ [ 'div' ] ], '<div />' ],
            // XML: Description Tags.
            200200 =>
                [ 'xml', [ [ '#hello' ] ], '<node id="hello" />' ],
            [ 'xml', [ [ '.hello' ] ], '<node class="hello" />' ],
            [ 'xml', [ [ '#he.llo.world' ] ], '<node id="he" class="llo world" />' ],
            // HTML: Attributes.
            200300 =>
                [ 'xml', [ [ 'div', [ 'id' => 'hello' ] ] ], '<div id="hello" />' ],
            [ 'xml', [ [ 'div#hello', [ 'id' => 'world' ] ] ], '<div id="world" />' ],
            // xml: Content.
            200400 =>
                [ 'xml', [ [ 'div', 'hello' ] ], '<div>hello</div>' ],
            [ 'xml', [ [ 'div#hello', 'world' ] ], '<div id="hello">world</div>' ],
            [ 'xml', [ [ 'div#hello', 'world', [ 'br' ] ] ], '<div id="hello">world<br /></div>' ],
            // XML: Avoid reference copy.
            200600 =>
                [
                    'xml',
                    [ [ 'div', [ 'span#id.class' ], [ null ] ] ],
                    '<div><span id="id" class="class" /><node /></div>'
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
        ];
    }
}
