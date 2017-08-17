<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\AbstractClazz;
use Mihaeu\PhpDependencies\Dependencies\ClazzLike;
use Mihaeu\PhpDependencies\Dependencies\ClazzLikeSet;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\Interfaze;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;
use Mihaeu\PhpDependencies\Dependencies\Trait_;
use Mihaeu\PhpDependencies\Util\Util;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyMap $map, \Closure $mappers = null) : string
    {
        return '@startuml'.PHP_EOL
            .$this->plantUmlNamespaceDefinitions($map).PHP_EOL
            .$this->plantUmlClassDefinitions($map).PHP_EOL
            .$this->dependenciesInPlantUmlFormat($map).PHP_EOL
            .'@enduml';
    }

    private function dependenciesInPlantUmlFormat(DependencyMap $map) : string
    {
        return str_replace(
            ['-->', '\\'],
            ['--|>', '.'],
            $map->toString()
        );
    }

    private function plantUmlClassDefinitions(DependencyMap $map) : string
    {
        /* @var ClazzLikeSet $clazzLikes */
        $clazzLikes = $map->reduce(new ClazzLikeSet(), function (ClazzLikeSet $set, Dependency $from, Dependency $to) {
            if ($from instanceof ClazzLike) {
                $set = $set->add($from);
            }
            if ($to instanceof ClazzLike) {
                $set = $set->add($to);
            }

            return $set;
        });

        return $this->printClassDefinitions($clazzLikes);
    }

    private function printClassDefinitions(ClazzLikeSet $clazzLikes) : string
    {
        return $clazzLikes->reduce('', function (string $carry, ClazzLike $item) {
            $type = 'class';

            if ($item instanceof Interfaze) {
                $type = 'interface';
            } elseif ($item instanceof AbstractClazz || $item instanceof Trait_) {
                $type = 'abstract class';
            }

            $className = str_replace('\\', '.', $item->toString());

            return $carry . $type . ' ' . $className . PHP_EOL;
        });
    }

    private function plantUmlNamespaceDefinitions(DependencyMap $map) : string
    {
        $namespaces = $map->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) {
            return $set
                ->add($from->namespaze())
                ->add($to->namespaze());
        });
        return $this->printNamespaceTree(
            $this->buildNamespaceTree($namespaces)
        );
    }

    private function buildNamespaceTree(DependencySet $namespaces) : array
    {
        return $namespaces->reduce([], function (array $total, Namespaze $namespaze) {
            $currentLevel = &$total;
            foreach ($namespaze->parts() as $part) {
                if (!array_key_exists($part, $currentLevel)) {
                    $currentLevel[$part] = [];
                }
                $currentLevel = &$currentLevel[$part];
            }
            return $total;
        });
    }

    private function printNamespaceTree(array $buildNamespaceTree) : string
    {
        return Util::reduce($buildNamespaceTree, function (string $total, string $namespace, array $children) : string {
            return  $total.'namespace '.$namespace.' {'.PHP_EOL.$this->printNamespaceTree($children).'}'.PHP_EOL;
        }, '');
    }
}
