<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Serato\SwsApp\ClientApplication\Cli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ViewConfigCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('view-config')
            ->addOption(
                self::OPTION_APP_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                "Application name."
            )
            ->setDescription('Views a complete configuration for an environment')
            ->setHelp(
                "Views a complete configuration for an environment.\n\n" .
                "Defaults to displaying all applications in the environment. Can display\n" .
                "configuration for a single application by using the --" . self::OPTION_APP_NAME . " option.\n\n" .
                "Defaults to using the current runtime environment. This can be overridden with\n" .
                "the --" . self::OPTION_ENVIRONMENT . " option.\n"
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
