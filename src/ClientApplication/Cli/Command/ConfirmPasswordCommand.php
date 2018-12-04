<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Serato\SwsApp\ClientApplication\Cli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfirmPasswordCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('confirm-password')
            ->addArgument(self::ARG_APP_ID, InputArgument::REQUIRED, 'Application ID')
            ->addArgument(self::ARG_PASSWORD, InputArgument::REQUIRED, 'Application Password')
            ->setDescription('Confirms a password for a given application')
            ->setHelp(
                "Confirms a password for an applicaton.\n\n" .
                "Uses the provided <" . self::ARG_APP_ID . "> and <" . self::ARG_PASSWORD .
                "> arguments to confirm the the client\n" .
                "application data contains the correct password hash.\n\n" .
                "Defaults to using the current runtime environment. This can be overridden with\n" .
                "the --" . self::OPTION_ENVIRONMENT . " argument.\n"
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //
    }
}
