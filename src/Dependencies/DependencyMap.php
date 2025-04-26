<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractMap;

/**
 * @extends AbstractMap<Dependency, Dependency>
 */
class DependencyMap extends AbstractMap
{
    /**
     * @param Dependency $from
     * @param Dependency $to
     */
    public function add($from, $to): self
    {
        $clone = clone $this;
        if ($from->equals($to)
            || $from->count() === 0
            || $to->count() === 0
            || \in_array($to->toString(), ['self', 'parent', 'static'])) {
            return $clone;
        }

        if (array_key_exists($from->toString(), $clone->map)) {
            $clone->map[$from->toString()][self::$VALUE] = $clone->map[$from->toString()][self::$VALUE]->add($to);
        } else {
            $clone->map[$from->toString()] = [
                self::$KEY      => $from,
                self::$VALUE    => (new DependencySet())->add($to),
            ];
        }
        return $clone;
    }

    public function addMap(self $other): self
    {
        return $this->reduce($other, function (DependencyMap $map, Dependency $from, Dependency $to) {
            return $map->add($from, $to);
        });
    }

    public function addSet(Dependency $from, DependencySet $toSet): self
    {
        $clone = $toSet->reduce($this, function (DependencyMap $map, Dependency $to) use ($from) {
            return $map->add($from, $to);
        });
        return $clone;
    }

    public function get(Dependency $from): DependencySet
    {
        return $this->map[$from->toString()][self::$VALUE] ?? new DependencySet();
    }

    public function fromDependencies(): DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from) {
            return $set->add($from);
        });
    }

    public function allDependencies(): DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) {
            return $set
                ->add($from)
                ->add($to);
        });
    }

    public function mapAllDependencies(\Closure $mappers): DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) use ($mappers) {
            return $set
                ->add($mappers($from))
                ->add($mappers($to));
        });
    }

    /**
     * This variant of reduce takes a \Closure which takes only a single Dependency
     * (as opposed to a pair of $to and $from) and applies it to both $to and $from.
     *
     * @param \Closure $mappers
     *
     * @return DependencyMap
     */
    public function reduceEachDependency(\Closure $mappers): DependencyMap
    {
        return $this->reduce(new self(), function (self $map, Dependency $from, Dependency $to) use ($mappers) {
            return $map->add($mappers($from), $mappers($to));
        });
    }

    public function toString(): string
    {
        return trim($this->reduce('', function (string $carry, Dependency $key, Dependency $value) {
            return $carry.$key->toString().' --> '.$value->toString().PHP_EOL;
        }));
    }
}
