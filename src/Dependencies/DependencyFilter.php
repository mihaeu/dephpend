<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class DependencyFilter
{
    /** @var array */
    private $internals;

    /**
     * @param array $internals
     */
    public function __construct(array $internals)
    {
        $this->internals = $internals;
    }

    public function filterByOptions(DependencyMap $dependencies, array $options) : DependencyMap
    {
        if (!$options['internals']) {
            $dependencies = $this->removeInternals($dependencies);
        }

        if (isset($options['filter-from'])) {
            $dependencies = $this->filterByFromNamespace($dependencies, $options['filter-from']);
        }

        if ($options['depth'] > 0) {
            $dependencies = $this->filterByDepth($dependencies, (int) $options['depth']);
        }

        if ($options['filter-namespace']) {
            $dependencies = $this->filterByNamespace($dependencies, $options['filter-namespace']);
        }

        if (isset($options['no-classes']) && $options['no-classes'] === true) {
            $dependencies = $this->filterClasses($dependencies);
        }

        return $dependencies;
    }

    public function removeInternals(DependencyMap $dependencies) : DependencyMap
    {
        return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) {
            return !in_array($to->toString(), $this->internals, true)
                    ? $map->add($from, $to)
                    : $map;
        });
    }

    public function filterByNamespace(DependencyMap $dependencies, string $namespace) : DependencyMap
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return $dependencies->reduce(new DependencyMap(), $this->filterNamespaceFn($namespace));
    }

    public function filterByFromNamespace(DependencyMap $dependencies, string $namespace) : DependencyMap
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) use ($namespace) {
            return $from->inNamespaze($namespace)
                ? $map->add($from, $to)
                : $map;
        });
    }

    private function filterNamespaceFn(Namespaze $namespaze) : \Closure
    {
        return function (DependencyMap $map, Dependency $from, Dependency $to) use ($namespaze) : DependencyMap {
            return $from->inNamespaze($namespaze) && $to->inNamespaze($namespaze)
                ? $map->add($from, $to)
                : $map;
        };
    }

    public function filterByDepth(DependencyMap $dependencies, int $depth) : DependencyMap
    {
        if ($depth === 0) {
            return clone $dependencies;
        }

        return $dependencies->reduce(new DependencyMap(), function (DependencyMap $dependencies, Dependency $from, Dependency $to) use ($depth) {
            return $dependencies->add(
                $from->reduceToDepth($depth),
                $to->reduceToDepth($depth)
            );
        });
    }

    public function filterClasses(DependencyMap $dependencies) : DependencyMap
    {
        return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) {
            if ($from->namespaze()->count() === 0 || $to->namespaze()->count() === 0) {
                return $map;
            }
            return $map->add($from->namespaze(), $to->namespaze());
        });
    }
}
