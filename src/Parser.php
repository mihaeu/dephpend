<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Parser as BaseParser;

class Parser
{
    /** @var BaseParser */
    private $parser;

    /**
     * Parser constructor.
     *
     * @param $parser
     */
    public function __construct(BaseParser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(PhpFileCollection $files) : Ast
    {
        $ast = new Ast();
        $files->each(function (PhpFile $file) use ($ast) {
            $node = $this->parser->parse($file->code());
            $ast->add($file, $node);
        });

        return $ast;
    }
}
