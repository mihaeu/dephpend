<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Formatters\DotFormatter::class)]
class DotFormatterTest extends TestCase
{
    public function testFormatsSimpleDependencies(): void
    {
        $expected = 'digraph generated_by_dePHPend {'.PHP_EOL
            ."\t\"A\" -> \"B\"".PHP_EOL
            ."\t\"C\" -> \"D\"".PHP_EOL
            ."\t\"A.b\" -> \"D.c\"".PHP_EOL
            .'}'
        ;

        $this->assertEquals($expected, (new DotFormatter())->format(DependencyHelper::map('
            A --> B
            C --> D
            A\\b --> D\\c
        ')));
    }
}
