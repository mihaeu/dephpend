<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use PhpParser\Parser;

class CachedParser extends DefaultParser
{
    /** @var Cache */
    private $cache;

    public function __construct(Cache $cache, Parser $parser)
    {
        $this->cache = $cache;

        parent::__construct($parser);
    }

    public function parse(PhpFile $file): array
    {
        if (!$this->cache->has($file)) {
            $this->cache->set($file, ...parent::parse($file));
        }
        return $this->cache->get($file);
    }
}
