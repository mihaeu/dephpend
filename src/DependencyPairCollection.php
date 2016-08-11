<?php

declare (strict_types = 1);

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
        if (in_array($dependency, $this->collection)
            || $dependency->from()->equals($dependency->to())) {
            return $clone;
        }

        $clone->collection[] = $dependency;

        return $clone;
    }

    /**
     * @param Clazz $clazz
     *
     * @return ClazzCollection
     */
    public function findClassesDependingOn(Clazz $clazz) : ClazzCollection
    {
        return $this->filter(function (DependencyPair $dependency) use ($clazz) {
            return $dependency->from()->equals($clazz);
        })->reduce(new ClazzCollection(), function (ClazzCollection $clazzCollection, DependencyPair $dependency) {
            return $clazzCollection->add($dependency->to());
        });
    }

    /**
     * @return ClazzCollection
     */
    public function allClasses() : ClazzCollection
    {
        return $this->reduce(new ClazzCollection(), function (ClazzCollection $clazzes, DependencyPair $dependency) {
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
        return $this->filter(function (DependencyPair $dependency) {
            return !in_array($dependency->to()->toString(), DependencyPairCollection::$internals, true);
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
