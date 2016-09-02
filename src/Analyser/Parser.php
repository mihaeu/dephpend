<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileCollection;
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
     * @param PhpFileCollection $files
     *
     * @return Ast
     */
    public function parse(PhpFileCollection $files) : Ast
    {
        $ast = new Ast();
        $files->each(function (PhpFile $file) use ($ast) {
            $node = $this->test($file->code());
            $ast->add($file, $node);
        });

        return $ast;
    }

    private function test(string $bla)
    {
        return $this->parser->parse($bla);
    }
}
