<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Aws\Sdk as AwsSdk;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Serato\SwsApp\ClientApplication\DataLoader;

abstract class AbstractCommand extends Command
{
    public const ARG_APP_ID = 'app-id';
    public const ARG_PASSWORD = 'password';
    public const OPTION_APP_NAME = 'app-name';
    public const OPTION_ENVIRONMENT = 'env';
    public const OPTION_IGNORE_CACHE = 'ignore-cache';

    /** @var string */
    private $env;

    /** @var boolean */
    private $useCache = true;

    /** @var DataLoader */
    private $dataLoader;

    /**
     * Constructs the command
     *
     * @param string                    $env        Application environment
     * @param AwsSdk                    $awsSdk     AWS SDK
     * @param CacheItemPoolInterface    $psrCache   PSR-6 cache item pool
     *
     * @return void
     */
    public function __construct(string $env, AwsSdk $awsSdk, CacheItemPoolInterface $psrCache)
    {
        parent::__construct();
        $this->env = $env;
        $this->dataLoader = new DataLoader($env, $awsSdk, $psrCache);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                self::OPTION_ENVIRONMENT,
                null,
                InputOption::VALUE_REQUIRED,
                "Application environment. One of `dev`, `test` or `production`.\n\n" .
                "Overrides the value extracted from the runtime environment itself."
            )
            ->addOption(
                self::OPTION_IGNORE_CACHE,
                null,
                InputOption::VALUE_NONE,
                "Ignores cached configuration data and always uses data fetched from S3."
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPTION_IGNORE_CACHE)) {
            $this->useCache = false;
        }
        if ($input->getOption(self::OPTION_ENVIRONMENT)) {
            $this->env = $input->getOption(self::OPTION_ENVIRONMENT);
        }
    }

    /**
     * Writes meaningful output about the command to the console
     *
     * @param OutputInterface $output
     * @param string $title
     * @param array $headerInfo
     * @return void
     */
    protected function writeInfoHeader(OutputInterface $output, string $title, array $headerInfo = []): void
    {
        $output->getFormatter()->setStyle(
            'header',
            new OutputFormatterStyle('black', 'yellow', ['bold'])
        );

        $maxLen = ['k' => 0, 'v' => 0];
        $rows = array_merge(
            [
                "Environment" => $this->getEnv(),
                "Use cache?" => ($this->getUseCache() ? "Yes" : "No")
            ],
            $headerInfo
        );
        $output->writeln("");
        foreach ($rows as $k => $v) {
            if (strlen($k) > $maxLen['k']) {
                $maxLen['k'] = strlen($k);
            }
            if (strlen($v) > $maxLen['v']) {
                $maxLen['v'] = strlen($v);
            }
        }
        $output->writeln(
            "<header> " . str_pad('', $maxLen['k'] + $maxLen['v'] + 3, '-') . " </header>"
        );
        $output->writeln(
            "<header> " . str_pad($title, $maxLen['k'] + $maxLen['v'] + 3, ' ') . " </header>"
        );
        $output->writeln(
            "<header> " . str_pad('', $maxLen['k'] + $maxLen['v'] + 3, '-') . " </header>"
        );
        foreach ($rows as $k => $v) {
            $output->writeln(
                "<header> " . str_pad($k, $maxLen['k'], ' ') . " : " . str_pad($v, $maxLen['v'], ' ') . " </header>"
            );
        }
        $output->writeln(
            "<header> " . str_pad('', $maxLen['k'] + $maxLen['v'] + 3, '-') . " </header>"
        );
    }

    protected function getCommonHelpText(): string
    {
        return "\nDefaults to using the current runtime environment. This can be overridden with\n" .
                "the --" . self::OPTION_ENVIRONMENT . " argument.\n";
    }

    protected function getEnv(): string
    {
        return $this->env;
    }

    protected function getUseCache(): bool
    {
        return $this->useCache;
    }

    protected function getDataLoader(): DataLoader
    {
        return $this->dataLoader;
    }
}
