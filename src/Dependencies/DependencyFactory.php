<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class DependencyFactory
{
    /**
     * @param array $parts
     *
     * @return Dependency
     */
    final public function createClazzFromStringArray(array $parts) : Dependency
    {
        try {
            $name = $this->extractClazzPart($parts);
            if (preg_match('/interface/i', $name)) {
                return $this->createInterfazeFromStringArray($parts);
            }
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
     * @param array $parts
     *
     * @return AbstractClazz
     */
    final public function createAbstractClazzFromStringArray(array $parts) : AbstractClazz
    {
        return new AbstractClazz(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array $parts
     *
     * @return Interfaze
     */
    final public function createInterfazeFromStringArray(array $parts) : Interfaze
    {
        return new Interfaze(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array $parts
     *
     * @return Trait_
     */
    final public function createTraitFromStringArray(array $parts) : Trait_
    {
        return new Trait_(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array $parts
     *
     * @return array
     */
    protected function extractNamespaceParts(array $parts)
    {
        return array_map(function (string $part) {
            return trim($part);
        }, array_slice($parts, 0, -1));
    }

    /**
     * @param array $parts
     *
     * @return mixed
     */
    protected function extractClazzPart(array $parts)
    {
        return trim(array_slice($parts, -1)[0]);
    }
}
