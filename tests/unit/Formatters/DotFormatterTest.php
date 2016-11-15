<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\DotFormatter
 */
class DotFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatsSimpleDependencies()
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
