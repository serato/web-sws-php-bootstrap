<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Serato\SwsApp\ClientApplication\Cli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowMissingCredentialsCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('show-missing-credentials')
            ->setDescription('Shows missing credentials')
            ->setHelp(
                "Shows missing credentials.\n\n" .
                "Defaults to displaying missing credentials for all applications in the.\n" .
                "environment Can display missing credentials for a single application by\n" .
                "using the --" . self::OPTION_APP_NAME . " option.\n" .
                $this->getCommonHelpText()
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }
}
