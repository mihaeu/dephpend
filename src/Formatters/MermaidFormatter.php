<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Closure;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Util\Util;

class MermaidFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyMap $map, ?Closure $mappers = null): string
    {
        return 'classDiagram'.PHP_EOL
            .$this->mermaidNamespaceDefinitions($map).PHP_EOL
            .$this->dependenciesInMermaidFormat($map).PHP_EOL;
    }

    private function dependenciesInMermaidFormat(DependencyMap $map): string
    {
        return str_replace(
            ['-->', '\\'],
            ['--|>', '.'],
            $map->toString()
        );
    }

    private function mermaidNamespaceDefinitions(DependencyMap $map): string
    {
        return $this->printNamespaceMap(
            $this->buildNamespaceMap($map)
        );
    }

    /**
     * @return array<string, mixed>|array<string>
     */
    private function buildNamespaceMap(DependencyMap $map): array
    {
        return $map->reduce([], function (array $total, Dependency $from, Dependency $to) {
            if (!$from->isNamespaced()) {
                $total[] = $from->toString();
            } else {
                $fromNamespace = $from->namespaze()->toString();
                if (!array_key_exists($fromNamespace, $total)) {
                    $total[$fromNamespace] = [];
                }
                $total[$fromNamespace][] = $from->toString();
            }
            if (!$to->isNamespaced()) {
                $total[] = $to->toString();
                return $total;
            } else {
                $toNamespace = $to->namespaze()->toString();
                if (!array_key_exists($toNamespace, $total)) {
                    $total[$toNamespace] = [];
                }
                $total[$toNamespace][] = $to->toString();
            }
            return $total;
        });


    }

    /**
     * @param array<string, string> $buildNamespaceMap
     */
    private function printNamespaceMap(array $buildNamespaceMap, int $indent = 1): string
    {
        $seen = [];
        return Util::reduce($buildNamespaceMap, function (string $total, string|int $namespace, string|array $classes) use ($indent, &$seen): string {
            if (is_int($namespace) || is_string($classes)) {
                $total.=\str_repeat("\t", $indent).'class '.$classes.PHP_EOL;
                $seen[] = $classes;
                return $total;
            }
            $total.=\str_repeat("\t", $indent).'namespace '.\str_replace('\\', '.', $namespace).' {'.PHP_EOL;
            foreach ($classes as $class) {
                if (in_array($class, $seen)) {
                    continue;
                }
                $seen[] = $class;
                $total.=\str_repeat("\t", $indent + 1).'class '.\str_replace('\\', '.', $class).PHP_EOL;
            }
            $total.=\str_repeat("\t", $indent).'}'.PHP_EOL;
            return $total;
        }, '');
    }
}
