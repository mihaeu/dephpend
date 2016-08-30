<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class UnderscoreDependencyFactory extends DependencyFactory
{
    /**
     * @inheritDoc
     */
    protected function extractClazzPart(array $parts)
    {
        return parent::extractClazzPart(
            $this->underscorePartsToNamespacedParts($parts)
        );
    }

    /**
     * @inheritDoc
     */
    protected function extractNamespaceParts(array $parts)
    {
        return parent::extractNamespaceParts(
            $this->underscorePartsToNamespacedParts($parts)
        );
    }

    /**
     * @param string[] $parts
     *
     * @return string[]
     */
    private function underscorePartsToNamespacedParts(array $parts)
    {
        $newParts = [];
        foreach ($parts as $underscorePart) {
            foreach (explode('_', $underscorePart) as $part) {
                $newParts[] = $part;
            }
        }
        return $newParts;
    }
}
