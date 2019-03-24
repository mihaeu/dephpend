<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class UmlTest extends TestCase
{
    private const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    private const SRC = __DIR__.'/../../src';

    public function testCreatesUml(): void
    {
        system('plantuml -version > /dev/null 2>&1', $returnStatus);
        if ($returnStatus !== 0) {
            $this->markTestSkipped('No PlantUML installation found');
            return;
        }

        $expected = <<<EOT
@startuml
namespace Mihaeu {
namespace PhpDependencies {
namespace OS {
}
namespace Dependencies {
}
namespace Exceptions {
}
namespace Formatters {
}
namespace Util {
}
}
}

Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Dependencies
Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Exceptions
Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Formatters
Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Util
@enduml
EOT;

        $tempFilePng = sys_get_temp_dir().'/dephpend-uml-test.png';
        $tempFileUml = sys_get_temp_dir().'/dephpend-uml-test.uml';
        shell_exec(self::DEPHPEND.' uml '.self::SRC.' --no-classes --keep-uml '
            .'--output="'.$tempFilePng.'" -f Mihaeu\\\\PhpDependencies\\\\OS');
        assertEquals(
            $expected,
            file_get_contents($tempFileUml)
        );

        if (@unlink($tempFilePng) === false
            && @unlink($tempFileUml) === false) {
            $this->fail('Uml diagram or uml artifact was not created.');
        }
    }
}
