<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\Util\AbstractCollection;
use PhpParser\Node;

class Ast extends AbstractCollection
{
    /**
     * @param PhpFile $file
     * @param Node[]  $node
     *
     * @return Ast
     */
    public function add(PhpFile $file, array $node) : self
    {
        $this->collection[$file->file()->getRealPath()] = $node;
        return $this;
    }
}
