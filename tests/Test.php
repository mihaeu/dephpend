<?php declare(strict_types = 1);

use PhpParser\ParserFactory;

class SomeTest extends PHPUnit_Framework_TestCase
{
    public function testProjectSetup()
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->assertNotNull($parser);
        $this->assertNotNull(new \mihaeu\phpDependencies\SomeBaseClass());
    }
}
