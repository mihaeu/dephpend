<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use org\bovigo\vfs\vfsStream;

/**
 * @covers Mihaeu\PhpDependencies\PhpFileFinder
 *
 * @uses Mihaeu\PhpDependencies\PhpFile
 * @uses Mihaeu\PhpDependencies\PhpFileCollection
 */
class PhpFileFinderTest extends \PHPUnit_Framework_TestCase
{
    /** @var PhpFileFinder */
    private $finder;

    public function setUp()
    {
        $this->finder = new PhpFileFinder();
    }

    public function testFindsSingleFileInFlatStructure()
    {
        $mockDir = vfsStream::setup('root', null, [
            'root' => [
                'someFile.php' => '<?php echo "Hello World";',
            ],
        ]);
        $dir = new \SplFileInfo($mockDir->url());
        $expected = new PhpFileCollection();
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url().'/someFile.php')));
        $this->assertEquals($expected, $this->finder->find($dir));
    }

    public function testFindsFilesInDeepStructure()
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
        $dir = new \SplFileInfo($mockDir->url());
        $expected = new PhpFileCollection();
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url().'/someFile.php')));
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url().'/fileInA.php')));
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url().'/fileInB.php')));
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url().'/fileInB2.php')));
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url().'/fileInC.php')));
        $this->assertEquals($expected, $this->finder->find($dir));
    }

    public function testFindsNothingIfThereIsNothing()
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
        $dir = new \SplFileInfo($mockDir->url());
        $this->assertEmpty($this->finder->find($dir));
    }
}
