<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\NodeTraverser;

/**
 * @covers Mihaeu\PhpDependencies\Analyser
 *
 * @uses Mihaeu\PhpDependencies\Ast
 * @uses Mihaeu\PhpDependencies\ClazzDependencies
 * @uses Mihaeu\PhpDependencies\DependencyInspectionVisitor
 * @uses Mihaeu\PhpDependencies\PhpFile
 * @uses Mihaeu\PhpDependencies\Clazz
 */
class AnalyserTest extends \PHPUnit_Framework_TestCase
{
    /** @var Analyser */
    private $analyser;

    public function setUp()
    {
        $this->analyser = new Analyser($this->createMock(NodeTraverser::class));
    }

    public function testAnalyse()
    {
        $ast = new Ast();
        $ast->add(new PhpFile(new \SplFileInfo(__FILE__)), [1]);
        $ast->add(new PhpFile(new \SplFileInfo(__DIR__)), [1]);
        $dependencies = $this->analyser->analyse($ast);
        $this->assertCount(2, $dependencies);
    }
}
