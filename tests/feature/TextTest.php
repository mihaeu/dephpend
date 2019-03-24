<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    private const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    private const SRC = __DIR__.'/../../src';

    public function testTextCommandOnDephpendSourceWithoutClassesAndWithRegexAndFromFilter(): void
    {
        assertEquals(
            'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\Dependencies'.PHP_EOL
            .'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\OS'.PHP_EOL,
            shell_exec(self::DEPHPEND.' text '.self::SRC
            .' --no-classes -f Mihaeu\\\\PhpDependencies\\\\Analyser -e "/Parser/"')
        );
    }

    public function testTextCommandOnPhpUnitWithUnderscoreNamespaces(): void
    {
        $expected = <<<EOT
PHPUnit\Runner --> PHPUnit\Framework
PHPUnit\Runner --> PHPUnit\Util\PHP
PHPUnit\Runner --> SebastianBergmann\Timer
PHPUnit\Runner --> Text
PHPUnit\Runner --> SebastianBergmann\FileIterator
PHPUnit\Runner --> PHPUnit\Util
PHPUnit\Runner --> SebastianBergmann
PHPUnit\Runner --> PHPUnit
PHPUnit\Runner\Filter --> PHPUnit\Framework
PHPUnit\Runner\Filter --> PHPUnit\Util

EOT;
        assertEquals(
            $expected,
            shell_exec(self::DEPHPEND.' text '.__DIR__.'/../../vendor/phpunit/phpunit/src'
            .' --underscore-namespaces --no-classes -f "PHPUnit\\\\Runner"')
        );
    }
}
