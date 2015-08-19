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
        $this->assertEquals("hello", Axe::html("hello"));
        $this->assertEquals("<div></div>", Axe::html([ "div" ]));
        $this->assertEquals("<div></div>hello", Axe::html([ "div" ], "hello"));
        $this->assertEquals("<div></div><br />", Axe::html([ "div" ], [ "br" ]));

        $this->assertEquals("hello", Axe::xml("hello"));
        $this->assertEquals("<div />", Axe::xml([ "div" ]));
        $this->assertEquals("<div />hello", Axe::xml([ "div" ], "hello"));
        $this->assertEquals("<div /><br />", Axe::xml([ "div" ], [ "br" ]));
    }

    /**
     * Test transforms methods.
     * @covers Rentalhost\VanillaAxe\Axe::html
     * @covers Rentalhost\VanillaAxe\Axe::xml
     * @covers Rentalhost\VanillaAxe\Axe::transform
     * @covers Rentalhost\VanillaAxe\Axe::normalizeOptions
     * @covers Rentalhost\VanillaAxe\Axe::parseTag
     * @covers Rentalhost\VanillaAxe\Axe::isAssociative
     * @dataProvider dataTransformsMethods
     */
    public function testTransformsMethods($method, $args, $expectedResult)
    {
        $this->assertEquals($expectedResult, call_user_func_array([ Axe::class, $method ], $args));
    }

    public function dataTransformsMethods()
    {
        return [
            // HTML: Literal String.
            100000 =>
            [ "html", [], null ],
            [ "html", [ "Hello World" ], 'Hello World' ],

            // HTML: Simple Tags.
            100100 =>
            [ "html", [ [] ], null ],
            [ "html", [ [ "" ] ], '<div></div>' ],
            [ "html", [ [ null ] ], '<div></div>' ],
            [ "html", [ [ "br" ] ], '<br />' ],
            [ "html", [ [ "div" ] ], '<div></div>' ],

            // HTML: Description Tags.
            100200 =>
            [ "html", [ [ "#hello" ] ], '<div id="hello"></div>' ],
            [ "html", [ [ ".hello" ] ], '<div class="hello"></div>' ],
            [ "html", [ [ "#he.llo.world" ] ], '<div id="he" class="llo world"></div>' ],

            // HTML: Attributes.
            100300 =>
            [ "html", [ [ "div", [ "id" => "hello" ] ] ], '<div id="hello"></div>' ],
            [ "html", [ [ "div#hello", [ "id" => "world" ] ] ], '<div id="world"></div>' ],

            // HTML: Content.
            100400 =>
            [ "html", [ [ "div", "hello" ] ], '<div>hello</div>' ],
            [ "html", [ [ "div#hello", "world" ] ], '<div id="hello">world</div>' ],
            [ "html", [ [ "div#hello", "world", [ "br" ] ] ], '<div id="hello">world<br /></div>' ],
            [ "html", [ [ "div#hello", "world", [ "br" ], [ "br" ] ] ], '<div id="hello">world<br /><br /></div>' ],

            // HTML: Force void to allow contents (why?).
            100500 =>
            [ "html", [ [ "br", "hello" ] ], '<br>hello</br>' ],

            // XML: Literal String.
            200000 =>
            [ "xml", [ "Hello World" ], 'Hello World' ],

            // XML: Simple Tags.
            200100 =>
            [ "xml", [ [] ], null ],
            [ "xml", [ [ "" ] ], '<node />' ],
            [ "xml", [ [ null ] ], '<node />' ],
            [ "xml", [ [ "br" ] ], '<br />' ],
            [ "xml", [ [ "div" ] ], '<div />' ],

            // XML: Description Tags.
            200200 =>
            [ "xml", [ [ "#hello" ] ], '<node id="hello" />' ],
            [ "xml", [ [ ".hello" ] ], '<node class="hello" />' ],
            [ "xml", [ [ "#he.llo.world" ] ], '<node id="he" class="llo world" />' ],

            // HTML: Attributes.
            200300 =>
            [ "xml", [ [ "div", [ "id" => "hello" ] ] ], '<div id="hello" />' ],
            [ "xml", [ [ "div#hello", [ "id" => "world" ] ] ], '<div id="world" />' ],

            // xml: Content.
            200400 =>
            [ "xml", [ [ "div", "hello" ] ], '<div>hello</div>' ],
            [ "xml", [ [ "div#hello", "world" ] ], '<div id="hello">world</div>' ],
            [ "xml", [ [ "div#hello", "world", [ "br" ] ] ], '<div id="hello">world<br /></div>' ],
        ];
    }
}
