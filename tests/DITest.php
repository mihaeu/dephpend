<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DI
 */
class DITest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesPhpFileFinder()
    {
        $this->assertInstanceOf(PhpFileFinder::class, (new DI())->phpFileFinder());
    }

    public function testCreatesParser()
    {
        $this->assertInstanceOf(Parser::class, (new DI())->parser());
    }

    public function testCreatesNormalAnalyser()
    {
        $this->assertInstanceOf(Analyser::class, (new DI())->analyser());
    }

    public function testCreatesUnderscoreAnalyser()
    {
        $this->assertInstanceOf(Analyser::class, (new DI())->analyser(true));
    }
}
