<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractCollection;

class ClazzLikeSet extends AbstractCollection
{
    /**
     * @param ClazzLike $clazzLike
     *
     * @return ClazzLikeSet
     */
    public function add(ClazzLike $clazzLike) : ClazzLikeSet
    {
        $clone = clone $this;
        if ($this->contains($clazzLike)) {
            return $clone;
        }

        $clone->collection[] = $clazzLike;

        return $clone;
    }
}
