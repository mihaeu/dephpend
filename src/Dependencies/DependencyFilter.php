<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\Functional;

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
        if (isset($options['underscore-namespaces']) && $options['underscore-namespaces'] === true) {
            $dependencies = $this->mapNamespaces($dependencies);
        }

        if (!$options['internals']) {
            $dependencies = $this->removeInternals($dependencies);
        }

        if (isset($options['filter-from'])) {
            $dependencies = $this->filterByFromNamespace($dependencies, $options['filter-from']);
        }

        if ($options['filter-namespace']) {
            $dependencies = $this->filterByNamespace($dependencies, $options['filter-namespace']);
        }

        if (isset($options['exclude-regex'])) {
            $this->ensureRegexIsValid($options['exclude-regex']);
            $dependencies = $this->excludeByRegex($dependencies, $options['exclude-regex']);
        }

        return $dependencies;
    }

    public function postFiltersByOptions(array $options) : \Closure
    {
        $filters = [];
        if ($options['depth'] > 0) {
            $filters[] = $this->reduceDependencyByDepth((int) $options['depth']);
        }

        if (isset($options['no-classes']) && $options['no-classes'] === true) {
            $filters[] = $this->reduceDependencyToNamespace();
        }
        return Functional::compose(...$filters);
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

    public function excludeByRegex(DependencyMap $dependencies, string $regex) : DependencyMap
    {
        return $dependencies->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) use ($regex) {
            return preg_match($regex, $from->toString()) === 1 || preg_match($regex, $to->toString()) === 1
                ? $map
                : $map->add($from, $to);
        });
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

    public function reduceDependencyToNamespace() : \Closure
    {
        return function (Dependency $dependency) : Dependency {
            return $dependency->namespaze();
        };
    }

    public function reduceDependencyByDepth(int $depth) : \Closure
    {
        return function (Dependency $dependency) use ($depth) : Dependency {
            return $dependency->reduceToDepth($depth);
        };
    }

    public function mapNamespaces(DependencyMap $dependencies) : DependencyMap
    {
        return $dependencies->reduceEachDependency(function (Dependency $dependency) {
            if (strpos($dependency->toString(), '_') === false) {
                return $dependency;
            }
            if ($dependency instanceof Namespaze) {
                return new Namespaze(explode('_', $dependency->toString()));
            }
            $parts = explode('_', $dependency->toString());
            return new Clazz($parts[count($parts) - 1], new Namespaze(array_slice($parts, 0, -1)));
        });
    }

    private function ensureRegexIsValid(string $regex)
    {
        if (@preg_match($regex, '') === false) {
            throw new \InvalidArgumentException(
                'Regular expression ('.$regex.') is not valid.'
            );
        }
    }
}
