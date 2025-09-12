<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\ParserException;
use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticAnalyser::class)]
class StaticAnalyserTest extends TestCase
{
    private StaticAnalyser $analyser;

    private DependencyInspectionVisitor&MockObject $dependencyInspectionVisitor;

    private Parser&MockObject $parser;

    protected function setUp(): void
    {
        /** @var NodeTraverser&MockObject $nodeTraverser */
        $nodeTraverser = $this->createMock(NodeTraverser::class);
        $this->dependencyInspectionVisitor = $this->createMock(DependencyInspectionVisitor::class);
        $this->parser = $this->createMock(Parser::class);

        $this->analyser = new StaticAnalyser(
            $nodeTraverser,
            $this->dependencyInspectionVisitor,
            $this->parser
        );
    }

    public function testAnalyse(): void
    {
        $this->dependencyInspectionVisitor->method('dependencies')->willReturn(new DependencyMap());
        $phpFile = $this->createMock(PhpFile::class);
        $phpFile->method('code')->willReturn('');
        $dependencies = $this->analyser->analyse((new PhpFileSet())->add($phpFile));
        $this->assertEquals(new DependencyMap(), $dependencies);
    }

    public function testEnrichesExceptionWhenParserThrows(): void
    {
        $phpFile = $this->createMock(PhpFile::class);
        $phpFile->method('code')->willReturn('');
        $phpFile->method('file')->willReturn(new \SplFileInfo('test.php'));
        $this->parser->method('parse')
            ->willThrowException(new Error('', []));

        $this->expectException(ParserException::class);
        $this->analyser->analyse((new PhpFileSet())->add($phpFile));
    }
}
