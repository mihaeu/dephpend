<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use PhpParser\Parser;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\DefaultParser
 */
class DefaultParserTest extends \PHPUnit\Framework\TestCase
{
    public function testPassesCodeToBaseParser()
    {
        $baseParser = $this->createMock(Parser::class);
        $baseParser->method('parse')->willReturn(['test']);
        $parser = new DefaultParser($baseParser);
        $this->assertEquals(['test'], $parser->parse(''));
    }
}
