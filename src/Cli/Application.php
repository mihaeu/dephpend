<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->printWarningIfXdebugIsEnabled($output);
        $this->setMemoryLimit($input);

        return parent::doRun($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    private function printWarningIfXdebugIsEnabled(OutputInterface $output)
    {
        if (extension_loaded('xdebug')) {
            $output->writeln(
                '<fg=black;bg=yellow>You are running dePHPend with xdebug enabled.'
                .' This has a major impact on runtime performance. '
                .'See https://getcomposer.org/xdebug</>'
            );
        }
    }

    /**
     * @param InputInterface $input
     */
    private function setMemoryLimit(InputInterface $input)
    {
        if ($input->hasOption('memory') && $input->getOption('memory')) {
            ini_set('memory_limit', $input->getOption('memory'));
        }
    }
}
