<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;

/**
 * @covers Mihaeu\PhpDependencies\PlantUmlWrapper
 * @covers Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClazzDependencies
 */
class PlantUmlWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ShellWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $shellWrapper;

    public function setUp()
    {
        $this->shellWrapper = $this->createMock(ShellWrapper::class);
    }

    public function testDetectsIfPlantUmlIsNotInstalled()
    {
        $this->shellWrapper->method('run')->willReturn(1);
        $this->expectException(PlantUmlNotInstalledException::class);
        new PlantUmlWrapper($this->shellWrapper);
    }

    public function testDetectsIfPlantUmlIsInstalled()
    {
        $this->shellWrapper->method('run')->willReturn(0);
        $this->assertInstanceOf(PlantUmlWrapper::class, new PlantUmlWrapper($this->shellWrapper));
    }

    public function testGenerate()
    {
        $simpleClassDependencies = new ClazzDependencies();
        $simpleClassDependencies->addDependency(new Clazz('SomeOtherClass'));
        $complexClassDependencies = new ClazzDependencies();
        $complexClassDependencies->addDependency(new Clazz('Class1'));
        $complexClassDependencies->addDependency(new Clazz('Class2'));
        $complexClassDependencies->addDependency(new Clazz('Class3'));
        $dependencies = [
            'GLOBAL' => $simpleClassDependencies,
            'SomeComplexClass' => $complexClassDependencies,
        ];
        $plantUml = new PlantUmlWrapper($this->shellWrapper);
        $plantUml->generate($dependencies);
        $this->assertEquals("@startuml\n"
            ."GLOBAL -down-|> SomeOtherClass\n"
            ."SomeComplexClass -up-|> Class1\n"
            ."SomeComplexClass -up-|> Class2\n"
            ."SomeComplexClass -up-|> Class3\n"
            .'@enduml', file_get_contents('dependencies.uml'));
    }
}
