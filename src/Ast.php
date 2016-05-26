<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

use PhpParser\Node;

class Ast
{
    /** @var \SplObjectStorage */
    private $nodes;

    /**
     * Ast constructor.
     */
    public function __construct()
    {
        $this->nodes = new \SplObjectStorage();
    }

    /**
     * @param PhpFile $file
     * @param Node[] $node
     */
    public function add(PhpFile $file, Array $node)
    {
        $this->nodes->attach($file, $node);
    }
}
