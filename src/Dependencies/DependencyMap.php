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

    public function addSet(Dependency $from, DependencySet $toSet) : self
    {
        $clone = clone $this;
        if (!array_key_exists($from->toString(), $this->map)) {
            $clone->map[$from->toString()] = [
                self::$KEY      => $from,
                self::$VALUE    => $toSet,
            ];
            return $clone;
        }

        $clone->map[$from->toString()][self::$VALUE] = $clone->get($from)->addAll($toSet);
        return $clone;
    }

    public function get(Dependency $from) : DependencySet
    {
        return $this->map[$from->toString()][self::$VALUE];
    }

    /**
     * @return DependencySet
     */
    public function fromDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) {
            return $set->add($from);
        });
    }

    /**
     * @return DependencySet
     */
    public function allDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $dependencies, Dependency $from, Dependency $to) {
            return $dependencies
                ->add($from)
                ->add($to);
        });
    }

    /**
     * @return DependencyMap
     */
    public function removeInternals() : self
    {
        return $this->reduce(new self(), function (self $map, Dependency $from, Dependency $to) {
            return !in_array($to->toString(), self::$internals, true)
                ? $map->add($from, $to)
                : $map;
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
        return $this->reduce(new self(), function (self $map, Dependency $from, Dependency $to) use ($namespace) {
            return $from->inNamespaze($namespace) && $to->inNamespaze($namespace)
                ? $map->add($from->reduceDepthFromLeftBy($namespace->count()), $to->reduceDepthFromLeftBy($namespace->count()))
                : $map;
        });
    }

    public function filterByDepth(int $depth) : self
    {
        if ($depth === 0) {
            return clone $this;
        }

        return $this->reduce(new self(), function (self $dependencies, Dependency $from, Dependency $to) use ($depth) {
            return $dependencies->add(
                $from->reduceToDepth($depth),
                $to->reduceToDepth($depth)
            );
        });
    }

    /**
     * @return DependencyMap
     */
    public function filterClasses() : self
    {
        return $this->reduce(new self(), function (self $map, Dependency $from, Dependency $to) {
            if ($from->namespaze()->count() === 0 || $to->namespaze()->count() === 0) {
                return $map;
            }
            return $map->add($from->namespaze(), $to->namespaze());
        });
    }

    /**
     * @inheritDoc
     */
    public function toString() : string
    {
        return trim($this->reduce('', function (string $carry, Dependency $key, Dependency $value) {
            return $value instanceof NullDependency
                ? $carry
                : $carry.$key->toString().' --> '.$value->toString().PHP_EOL;
        })
        );
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
