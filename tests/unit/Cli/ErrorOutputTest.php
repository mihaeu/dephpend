<?php

declare(strict_types=1);

namespace Cli;

use Mihaeu\PhpDependencies\Cli\ErrorOutput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @covers \Mihaeu\PhpDependencies\Cli\ErrorOutput
 */
class ErrorOutputTest extends TestCase
{
    /** @var ErrorOutput */
    private $errorOutput;

    /** @var SymfonyStyle|MockObject */
    private $symfonyStyle;

    public function setUp(): void
    {
        $this->symfonyStyle = $this->createMock(SymfonyStyle::class);
        $this->errorOutput = new ErrorOutput(
            $this->symfonyStyle
        );
    }

    public function testWriteln(): void
    {
        $this->symfonyStyle->method('getErrorStyle')->willReturnSelf();
        $this->symfonyStyle->expects(self::once())->method('writeln')->with('test');
        $this->errorOutput->writeln('test');
    }
}
