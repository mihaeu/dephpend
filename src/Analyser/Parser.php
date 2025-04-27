<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use PhpParser\Node;

interface Parser
{
    /**
     * @return list<Node>
     */
    public function parse(string $code): array;
}
