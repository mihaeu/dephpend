<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\Ast
 */
class AstTest extends \PHPUnit_Framework_TestCase
{
    public function testEach()
    {
        $ast = (new Ast())->add(new PhpFile(new \SplFileInfo(__DIR__)), [1]);
        $ast->each(function (array $nodes) {
            $this->assertEquals([1], $nodes);
        });
    }
}
