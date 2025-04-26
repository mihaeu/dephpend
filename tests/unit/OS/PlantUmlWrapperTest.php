<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers Mihaeu\PhpDependencies\OS\PlantUmlWrapper
 * @covers Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException
 */
class PlantUmlWrapperTest extends TestCase
{
    /** @var ShellWrapper&MockObject */
    private $shellWrapper;

    /** @var PlantUmlFormatter&MockObject */
    private $plantUmlFormatter;

    protected function setUp(): void
    {
        $this->shellWrapper = $this->createMock(ShellWrapper::class);
        $this->plantUmlFormatter = $this->createMock(PlantUmlFormatter::class);
    }

    public static function plantUmlInstallationProvider(): array
    {
        return [
            'PlantUML is installed' => ['shellReturnCode' => 0, 'expectException' => false],
            'PlantUML is not installed' => ['shellReturnCode' => 1, 'expectException' => true],
        ];
    }

    #[DataProvider('plantUmlInstallationProvider')]
    public function testInstallationCheckDuringGeneration(int $shellReturnCode, bool $expectException): void
    {
        $this->shellWrapper->method('run')
             ->willReturn($shellReturnCode);

        $wrapper = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);

        if ($expectException) {
            $this->expectException(PlantUmlNotInstalledException::class);
        }

        $wrapper->generate(new DependencyMap(), new SplFileInfo('test.png'));
        if ($expectException) {
            $this->fail('PlantUmlNotInstalledException was expected but not thrown.');
        }

        $this->expectNotToPerformAssertions();
    }

    public function testGenerate(): void
    {
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
        $plantUml->generate(new DependencyMap(), new SplFileInfo(sys_get_temp_dir().'/dependencies.png'), true);
        $this->assertFileExists(sys_get_temp_dir().'/dependencies.uml');
        unlink(sys_get_temp_dir().'/dependencies.uml');
    }

    public function testRemoveUml(): void
    {
        $plantUml = new PlantUmlWrapper($this->plantUmlFormatter, $this->shellWrapper);
        $plantUml->generate(new DependencyMap(), new SplFileInfo(sys_get_temp_dir().'/dependencies.png'));
        $this->assertFileDoesNotExist(sys_get_temp_dir().'/dependencies.uml');
    }
}
