<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Util\Util;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers Mihaeu\PhpDependencies\Cli\TestFeaturesCommand
 */
class TestFeaturesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testSmokeTest()
    {
        $command = new TestFeaturesCommand();
        $output = new BufferedOutput();
        $command->run(new ArrayInput([]), $output);

        // this just checks if every line starts with an [
        // this is not more than a smoke test, but should suffice for the
        // purpose of this command
        $this->assertFalse(Util::array_once(explode(PHP_EOL, trim($output->fetch())), function (string $line) {
            return mb_strpos($line, '[') !== 0;
        }));
    }
}
