<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Closure;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;
use Mihaeu\PhpDependencies\Util\Util;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyMap $map, ?Closure $mappers = null): string
    {
        return '@startuml'.PHP_EOL
            .$this->plantUmlNamespaceDefinitions($map).PHP_EOL
            .$this->dependenciesInPlantUmlFormat($map).PHP_EOL
            .'@enduml';
    }

    private function dependenciesInPlantUmlFormat(DependencyMap $map): string
    {
        return str_replace(
            ['-->', '\\'],
            ['--|>', '.'],
            $map->toString()
        );
    }

    private function plantUmlNamespaceDefinitions(DependencyMap $map): string
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

    /**
     * @return array<string, array<string, array<string, array<string>>>>
     */
    private function buildNamespaceTree(DependencySet $namespaces): array
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

    /**
     * @param array<string, array<string, array<string, array<string>>>> $buildNamespaceTree
     */
    private function printNamespaceTree(array $buildNamespaceTree): string
    {
        return Util::reduce($buildNamespaceTree, function (string $total, string $namespace, array $children): string {
            return  $total.'namespace '.$namespace.' {'.PHP_EOL.$this->printNamespaceTree($children).'}'.PHP_EOL;
        }, '');
    }
}
