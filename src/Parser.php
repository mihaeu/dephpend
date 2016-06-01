<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
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

    public function parse(PhpFileCollection $files) : array
    {
        return $files->mapToArray(function (PhpFile $file) {
            $parsedCode = $this->parser->parse($file->code());

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver);
            $forClazz = new Clazz($file->file()->getBasename());
            $dependencies = new ClassDependencies($forClazz);
            $traverser->addVisitor(new DependencyInspectionVisitor($dependencies));
            $traverser->traverse($parsedCode);

            return $dependencies->dependencies();
        });
    }
}
