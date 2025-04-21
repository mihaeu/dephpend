<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use PhpParser\Parser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\DefaultParser
 */
class DefaultParserTest extends TestCase
{
    public function testPassesCodeToBaseParser(): void
    {
        /** @var Parser&MockObject $baseParser */
        $baseParser = $this->createMock(Parser::class);
        $baseParser->method('parse')->willReturn(['test']);
        $parser = new DefaultParser($baseParser);
        $this->assertEquals(['test'], $parser->parse(''));
    }
}
