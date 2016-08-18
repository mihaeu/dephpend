<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\PlantUmlFormatter
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
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('ClassA'), new Clazz('ClassB')))
            ->add(new DependencyPair(new Clazz('ClassA'), new Clazz('ClassC')));
        $this->assertEquals("@startuml\n"
            ."ClassA --|> ClassB\n"
            ."ClassA --|> ClassC\n"
            .'@enduml', $this->plantUmlFormatter->format($dependencyCollection));
    }
}
