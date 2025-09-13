<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Formatters\MermaidFormatter;
use SplFileInfo;

class MermaidWrapper
{
    /** @var MermaidFormatter */
    private $mermaidFormatter;

    /**
     * @param MermaidFormatter $mermaidFormatter
     */
    public function __construct(MermaidFormatter $mermaidFormatter)
    {
        $this->mermaidFormatter = $mermaidFormatter;
    }

    public function generate(DependencyMap $map, SplFileInfo $destination): void
    {
        file_put_contents($destination->getPathname(), $this->mermaidFormatter->format($map));
    }
}
