<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    const DEPHPEND = PHP_BINARY.' -n -d extension=iconv.so -d extension=tokenizer.so -d extension=json.so '.__DIR__.'/../../bin/dephpend';
    const SRC = __DIR__.'/../../src';
}
