<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;

/**
 * @covers Mihaeu\PhpDependencies\DependencyInspectionVisitor
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClassDependencies
 */
class DependencyInspectionVisitorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor;

    /** @var ClassDependencies */
    private $classDependencies;

    public function setUp()
    {
        $this->classDependencies = new ClassDependencies(new Clazz('Test'));
        $this->dependencyInspectionVisitor = new DependencyInspectionVisitor($this->classDependencies);
    }

    public function testDetectsExplicitNewCreation()
    {
        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertEquals(new Clazz('TestDep'), $this->classDependencies->dependencies()[0]);
    }

    public function testDetectsImplicitNewCreation()
    {
        $node = new NewNode(new VariableNode('TestDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertEquals(new Clazz('TestDep'), $this->classDependencies->dependencies()[0]);
    }
}
