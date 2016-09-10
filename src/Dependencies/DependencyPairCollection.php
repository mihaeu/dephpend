<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractCollection;
use Mihaeu\PhpDependencies\Util\Collection;

class DependencyPairCollection extends AbstractCollection
{
    /**
     * @param DependencyPair $dependency
     *
     * @return self
     */
    public function add(DependencyPair $dependency) : self
    {
        $clone = clone $this;
        if ($this->contains($dependency)) {
            return $clone;
        }

        $clone->collection[] = $dependency;

        return $clone;
    }

    /**
     * @return Collection
     */
    public function unique() : Collection
    {
        return $this->reduce(new self(), function (self $dependencies, DependencyPair $dependency) {
            return $dependencies->contains($dependency)
                ? $dependencies
                : $dependencies->add($dependency);
        });
    }

    /**
     * @return DependencySet
     */
    public function fromDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $clazzes, DependencyPair $dependency) {
            return $clazzes->contains($dependency->from())
                ? $clazzes
                : $clazzes->add($dependency->from());
        });
    }

    /**
     * @return DependencySet
     */
    public function allDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $dependencies, DependencyPair $dependency) {
            return $dependencies
                ->add($dependency->from())
                ->addAll($dependency->to());
        });
    }

    /**
     * @return self
     */
    public function removeInternals() : self
    {
        return $this->reduce(new self(), function (self $collection, DependencyPair $dependencyPair) {
            return $collection->add(new DependencyPair($dependencyPair->from(), $dependencyPair->to()->filter(function (Dependency $dependency) {
                return !in_array($dependency->toString(), self::$internals, true);
            })));
        });
    }

    /**
     * @param string $namespace
     *
     * @return self
     */
    public function filterByNamespace(string $namespace) : self
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return $this->reduce(new self(), function (self $collection, DependencyPair $dependencyPair) use ($namespace) {
            if ($dependencyPair->from()->inNamespaze($namespace)) {
                $reducedFrom = $dependencyPair->from()->reduceDepthFromLeftBy($namespace->count());
                $reducedTo = $dependencyPair->to()->reduce(new DependencySet(), function (DependencySet $set, Dependency $dependency) use ($namespace) {
                    if ($dependency->inNamespaze($namespace)) {
                        return $set->add($dependency->reduceDepthFromLeftBy($namespace->count()));
                    }
                    return $set;
                });
                return $collection->add(new DependencyPair($reducedFrom, $reducedTo));
            }
            return $collection;
        });
    }

    public function filterByDepth(int $depth) : self
    {
        if ($depth === 0) {
            return clone $this;
        }

        return $this->reduce(new self(), function (self $dependencies, DependencyPair $dependencyPair) use ($depth) {
            return $dependencies->add(new DependencyPair(
                $dependencyPair->from()->reduceToDepth($depth),
                $dependencyPair->to()->reduceToDepth($depth))
            );
        });
    }

    /**
     * @return self
     */
    public function filterClasses() : self
    {
        return $this->reduce(new self(), function (self $dependencies, DependencyPair $dependencyPair) {
            $to = $dependencyPair->to()->reduce(new DependencySet(), function (DependencySet $set, Dependency $dependency) {
                return $dependency->namespaze()->count()
                    ? $set->add($dependency->namespaze())
                    : $set;
            });

            return $dependencies->add(new DependencyPair($dependencyPair->from()->namespaze(), $to));
        });
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
