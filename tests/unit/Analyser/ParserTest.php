<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileCollection;
use PhpParser\Parser as BaseParser;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\Parser
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParsesCollection()
    {
        $mockFile = $this->createMock(PhpFile::class);
        $code = '<?php echo "Hello World!";';
        $mockFile->method('code')->willReturn($code);

        $mockSplFile = $this->createMock(\SplFileInfo::class);
        $mockSplFile->method('getBasename')->willReturn('HelloWorld');
        $mockFile->method('file')->willReturn($mockSplFile);

        $mockParser = $this->createMock(BaseParser::class);
        $mockParser->method('parse')->willReturn([]);
        $mockParser->expects($this->once())->method('parse')->with($code);

        $parser = new Parser($mockParser);
        $files = (new PhpFileCollection())->add($mockFile);
        $ast = $parser->parse($files);
//        $this->assertEquals([], $ast->get($mockFile));
    }
}
