<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @covers Mihaeu\PhpDependencies\OS\PhpFileSet
 * @covers Mihaeu\PhpDependencies\Util\AbstractCollection
 */
class PhpFileSetTest extends TestCase
{
    public function testEquals(): void
    {
        $file = new PhpFile(new SplFileInfo(sys_get_temp_dir()));
        $collection1 = (new PhpFileSet())
            ->add($file);
        $collection2 = (new PhpFileSet())
            ->add($file);
        assertTrue($collection1->equals($collection2));
    }

    public function testDoesNotAllowDuplicated(): void
    {
        $set = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__DIR__)));
        assertCount(1, $set);
    }

    public function testIsImmutable(): void
    {
        $set = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)));
        $setAfterRefusingDuplicate = $set->add(new PhpFile(new SplFileInfo(__DIR__)));
        assertNotSame($set, $setAfterRefusingDuplicate);
    }

    public function testNotEquals(): void
    {
        $collection1 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(sys_get_temp_dir())));
        $collection2 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)));
        assertFalse($collection1->equals($collection2));
    }

    public function testCount0WhenEmpty(): void
    {
        $collection1 = new PhpFileSet();
        assertCount(0, $collection1);
    }

    public function testCount(): void
    {
        assertCount(2, (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__DIR__.'/../../../composer.json'))));
    }

    public function testEach(): void
    {
        $collection1 = (new PhpFileSet())->add(new PhpFile(new SplFileInfo(__DIR__)));
        $collection1->each(function (PhpFile $file) {
            assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $file);
        });
    }

    public function testMapToArray(): void
    {
        $collection1 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__FILE__)))
            ->toArray();
        assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $collection1[0]);
        assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $collection1[1]);
    }

    public function testAddAll(): void
    {
        $collection1 = new PhpFileSet();
        $collection2 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__FILE__)));
        $combinedCollection = $collection1->addAll($collection2)->toArray();
        assertCount(2, $combinedCollection);
        assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $combinedCollection[0]);
        assertEquals(new PhpFile(new SplFileInfo(__FILE__)), $combinedCollection[1]);
    }
}
