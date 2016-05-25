<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;


class PhpFileCollection
{
    /** @var PhpFile[] */
    private $collection;

    public function add(PhpFile $file)
    {
        $collection[] = $file;
    }

    public function equals(PhpFileCollection $other)
    {
        return $this->collection === $other->collection;
    }
}
