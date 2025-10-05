<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(\Mihaeu\PhpDependencies\OS\PhpFileSet::class)]
#[CoversClass(\Mihaeu\PhpDependencies\Util\AbstractCollection::class)]
class PhpFileSetTest extends TestCase
{
    public function testEquals(): void
    {
        $file = new PhpFile(new SplFileInfo(sys_get_temp_dir()));
        $collection1 = (new PhpFileSet())
            ->add($file);
        $collection2 = (new PhpFileSet())
            ->add($file);
        $this->assertTrue($collection1->equals($collection2));
    }

    public function testDoesNotAllowDuplicated(): void
    {
        $set = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__DIR__)));
        $this->assertCount(1, $set);
    }

    public function testIsImmutable(): void
    {
        $set = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)));
        $setAfterRefusingDuplicate = $set->add(new PhpFile(new SplFileInfo(__DIR__)));
        $this->assertNotSame($set, $setAfterRefusingDuplicate);
    }

    public function testNotEquals(): void
    {
        $collection1 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(sys_get_temp_dir())));
        $collection2 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)));
        $this->assertFalse($collection1->equals($collection2));
    }

    public function testCount0WhenEmpty(): void
    {
        $collection1 = new PhpFileSet();
        $this->assertCount(0, $collection1);
    }

    public function testCount(): void
    {
        $this->assertCount(2, (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__DIR__.'/../../../composer.json'))));
    }

    public function testEach(): void
    {
        $collection1 = (new PhpFileSet())->add(new PhpFile(new SplFileInfo(__DIR__)));
        $collection1->each(function (PhpFile $file) {
            $this->assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $file);
        });
    }

    public function testMapToArray(): void
    {
        $collection1 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__FILE__)))
            ->toArray();
        $this->assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $collection1[0]);
        $this->assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $collection1[1]);
    }

    public function testAddAll(): void
    {
        $collection1 = new PhpFileSet();
        $collection2 = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo(__DIR__)))
            ->add(new PhpFile(new SplFileInfo(__FILE__)));
        $combinedCollection = $collection1->addAll($collection2)->toArray();
        $this->assertCount(2, $combinedCollection);
        $this->assertEquals(new PhpFile(new SplFileInfo(__DIR__)), $combinedCollection[0]);
        $this->assertEquals(new PhpFile(new SplFileInfo(__FILE__)), $combinedCollection[1]);
    }
}
