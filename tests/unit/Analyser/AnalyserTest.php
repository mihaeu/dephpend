<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use PhpParser\NodeTraverser;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\Analyser
 */
class AnalyserTest extends \PHPUnit_Framework_TestCase
{
    /** @var Analyser */
    private $analyser;

    /** @var DependencyInspectionVisitor|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyInspectionVisitor;

    public function setUp()
    {
        /** @var NodeTraverser $nodeTraverser */
        $nodeTraverser = $this->createMock(NodeTraverser::class);
        $this->dependencyInspectionVisitor = $this->createMock(DependencyInspectionVisitor::class);

        $this->analyser = new Analyser($nodeTraverser, $this->dependencyInspectionVisitor);
    }

    public function testAnalyse()
    {
        $this->dependencyInspectionVisitor->method('dependencies')->willReturn(new DependencyMap());
        $dependencies = $this->analyser->analyse(new Ast());
        $this->assertEquals(new DependencyMap(), $dependencies);
    }
}
