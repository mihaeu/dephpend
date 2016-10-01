<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter
 */
class PlantUmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlantUmlFormatter */
    private $plantUmlFormatter;

    public function setUp()
    {
        $this->plantUmlFormatter = new PlantUmlFormatter();
    }

    public function testFormat()
    {
        $dependencyCollection = DependencyHelper::map('ClassA --> ClassB, ClassC');
        $this->assertEquals("@startuml\n"
            ."ClassA --|> ClassB\n"
            ."ClassA --|> ClassC\n"
            .'@enduml', $this->plantUmlFormatter->format($dependencyCollection));
    }
}
