<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use function array_map;
use function array_slice;
use function trim;

class DependencyFactory
{
    /**
     * @param array<string> $parts
     */
    final public function createClazzFromStringArray(array $parts): Clazz|NullDependency
    {
        try {
            $clazz = new Clazz(
                $this->extractClazzPart($parts),
                new Namespaze($this->extractNamespaceParts($parts))
            );
        } catch (\InvalidArgumentException $exception) {
            $clazz = new NullDependency();
        }
        return $clazz;
    }

    /**
     * @param array<string> $parts
     *
     * @return AbstractClazz
     */
    final public function createAbstractClazzFromStringArray(array $parts): AbstractClazz
    {
        return new AbstractClazz(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array<string> $parts
     *
     * @return Interfaze
     */
    final public function createInterfazeFromStringArray(array $parts): Interfaze
    {
        return new Interfaze(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array<string> $parts
     *
     * @return Trait_
     */
    final public function createTraitFromStringArray(array $parts): Trait_
    {
        return new Trait_(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array<string> $parts
     *
     * @return array<string>
     */
    protected function extractNamespaceParts(array $parts): array
    {
        return array_map(function (string $part) {
            return trim($part);
        }, array_slice($parts, 0, -1));
    }

    /**
     * @param array<string> $parts
     */
    protected function extractClazzPart(array $parts): string
    {
        return trim(array_slice($parts, -1)[0]);
    }
}
