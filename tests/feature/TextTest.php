<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class TextTest extends \PHPUnit_Framework_TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    const SRC = __DIR__.'/../../src';

    public function testTextCommandOnDephpendSourceWithoutClassesAndWithRegexAndFromFilter()
    {
        $this->assertEquals(
            'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\Dependencies'.PHP_EOL
            .'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\OS'.PHP_EOL
            .'Mihaeu\PhpDependencies\Analyser --> Mihaeu\PhpDependencies\Util'.PHP_EOL,
            shell_exec(self::DEPHPEND.' text '.self::SRC
                .' --no-classes -f Mihaeu\\\\PhpDependencies\\\\Analyser -e "/Parser/"'));
    }

    public function testTextCommandOnPhpUnitWithUnderscoreNamespaces()
    {
        $this->assertEquals(
            'PHPUnit\Runner --> PHPUnit\Util'.PHP_EOL
            .'PHPUnit\Runner --> PHPUnit\Framework'.PHP_EOL
            .'PHPUnit\Runner --> File\Iterator'.PHP_EOL
            .'PHPUnit\Runner --> SebastianBergmann'.PHP_EOL
            .'PHPUnit\Runner --> PHPUnit'.PHP_EOL
            .'PHPUnit\Runner\Filter --> PHPUnit\Framework'.PHP_EOL
            .'PHPUnit\Runner\Filter --> PHPUnit\Util'.PHP_EOL
            .'PHPUnit\Runner\Filter\Group --> PHPUnit\Runner\Filter'.PHP_EOL,
            shell_exec(self::DEPHPEND.' text '.__DIR__.'/../../vendor/phpunit/phpunit/src'
                .' --underscore-namespaces --no-classes -f "PHPUnit\\\\Runner"'));
    }
}
