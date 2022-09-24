<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testTextCommandOnDephpendSourceWithoutClassesAndWithRegexAndFromFilter(): void
    {
        $this->assertEquals(
            'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\Dependencies'.PHP_EOL
            .'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\OS'.PHP_EOL,
            shell_exec(DEPHPEND_BIN.' text '.SRC_PATH
            .' --no-classes --filter-from=Mihaeu\\\\PhpDependencies\\\\Analyser --exclude-regex="/Parser/"')
        );
    }
}
