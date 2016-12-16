<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class DsmTest extends \PHPUnit_Framework_TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    const SRC = __DIR__.'/../../src';

    public function testCreatesSimpleDsmInHtml()
    {
        $this->assertRegExp(
            '/PhpDependencies.+X.+\d.+1.+1\d.+29/s',
            shell_exec(self::DEPHPEND.' dsm '.self::SRC.' --no-classes -d 2 --format=html')
        );
    }
}
