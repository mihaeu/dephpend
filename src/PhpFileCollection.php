<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

class PhpFileCollection
{
    /** @var PhpFile[] */
    private $collection = [];

    public function add(PhpFile $file)
    {
        $this->collection[] = $file;
    }

    public function get(int $i) : PhpFile
    {
        if (!array_key_exists($i, $this->collection)) {
            throw new IndexOutOfBoundsException();
        }
        return $this->collection[$i];
    }

    public function equals(PhpFileCollection $other) : bool
    {
        return $this->collection === $other->collection;
    }
}
