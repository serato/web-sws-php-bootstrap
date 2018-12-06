<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Exception;
use Serato\SwsApp\ClientApplication\Cli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ViewCredentialsCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('show-credentials')
            ->addOption(
                self::OPTION_APP_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                "Application name."
            )
            ->setDescription('Display environment-specific credentials')
            ->setHelp(
                "Display environment-specific credentials.\n\n" .
                "Defaults to displaying all applications in the environment. Can display\n" .
                "credentials for a single application by using the --" . self::OPTION_APP_NAME . " option.\n" .
                $this->getCommonHelpText()
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $credsFile = $this->getDataLoader()->getCredentialsObjectName($this->getEnv());
        $headerInfo = ['Credentials file' => $credsFile];
        $data = $this->getDataLoader()->getItem(
            $credsFile,
            $this->getUseCache()
        );

        if ($input->getOption(self::OPTION_APP_NAME)) {
            $appName = $input->getOption(self::OPTION_APP_NAME);
            $headerInfo['App name'] = $appName;
            if (!isset($data[$appName])) {
                throw new Exception("Invalid app name `$appName` for `" . $this->getEnv() . "` environment.");
            } else {
                $data = $data[$appName];
            }
        }
        $this->writeInfoHeader($output, 'Credentials', $headerInfo);
        $output->writeln("\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n");
    }
}
