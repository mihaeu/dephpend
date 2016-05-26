<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

/**
 * @covers mihaeu\phpDependencies\Ast
 *
 * @uses mihaeu\phpDependencies\PhpFile
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
}
