<?php

namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Exception;
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
            ->setName('show-config')
            ->addOption(
                self::OPTION_APP_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                "Application name."
            )
            ->setDescription('Display app configuration for an environment')
            ->setHelp(
                "Display app configuration for an environment.\n\n" .
                "Displays complete configuration data that is a merged combination of common\n" .
                "application data and environment-specific credentials data.\n\n" .
                "This is the same data provided into a SWS web application.\n\n" .
                "Defaults to displaying all applications in the environment. Can display\n" .
                "configuration for a single application by using the --" . self::OPTION_APP_NAME . " option.\n" .
                $this->getCommonHelpText()
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCommonOptions($input);

        $headerInfo = [];

        $data = $this->getDataLoader()->getApp($this->getEnv(), $this->getUseCache());

        if ($input->getOption(self::OPTION_APP_NAME)) {
            $appName = $input->getOption(self::OPTION_APP_NAME);
            $headerInfo['App name'] = $appName;
            if (!isset($data[$appName])) {
                throw new Exception("Invalid app name `$appName` for `" . $this->getEnv() . "` environment.");
            } else {
                $data = $data[$appName];
            }
        }
        $this->writeInfoHeader($output, 'App Configuration', $headerInfo);
        $output->writeln("\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n");
    }
}
