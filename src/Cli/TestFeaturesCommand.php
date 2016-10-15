<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Util\DI;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

// @codeCoverageIgnoreStart
class TestFeaturesCommand extends Command
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('test-features');
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Test support for dependency detection')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->fetchAllFeatureTests();
        $results = $this->runAllTests($files);

        usort($results, $this->sortSuccessFirst());
        foreach ($results as $result) {
            $output->writeln($result[1]);
        }
    }

    public function runTest(string $filename) : array
    {
        $_SERVER['argv'] = [0, 'text', $filename];
        $application = new Application('', '', new DI([]));
        $application->setAutoExit(false);
        $applicationOutput = new BufferedOutput();
        $application->doRun(new ArgvInput($_SERVER['argv']), $applicationOutput);

        $expected = $this->getExpectations($filename);
        $actual = $this->cleanOutput($applicationOutput->fetch());
        return $expected === $actual
            ? [true, '<info>[✓] '.$this->extractFeatureName($filename).'<info>']
            : [false, '<error>[✗] '.$this->extractFeatureName($filename).'<error>'];
    }

    private function cleanOutput(string $output) : string
    {
        return trim(str_replace(Application::XDEBUG_WARNING, '', $output));
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function extractFeatureName(string $filename) : string
    {
        preg_match_all('/((?:^|[A-Z])[a-z0-9]+)/', $filename, $matches);
        return str_replace(['cli', ' feature'], '', strtolower(implode(' ', $matches[1])));
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function getExpectations(string $filename) : string
    {
        $expectations = [];
        foreach (file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos($line, '# ') !== 0) {
                break;
            }
            $expectations[] = str_replace('# ', '', $line);
        }
        return implode(PHP_EOL, $expectations);
    }

    /**
     *
     * @return \RegexIterator
     */
    protected function fetchAllFeatureTests() : \RegexIterator
    {
        return new \RegexIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../../tests/samples')
            ), '/^.+Feature\.php$/i', \RecursiveRegexIterator::GET_MATCH
        );
    }

    /**
     * @param $files
     *
     * @return array
     */
    protected function runAllTests($files) : array
    {
        return array_map(function ($filename) {
            return $this->runTest($filename[0]);
        }, iterator_to_array($files));
    }

    private function sortSuccessFirst() : \Closure
    {
        return function (array $x, array $y) {
            if ($x[0] === true) {
                return -1;
            } elseif ($y[0] === true) {
                return 1;
            }
            return 0;
        };
    }
}
// @codeCoverageIgnoreEnd
