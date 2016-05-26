<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

use PhpParser\ParserFactory;
use PhpParser\Parser as BaseParser;

class Parser
{
    /** @var BaseParser */
    private $parser;

    /**
     * Parser constructor.
     * @param $parser
     */
    public function __construct(BaseParser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(PhpFileCollection $files) : Ast
    {
        $ast = new Ast;
        $files->each(function (PhpFile $file) use ($ast) {
            $ast->add($file, $this->parser->parse($file->code()));
        });
        return $ast;
    }
}
