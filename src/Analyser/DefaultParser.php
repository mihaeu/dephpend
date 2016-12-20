<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use PhpParser\Parser as BaseParser;

class DefaultParser implements Parser
{
    /** @var BaseParser */
    private $baseParser;

    /**
     * @param BaseParser $baseParser
     */
    public function __construct(BaseParser $baseParser)
    {
        $this->baseParser = $baseParser;
    }

    public function parse(PhpFile $file): array
    {
        return $this->baseParser->parse($file->code());
    }
}
