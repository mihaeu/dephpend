<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractMap;

class DependencyMap extends AbstractMap
{
    /**
     * @param Dependency $fromDependency
     * @param Dependency $toDependency
     *
     * @return DependencyMap
     */
    public function add(Dependency $fromDependency, Dependency $toDependency) : self
    {
        $clone = clone $this;
        if ($fromDependency->equals($toDependency)) {
            return $clone;
        }

        if (array_key_exists($fromDependency->toString(), $clone->map)) {
            $clone->map[$fromDependency->toString()][self::$VALUE] = $clone->map[$fromDependency->toString()][self::$VALUE]->add($toDependency);
        } else {
            $clone->map[$fromDependency->toString()] = [
                self::$KEY      => $fromDependency,
                self::$VALUE    => (new DependencySet())->add($toDependency),
            ];
        }
        return $clone;
    }

    public function addSet(Dependency $fromDependency, DependencySet $toDependencies) : self
    {
        if ($toDependencies->count() === 0) {
            return $this->add($fromDependency, new NullDependency());
        }
        return $toDependencies->reduce(clone $this, function (self $map, Dependency $dependency) use ($fromDependency) {
            return $map->add($fromDependency, $dependency);
        });
    }

    /**
     * @return DependencySet
     */
    public function fromDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, DependencySet $toDependencies, Dependency $fromDependency) {
            return $set->add($fromDependency);
        });
    }

    /**
     * @return DependencySet
     */
    public function allDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $dependencies, DependencySet $toDependencies, Dependency $fromDependency) {
            return $dependencies
                ->add($fromDependency)
                ->addAll($toDependencies);
        });
    }

    /**
     * @return DependencyMap
     */
    public function removeInternals() : self
    {
        return $this->reduce(new self(), function (self $map, DependencySet $toDependencies, Dependency $fromDependency) {
            return $map->addSet($fromDependency, $toDependencies->filter(function (Dependency $dependency) {
                return !in_array($dependency->toString(), self::$internals, true);
            }));
        });
    }

    /**
     * @param string $namespace
     *
     * @return DependencyMap
     */
    public function filterByNamespace(string $namespace) : self
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return $this->reduce(new self(), function (self $map, DependencySet $toDependencies, Dependency $fromDependency) use ($namespace) {
            if ($fromDependency->inNamespaze($namespace)) {
                $reducedFrom = $fromDependency->reduceDepthFromLeftBy($namespace->count());
                $reducedTo = $toDependencies->reduce(new DependencySet(), function (DependencySet $set, Dependency $dependency) use ($namespace) {
                    if ($dependency->inNamespaze($namespace)) {
                        return $set->add($dependency->reduceDepthFromLeftBy($namespace->count()));
                    }
                    return $set;
                });
                return $map->addSet($reducedFrom, $reducedTo);
            }
            return $map;
        });
    }

    public function filterByDepth(int $depth) : self
    {
        if ($depth === 0) {
            return clone $this;
        }

        return $this->reduce(new self(), function (self $dependencies, DependencySet $toDependencies, Dependency $fromDependency) use ($depth) {
            return $dependencies->addSet(
                $fromDependency->reduceToDepth($depth),
                $toDependencies->reduceToDepth($depth)
            );
        });
    }

    /**
     * @return DependencyMap
     */
    public function filterClasses() : self
    {
        return $this->reduce(new self(), function (self $dependencies, DependencySet $toDependencies, Dependency $fromDependency) {
            $to = $toDependencies->reduce(new DependencySet(), function (DependencySet $set, Dependency $dependency) {
                return $dependency->namespaze()->count()
                    ? $set->add($dependency->namespaze())
                    : $set;
            });

            return $dependencies->addSet($fromDependency->namespaze(), $to);
        });
    }

    /**
     * @inheritDoc
     */
    public function toString() : string
    {
        return trim($this->reduce('', function (string $carry, DependencySet $value, Dependency $key) {
            return $value->reduce($carry, function (string $carry, Dependency $value) use ($key) {
                return $carry.$key.' --> '.$value.PHP_EOL;
            });
        }));
    }


    private static $internals = [

        // classes
        'stdClass',
        'Exception',
        'ErrorException',
        'Error',
        'ParseError',
        'TypeError',
        'ArithmeticError',
        'DivisionByZeroError',
        'Closure',
        'Generator',
        'ClosedGeneratorException',
        'DateTime',
        'DateTimeImmutable',
        'DateTimeZone',
        'DateInterval',
        'DatePeriod',
        'LibXMLError',
        'SQLite3',
        'SQLite3Stmt',
        'SQLite3Result',
        'CURLFile',
        'DOMException',
        'DOMStringList',
        'DOMNameList',
        'DOMImplementationList',
        'DOMImplementationSource',
        'DOMImplementation',
        'DOMNode',
        'DOMNameSpaceNode',
        'DOMDocumentFragment',
        'DOMDocument',
        'DOMNodeList',
        'DOMNamedNodeMap',
        'DOMCharacterData',
        'DOMAttr',
        'DOMElement',
        'DOMText',
        'DOMComment',
        'DOMTypeinfo',
        'DOMUserDataHandler',
        'DOMDomError',
        'DOMErrorHandler',
        'DOMLocator',
        'DOMConfiguration',
        'DOMCdataSection',
        'DOMDocumentType',
        'DOMNotation',
        'DOMEntity',
        'DOMEntityReference',
        'DOMProcessingInstruction',
        'DOMStringExtend',
        'DOMXPath',
        'finfo',
        'LogicException',
        'BadFunctionCallException',
        'BadMethodCallException',
        'DomainException',
        'InvalidArgumentException',
        'LengthException',
        'OutOfRangeException',
        'RuntimeException',
        'OutOfBoundsException',
        'OverflowException',
        'RangeException',
        'UnderflowException',
        'UnexpectedValueException',
        'RecursiveIteratorIterator',
        'IteratorIterator',
        'FilterIterator',
        'RecursiveFilterIterator',
        'CallbackFilterIterator',
        'RecursiveCallbackFilterIterator',
        'ParentIterator',
        'LimitIterator',
        'CachingIterator',
        'RecursiveCachingIterator',
        'NoRewindIterator',
        'AppendIterator',
        'InfiniteIterator',
        'RegexIterator',
        'RecursiveRegexIterator',
        'EmptyIterator',
        'RecursiveTreeIterator',
        'ArrayObject',
        'ArrayIterator',
        'RecursiveArrayIterator',
        'SplFileInfo',
        'DirectoryIterator',
        'FilesystemIterator',
        'RecursiveDirectoryIterator',
        'GlobIterator',
        'SplFileObject',
        'SplTempFileObject',
        'SplDoublyLinkedList',
        'SplQueue',
        'SplStack',
        'SplHeap',
        'SplMinHeap',
        'SplMaxHeap',
        'SplPriorityQueue',
        'SplFixedArray',
        'SplObjectStorage',
        'MultipleIterator',
        'SessionHandler',
        'PDOException',
        'PDO',
        'PDOStatement',
        'PDORow',
        '__PHP_Incomplete_Class',
        'php_user_filter',
        'Directory',
        'AssertionError',
        'ReflectionException',
        'Reflection',
        'ReflectionFunctionAbstract',
        'ReflectionFunction',
        'ReflectionGenerator',
        'ReflectionParameter',
        'ReflectionType',
        'ReflectionMethod',
        'ReflectionClass',
        'ReflectionObject',
        'ReflectionProperty',
        'ReflectionExtension',
        'ReflectionZendExtension',
        'PharException',
        'Phar',
        'PharData',
        'PharFileInfo',
        'SimpleXMLElement',
        'SimpleXMLIterator',
        'XMLReader',
        'XMLWriter',
        'ZipArchive',

        // interfaces
        'Traversable',
        'IteratorAggregate',
        'Iterator',
        'ArrayAccess',
        'Serializable',
        'Throwable',
        'DateTimeInterface',
        'RecursiveIterator',
        'OuterIterator',
        'Countable',
        'SeekableIterator',
        'SplObserver',
        'SplSubject',
        'JsonSerializable',
        'SessionHandlerInterface',
        'SessionIdInterface',
        'SessionUpdateTimestampHandlerInterface',
        'Reflector',
    ];
}
