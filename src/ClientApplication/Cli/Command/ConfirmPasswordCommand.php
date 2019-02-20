<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Serato\SwsApp\ClientApplication\Cli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Exception;

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
            ->setDescription('Confirms a password for a given application')
            ->setHelp(
                "Confirms a password for an applicaton.\n\n" .
                "Prompts the user to provide a password and confirms that it matches with the\n" .
                "password hash stored against <" . self::ARG_APP_ID . ">.\n" .
                $this->getCommonHelpText()
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCommonOptions($input);

        $appId = $input->getArgument(self::ARG_APP_ID);

        $data = $this->getDataLoader()->getApp($this->getEnv(), $this->getUseCache());

        $passwordHash = $this->getPasswordHash($data, $appId);

        $helper = $this->getHelper('question');

        $question = new Question("Password for app '$appId'?");
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        $output->getFormatter()->setStyle(
            'yes',
            new OutputFormatterStyle('green', 'black', ['bold'])
        );
        $output->getFormatter()->setStyle(
            'no',
            new OutputFormatterStyle('red', 'black', ['bold'])
        );

        $this->writeInfoHeader($output, 'Confirm Password', ['Application ID' => $appId]);
        $output->writeln(
            "\nPassword match? " .
            (password_verify($password, $passwordHash) ? "<yes>Yes</yes>" : "<no>No</no>") .
            "\n"
        );
    }

    private function getPasswordHash(array $appData, string $appId): string
    {
        foreach ($appData as $name => $data) {
            if ($data['id'] === $appId) {
                if (!isset($data['password_hash'])) {
                    throw new Exception(
                        "No password hash found for app ID '$appId' in '"  . $this->getEnv(). "' environment"
                    );
                } else {
                    return $data['password_hash'];
                }
            }
        }
        throw new Exception("Application ID '$appId' not found in '"  . $this->getEnv(). "' environment");
    }
}
