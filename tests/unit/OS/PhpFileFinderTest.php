<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @covers Mihaeu\PhpDependencies\OS\PhpFileFinder
 */
class PhpFileFinderTest extends TestCase
{
    /** @var PhpFileFinder */
    private $finder;

    protected function setUp(): void
    {
        $this->finder = new PhpFileFinder();
    }

    public function testFindsSingleFileInFlatStructure(): void
    {
        $mockDir = vfsStream::setup('root', null, [
            'root' => [
                'someFile.php' => '<?php echo "Hello World";',
            ],
        ]);
        $dir = new SplFileInfo($mockDir->url());
        $expected = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/someFile.php')));
        assertEquals($expected, $this->finder->find($dir));
    }

    public function testFindsFilesInDeepStructure(): void
    {
        $mockDir = vfsStream::setup('root', null, [
            'root' => [
                'someFile.php' => '<?php echo "Hello World";',
                'dirA' => [
                    'dirB' => [
                        'dirC' => [
                            'fileInC.php' => '<?php echo "Hello World";',
                        ],
                        'fileInB.php' => '<?php echo "Hello World";',
                        'fileInB2.php' => '<?php echo "Hello World";',
                    ],
                    'fileInA.php' => '<?php echo "Hello World";',
                ],
            ],
        ]);
        $dir = new SplFileInfo($mockDir->url());
        $expected = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/someFile.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirA/fileInA.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirA/dirB/fileInB.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirA/dirB/fileInB2.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirA/dirB/dirC/fileInC.php')));
        assertEquals($expected, $this->finder->find($dir));
    }

    public function testFindsNothingIfThereIsNothing(): void
    {
        $mockDir = vfsStream::setup('root', null, [
            'root' => [
                'someFile.js' => 'console.log("Hello World!");',
                'dirA' => [
                    'dirB' => [
                        'dirC' => [
                            'fileInC.js' => '',
                        ],
                        'fileInB.js' => '',
                        'fileInB2.js' => '',
                    ],
                    'fileInA.js' => '',
                ],
            ],
        ]);
        $dir = new SplFileInfo($mockDir->url());
        assertEmpty($this->finder->find($dir));
    }

    public function testFindFilesInDeeplyNestedDirectory(): void
    {
        $mockDir = vfsStream::setup('root', null, [
            'root' => [
                'someFile.php' => 'console.log("Hello World!");',
                'dirA' => [
                    'fileInA.php' => '',
                ],
                'dirB' => [
                    'dirC' => [
                        'fileInC.php' => '',
                    ],
                    'fileInB.php' => '',
                    'fileInB2.php' => '',
                ],
            ],
        ]);
        $expected = (new PhpFileSet())
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirA/fileInA.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirB/fileInB.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirB/fileInB2.php')))
            ->add(new PhpFile(new SplFileInfo($mockDir->url().'/root/dirB/dirC/fileInC.php')));
        $actual = $this->finder->getAllPhpFilesFromSources([
            $mockDir->url().'/root/dirA',
            $mockDir->url().'/root/dirB',
        ]);
        assertEquals($expected, $actual);
    }
}
