<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter
 */
class PlantUmlFormatterTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals("@startuml\n\n"
            ."ClassA --|> ClassB\n"
            ."ClassA --|> ClassC\n"
            .'@enduml', $this->plantUmlFormatter->format($dependencyCollection));
    }

    public function testFormatsNestedNamespaces()
    {
        $this->assertEquals('@startuml
namespace A {
namespace b {
}
}
namespace B {
namespace a {
}
namespace b {
}
}

A.b.C1 --|> A.b.C2
B.a.C1 --|> B.b.C2
@enduml', $this->plantUmlFormatter->format(DependencyHelper::map('
            A\\b\\C1 --> A\\b\\C2
            B\\a\\C1 --> B\\b\\C2
        ')));
    }
}
