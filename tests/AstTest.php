<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Ast
 *
 * @uses Mihaeu\PhpDependencies\PhpFile
 */
class AstTest extends \PHPUnit_Framework_TestCase
{
    public function testIterable()
    {
        $ast = new Ast();
        $ast->add(new PhpFile(new \SplFileInfo(__DIR__)), [1]);
        $ast->add(new PhpFile(new \SplFileInfo(__FILE__)), [2]);
        $array = iterator_to_array($ast);
        $this->assertEquals($ast->get($array[0]), [1]);
        $this->assertEquals($ast->get($array[1]), [2]);
    }

    public function testEach()
    {
        $ast = new Ast();
        $ast->add(new PhpFile(new \SplFileInfo(__DIR__)), [1]);
        $ast->each(function (PhpFile $file, array $nodes) {
            $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $file);
            $this->assertEquals([1], $nodes);
        });
    }

    public function testMapToArray()
    {
        $ast = new Ast();
        $ast->add(new PhpFile(new \SplFileInfo(__DIR__)), [1]);
        $ast->add(new PhpFile(new \SplFileInfo(__FILE__)), [2]);
        $result = $ast->mapToArray(function (PhpFile $file, array $nodes) {
            return $file;
        });
        $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $result[0]);
        $this->assertEquals(new PhpFile(new \SplFileInfo(__FILE__)), $result[1]);
    }
}
