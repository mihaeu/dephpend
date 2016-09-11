<?php

declare(strict_types = 1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Mihaeu\PhpDependencies\Cli\Application;
use Mihaeu\PhpDependencies\Util\DI;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    /** @var string */
    private $command;

    /** @var string */
    private $dir;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $this->dir = sys_get_temp_dir() . '/dephpend-features';
        if (!@mkdir($this->dir) && !is_dir($this->dir)) {
            throw new RuntimeException('Cannot create '.$this->dir);
        }
    }

    /**
     * @Given I run the :name command
     */
    public function iRunTheNameCommand(string $name)
    {
        $this->command = $name;
    }

    /**
     * @Given I have the following code:
     */
    public function iHaveTheFollowingCode(PyStringNode $string)
    {
        file_put_contents($this->dir.'/feature.php', '<?php'.PHP_EOL.$string);
    }

    /**
     * @Then I should see:
     * @throws \Exception
     */
    public function iShouldSee(PyStringNode $string)
    {
        $application = new Application('', '', new DI());
        $application->setAutoExit(false);
        $output = new BufferedOutput();
        $args = [
            'command'       => $this->command,
            'source'        => [$this->dir],
        ];
        $application->run(new ArrayInput($args), $output);

        $expected = $string->getRaw();
        $actual = $this->cleanOutput($output->fetch());
        PHPUnit_Framework_Assert::assertEquals($expected, $actual);
    }

    /**
     * @param string $output
     *
     * @return string
     */
    private function cleanOutput(string $output) : string
    {
        return trim(str_replace(
            'You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug',
            '',
            $output
        ));
    }
}
