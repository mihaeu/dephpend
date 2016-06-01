<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Parser as BaseParser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParsesCollection()
    {
        $mockFile = $this->getMockBuilder(PhpFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $code = '<?php echo "Hello World!";';
        $mockFile->method('code')->willReturn($code);

        $mockSplFile = $this->getMockBuilder(\SplFileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockSplFile->method('getBasename')->willReturn('HelloWorld');
        $mockFile->method('file')->willReturn($mockSplFile);

        $mockParser = $this->getMockBuilder(BaseParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockParser->method('parse')->willReturn([]);
        $mockParser->expects($this->once())->method('parse')->with($code);

        $parser = new Parser($mockParser);
        $files = new PhpFileCollection($mockFile);
        $ast = $parser->parse($files);
        $this->assertNotNull($ast);
    }
}
