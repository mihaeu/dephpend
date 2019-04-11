<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\ParserException;
use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\StaticAnalyser
 */
class StaticAnalyserTest extends TestCase
{
    /** @var StaticAnalyser */
    private $analyser;

    /** @var DependencyInspectionVisitor|PHPUnit_Framework_MockObject_MockObject */
    private $dependencyInspectionVisitor;

    /** @var Parser */
    private $parser;

    protected function setUp(): void
    {
        /** @var NodeTraverser $nodeTraverser */
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
        assertEquals(new DependencyMap(), $dependencies);
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
