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

    private function selectedFilters(array $options) : array
    {
        $filters = [];
        if (!$options['internals']) {
            $filters[] = $this->removeInternals();
        }

        if (isset($options['filter-from'])) {
            $filters[] = $this->filterByFromNamespace($options['filter-from']);
        }

        if ($options['depth'] > 0) {
            $filters[] = $this->filterByDepth((int) $options['depth']);
        }

        if ($options['filter-namespace']) {
            $filters[] = $this->filterByNamespace($options['filter-namespace']);
        }

        if (isset($options['no-classes']) && $options['no-classes'] === true) {
            $filters[] = $this->filterClasses();
        }

        return $filters;
    }

    public function filterByOptions(DependencyMap $dependencies, array $options) : DependencyMap
    {
        return array_reduce($this->selectedFilters($options), function (DependencyMap $dependencies, \Closure $filter) {
            return $filter($dependencies);
        }, $dependencies);
    }

    public function removeInternals() : \Closure
    {
        return function (DependencyMap $dependencies) : DependencyMap {
            return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) {
                return !in_array($to->toString(), $this->internals, true)
                    ? $map->add($from, $to)
                    : $map;
            });
        };
    }

    public function filterByNamespace(string $namespace) : \Closure
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return function (DependencyMap $dependencies) use ($namespace) : DependencyMap {
            return $dependencies->reduce(new DependencyMap(), $this->filterNamespaceFn($namespace));
        };
    }

    public function filterByFromNamespace(string $namespace) : \Closure
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return function (DependencyMap $dependencies) use ($namespace) : DependencyMap {
            return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) use ($namespace) {
                return $from->inNamespaze($namespace)
                    ? $map->add($from, $to)
                    : $map;
            });
        };
    }

    private function filterNamespaceFn(Namespaze $namespaze) : \Closure
    {
        return function (DependencyMap $map, Dependency $from, Dependency $to) use ($namespaze) : DependencyMap {
            return $from->inNamespaze($namespaze) && $to->inNamespaze($namespaze)
                ? $map->add($from, $to)
                : $map;
        };
    }

    public function filterByDepth(int $depth) : \Closure
    {
        return function (DependencyMap $dependencies) use ($depth) : DependencyMap {
            if ($depth === 0) {
                return clone $dependencies;
            }

            return $dependencies->reduce(new DependencyMap(), function (DependencyMap $dependencies, Dependency $from, Dependency $to) use ($depth) {
                return $dependencies->add(
                    $from->reduceToDepth($depth),
                    $to->reduceToDepth($depth)
                );
            });
        };
    }

    public function filterClasses() : \Closure
    {
        return function (DependencyMap $dependencies) : DependencyMap {
            return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) {
                if ($from->namespaze()->count() === 0 || $to->namespaze()->count() === 0) {
                    return $map;
                }
                return $map->add($from->namespaze(), $to->namespaze());
            });
        };
    }
}
