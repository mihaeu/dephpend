<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter
 */
class PlantUmlFormatterTest extends TestCase
{
    /** @var PlantUmlFormatter */
    private $plantUmlFormatter;

    protected function setUp(): void
    {
        $this->plantUmlFormatter = new PlantUmlFormatter();
    }

    public function testFormat(): void
    {
        $dependencyCollection = DependencyHelper::map('ClassA --> ClassB, ClassC');
        $this->assertEquals("@startuml\n\n"
            ."ClassA --|> ClassB\n"
            ."ClassA --|> ClassC\n"
            .'@enduml', $this->plantUmlFormatter->format($dependencyCollection));
    }

    public function testFormatsNestedNamespaces(): void
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
