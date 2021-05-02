<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SplFileInfo;

/**
 * @covers Mihaeu\PhpDependencies\OS\PlantUmlWrapper
 * @covers Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException
 */
class PlantUmlWrapperTest extends TestCase
{
    /** @var ShellWrapper|PHPUnit_Framework_MockObject_MockObject */
    private $shellWrapper;

    /** @var PlantUmlFormatter|PHPUnit_Framework_MockObject_MockObject */
    private $plantUmlFormatter;

    protected function setUp(): void
    {
        $this->shellWrapper = $this->createMock(ShellWrapper::class);
        $this->plantUmlFormatter = $this->createMock(PlantUmlFormatter::class);
    }

    public function testDetectsIfPlantUmlIsNotInstalled(): void
    {
        $this->shellWrapper->method('run')->willReturn(1);
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);

        $this->expectException(PlantUmlNotInstalledException::class);
        $plantUml->generate(new DependencyMap(), new SplFileInfo(__FILE__));
    }

    public function testDetectsIfPlantUmlIsInstalled(): void
    {
        $this->shellWrapper->method('run')->willReturn(0);
        assertInstanceOf(PlantUmlWrapper::class, new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper));
    }

    public function testGenerate(): void
    {
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
        $plantUml->generate(new DependencyMap(), new SplFileInfo(sys_get_temp_dir().'/dependencies.png'), true);
        assertFileExists(sys_get_temp_dir().'/dependencies.uml');
        unlink(sys_get_temp_dir().'/dependencies.uml');
    }

    public function testRemoveUml(): void
    {
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
        $plantUml->generate(new DependencyMap(), new SplFileInfo(sys_get_temp_dir().'/dependencies.png'));
        assertFileNotExists(sys_get_temp_dir().'/dependencies.uml');
    }
}
