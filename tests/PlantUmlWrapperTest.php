<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;

/**
 * @covers Mihaeu\PhpDependencies\PlantUmlWrapper
 * @covers Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\DependencyCollection
 */
class PlantUmlWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ShellWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $shellWrapper;

    /** @var PlantUmlFormatter|\PHPUnit_Framework_MockObject_MockObject */
    private $plantUmlFormatter;

    public function setUp()
    {
        $this->shellWrapper = $this->createMock(ShellWrapper::class);
        $this->plantUmlFormatter = $this->createMock(PlantUmlFormatter::class);
    }

    public function testDetectsIfPlantUmlIsNotInstalled()
    {
        $this->shellWrapper->method('run')->willReturn(1);
        $this->expectException(PlantUmlNotInstalledException::class);
        new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
    }

    public function testDetectsIfPlantUmlIsInstalled()
    {
        $this->shellWrapper->method('run')->willReturn(0);
        $this->assertInstanceOf(PlantUmlWrapper::class, new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper));
    }

    public function testGenerate()
    {
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
        $plantUml->generate(new DependencyCollection(), new \SplFileInfo(sys_get_temp_dir().'/dependencies.png'), true);
        $this->assertFileExists(sys_get_temp_dir().'/dependencies.uml');
        unlink(sys_get_temp_dir().'/dependencies.uml');
    }

    public function testRemoveUml()
    {
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
        $plantUml->generate(new DependencyCollection(), new \SplFileInfo(sys_get_temp_dir().'/dependencies.png'));
        $this->assertFileNotExists(sys_get_temp_dir().'/dependencies.uml');
    }
}
