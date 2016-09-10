<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Util\AbstractCollection;

class PhpFileSet extends AbstractCollection
{
    public function add(PhpFile $file) : PhpFileSet
    {
        $clone = clone $this;
        if ($this->contains($file)) {
            return $clone;
        }

        $clone->collection[] = $file;
        return $clone;
    }

    public function addAll(PhpFileSet $otherCollection) : PhpFileSet
    {
        $clone = clone $this;
        $clone->collection = array_reduce($clone->collection, function (array $carry, PhpFile $file) {
            if (!in_array($file, $carry)) {
                $carry[] = $file;
            }
            return $carry;
        }, $otherCollection->collection);

        return $clone;
    }

    public function contains($other) : bool
    {
        return $this->any(function (PhpFile $file) use ($other) {
            return $file->equals($other);
        });
    }
}
