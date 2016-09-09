<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use PhpParser\Parser as BaseParser;

class Parser
{
    /** @var BaseParser */
    private $parser;

    /**
     * @param $parser
     */
    public function __construct(BaseParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param PhpFileSet $files
     *
     * @return Ast
     */
    public function parse(PhpFileSet $files) : Ast
    {
        return $files->reduce(new Ast(), function (Ast $ast, PhpFile $file) {
            return $ast->add($file, $this->parser->parse($file->code()));
        });
    }
}
