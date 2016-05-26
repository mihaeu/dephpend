<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

use org\bovigo\vfs\vfsStream;

/**
 * @covers mihaeu\phpDependencies\FileFinder
 *
 * @uses mihaeu\phpDependencies\PhpFile
 * @uses mihaeu\phpDependencies\PhpFileCollection
 */
class FileFinderTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileFinder */
    private $finder;

    public function setUp()
    {
        $this->finder = new FileFinder();
    }

    public function testFindsSingleFileInFlatStructure()
    {
        $mockDir = vfsStream::setup('root', null, [
            'root' => [
                'someFile.php' => '<?php echo "Hello World";'
            ]
        ]);
        $dir = new \SplFileInfo($mockDir->url());
        $expected = new PhpFileCollection();
        $expected->add(new PhpFile(new \SplFileInfo($mockDir->url() . '/someFile.php')));
        $this->assertEquals($expected, $this->finder->find($dir));
    }

    public function testFindsASingleFileInDeepStructure()
    {
        $this->markTestSkipped();
    }

    public function testFindsNothingIfThereIsNothing()
    {
        $this->markTestSkipped();
    }
}
