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
    public function allDependencies() : DependencyCollection
    {
        return $this->reduce(new DependencyCollection(), function (DependencyCollection $clazzes, DependencyPair $dependency) {
            if (!$clazzes->contains($dependency->from())) {
                $clazzes = $clazzes->add($dependency->from());
            }
            if (!$clazzes->contains($dependency->to())) {
                $clazzes = $clazzes->add($dependency->to());
            }

            return $clazzes;
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

    public function filterByDepth(int $depth) : DependencyPairCollection
    {
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
