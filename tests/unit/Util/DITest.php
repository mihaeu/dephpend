<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\UnderscoreDependencyFactory;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;

/**
 * @covers Mihaeu\PhpDependencies\Util\DI
 */
class DITest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesDependencyFilter()
    {
        $this->assertInstanceOf(DependencyFilter::class, (new DI([]))->dependencyFilter());
    }
    
    public function testCreatesPhpFileFinder()
    {
        $this->assertInstanceOf(PhpFileFinder::class, (new DI([]))->phpFileFinder());
    }

    public function testCreatesParser()
    {
        $this->assertInstanceOf(Parser::class, (new DI([]))->parser());
    }

    public function testCreatesAnalyser()
    {
        $this->assertInstanceOf(Analyser::class, (new DI([]))->analyser());
    }

    public function testCreatesDefaultDependencyFactory()
    {
        $this->assertInstanceOf(DependencyFactory::class, (new DI([]))->dependencyFactory());
    }

    public function testCreatesUnderscoreDependencyFactory()
    {
        $this->assertInstanceOf(UnderscoreDependencyFactory::class, (new DI([]))->dependencyFactory(true));
    }
}
