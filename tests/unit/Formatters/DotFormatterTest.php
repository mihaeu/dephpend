<?php declare(strict_types=1);

namespace unit\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Formatters\DotFormatter;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\DotFormatter
 */
class DotFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatsSimpleDependencies()
    {
        $expected = 'digraph generated_by_dePHPend {'.PHP_EOL
            ."\tA -> B".PHP_EOL
            ."\tA -> D".PHP_EOL
            ."\tC -> D".PHP_EOL
            .'}'
        ;

        $this->assertEquals($expected, (new DotFormatter())->format(DependencyHelper::map('
            A --> B
            C --> D
            A --> D
        ')));
    }
}
