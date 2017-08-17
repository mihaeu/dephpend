<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class DsmTest extends BaseTest
{
    public function testCreatesSimpleDsmInHtml()
    {
        $this->assertRegExp(
            '/PhpDependencies.+X.+\d.+2.+1\d.+29/s',
            shell_exec(self::DEPHPEND.' dsm '.self::SRC.' --no-classes -d 2 --format=html')
        );
    }
}
