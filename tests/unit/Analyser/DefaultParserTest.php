<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use PhpParser\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\DefaultParser
 */
class DefaultParserTest extends TestCase
{
    public function testPassesCodeToBaseParser(): void
    {
        $baseParser = $this->createMock(Parser::class);
        $baseParser->method('parse')->willReturn(['test']);
        $parser = new DefaultParser($baseParser);
        $this->assertEquals(['test'], $parser->parse(''));
    }
}
