<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use PhpParser\Error;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Application extends \Symfony\Component\Console\Application
{
    const XDEBUG_WARNING = 'You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug';

    public function __construct(string $name, string $version, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($name, $version);

        $this->setHelperSet($this->getDefaultHelperSet());
        $this->setDefaultCommand('list');
        $this->setDispatcher($dispatcher);
    }

    /**
     * Commands are added here instead of before executing run(), because
     * we need access to command line options in order to inject the
     * right dependencies.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->printWarningIfXdebugIsEnabled($output);

        try {
            parent::doRun($input, $output);
        } catch (Error $e) {
            $output->writeln('<error>Sorry, we could not analyse your dependencies, '
                . 'because the sources contain syntax errors:' . PHP_EOL . PHP_EOL
                . $e->getMessage() . '<error>'
            );
            return $e->getCode() ?? 1;
        } catch (\Throwable $e) {
            if ($output !== null) {
                $output->writeln(
                    "<error>Something went wrong, this shouldn't happen."
                    . ' Please take a minute and report this issue:'
                    . ' https://github.com/mihaeu/dephpend/issues</error>'
                    . PHP_EOL . PHP_EOL
                    . $e->getMessage()
                    . PHP_EOL . PHP_EOL
                    . "[{$e->getFile()} at line {$e->getLine()}]"
                );
            }
            return $e->getCode() ?? 1;
        }

        return 0;
    }

    private function printWarningIfXdebugIsEnabled(OutputInterface $output)
    {
        if (\extension_loaded('xdebug')) {
            $output->writeln('<fg=black;bg=yellow>' . self::XDEBUG_WARNING . '</>');
        }
    }
}
