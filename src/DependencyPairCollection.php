<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyPairCollection extends AbstractCollection
{
    /**
     * @param DependencyPair $dependency
     *
     * @return DependencyPairCollection
     */
    public function add(DependencyPair $dependency) : DependencyPairCollection
    {
        $clone = clone $this;
        if ($dependency->from()->equals($dependency->to())
            || $this->contains($dependency)) {
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
        return $this->reduce(new self(), function (DependencyPairCollection $dependencies, DependencyPair $dependency) {
            return $dependencies->contains($dependency)
                ? $dependencies
                : $dependencies->add($dependency);
        });
    }

    /**
     * @return DependencyCollection
     */
    public function fromDependencies() : DependencyCollection
    {
        return $this->reduce(new DependencyCollection(), function (DependencyCollection $clazzes, DependencyPair $dependency) {
            return $clazzes->contains($dependency->from())
                ? $clazzes
                : $clazzes->add($dependency->from());
        });
    }

    /**
     * @return DependencyCollection
     */
    public function allDependencies() : DependencyCollection
    {
        return $this->reduce(new DependencyCollection(), function (DependencyCollection $dependencies, DependencyPair $dependency) {
            if (!$dependencies->contains($dependency->from())) {
                $dependencies = $dependencies->add($dependency->from());
            }

            if (!$dependencies->contains($dependency->to())) {
                $dependencies = $dependencies->add($dependency->to());
            }

            return $dependencies;
        });
    }

    /**
     * @return DependencyPairCollection
     */
    public function removeInternals() : DependencyPairCollection
    {
        return $this->filter(function (DependencyPair $dependencyPair) {
            return !in_array(
                $dependencyPair->to()->toString(),
                DependencyPairCollection::$internals, true
            );
        });
    }

    /**
     * @param string $namespace
     *
     * @return DependencyPairCollection
     */
    public function filterByNamespace(string $namespace) : DependencyPairCollection
    {
        $namespace = new Namespaze(array_filter(explode('\\', $namespace)));
        return $this->reduce(new DependencyPairCollection(), function (DependencyPairCollection $dependencies, DependencyPair $dependencyPair) use ($namespace) {
            if ($dependencyPair->from()->reduceToDepth($namespace->count())->equals($namespace)
                && $dependencyPair->to()->reduceToDepth($namespace->count())->equals($namespace)) {
                $dependencies = $dependencies->add(new DependencyPair(
                    $dependencyPair->from()->reduceDepthFromLeftBy($namespace->count()),
                    $dependencyPair->to()->reduceDepthFromLeftBy($namespace->count())
                ));
            }

            return $dependencies;
        });
    }

    public function filterByDepth(int $depth) : DependencyPairCollection
    {
        if ($depth === 0) {
            return clone $this;
        }

        return $this->reduce(new self(), function (DependencyPairCollection $dependencies, DependencyPair $dependencyPair) use ($depth) {
            return $dependencies->add(new DependencyPair(
                $dependencyPair->from()->reduceToDepth($depth),
                $dependencyPair->to()->reduceToDepth($depth))
            );
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
