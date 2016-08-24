<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyFactory
{
    /**
     * @param array $parts
     *
     * @return Clazz
     */
    public function createClazzFromStringArray(array $parts) : Clazz
    {
        return new Clazz(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array $parts
     *
     * @return AbstractClazz
     */
    public function createAbstractClazzFromStringArray(array $parts) : AbstractClazz
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
    public function createInterfazeFromStringArray(array $parts) : Interfaze
    {
        return new Interfaze(
            $this->extractClazzPart($parts),
            new Namespaze($this->extractNamespaceParts($parts))
        );
    }

    /**
     * @param array $parts
     *
     * @return array
     */
    private function extractNamespaceParts(array $parts)
    {
        return array_slice($parts, 0, -1);
    }

    /**
     * @param array $parts
     *
     * @return mixed
     */
    private function extractClazzPart(array $parts)
    {
        return array_slice($parts, -1)[0];
    }
}
