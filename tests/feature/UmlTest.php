<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class UmlTest extends TestCase
{
    public function testCreatesUml(): void
    {
        system('plantuml -version > /dev/null 2>&1', $returnStatus);
        if ($returnStatus !== 0) {
            $this->markTestSkipped('No PlantUML installation found');
        }

        $expected = <<<EOT
@startuml
namespace Mihaeu {
namespace PhpDependencies {
namespace OS {
}
namespace Formatters {
}
namespace Dependencies {
}
namespace Exceptions {
}
namespace Util {
}
}
}

Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Formatters
Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Dependencies
Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Exceptions
Mihaeu.PhpDependencies.OS --|> Mihaeu.PhpDependencies.Util
@enduml
EOT;

        $tempFilePng = sys_get_temp_dir().'/dephpend-uml-test.png';
        $tempFileUml = sys_get_temp_dir().'/dephpend-uml-test.uml';
        shell_exec(sprintf('"%s" -n "%s" uml "%s" --no-classes --keep-uml --output="%s" -f Mihaeu\\\\PhpDependencies\\\\OS', PHP_BINARY, DEPHPEND_BIN, SRC_PATH, $tempFilePng));
        $this->assertEquals(
            $expected,
            file_get_contents($tempFileUml)
        );

        if (@unlink($tempFilePng) === false
            && @unlink($tempFileUml) === false) {
            $this->fail('Uml diagram or uml artifact was not created.');
        }
    }
}
