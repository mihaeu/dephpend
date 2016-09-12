<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Util\DI;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
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
        $files = new \RegexIterator(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__.'/../../tests/features')
            ), '/^.+Feature\.php$/i', \RecursiveRegexIterator::GET_MATCH
        );
        foreach ($files as $filename) {
            $this->runTest($filename[0], $output);
        }
    }

    public function runTest(string $filename, OutputInterface $output)
    {
        $application = new Application('', '', new DI());
        $application->setAutoExit(false);
        $applicationOutput = new BufferedOutput();
        $args = [
            'command'       => 'text',
            'source'        => [$filename],
        ];
        $application->run(new ArrayInput($args), $applicationOutput);

        $expected = $this->getExpectations($filename);
        $actual = $this->cleanOutput($applicationOutput->fetch());
        $expected === $actual
            ? $output->writeln('<info>[✓] '.$this->extractFeatureName($filename).'<info>')
            : $output->writeln('<error>[✗] '.$this->extractFeatureName($filename).'<error>');
    }

    private function cleanOutput(string $output) : string
    {
        return trim(str_replace(
            'You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug',
            '',
            $output
        ));
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function extractFeatureName(string $filename) : string
    {
        $matches = [];
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $filename, $matches);

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
}
// @codeCoverageIgnoreEnd
