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
    public const OPTION_LOCAL_DIRECTORY_PATH = 'local-dir-path';

    /** @var string */
    private $env;

    /** @var AwsSdk */
    private $awsSdk;

    /** @var CacheItemPoolInterface */
    private $psrCache;

    /** @var boolean */
    private $useCache = true;

    /** @var DataLoader */
    private $dataLoader;

    /** @var string */
    private $localDirPath;

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
        $this->awsSdk = $awsSdk;
        $this->psrCache = $psrCache;
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
                "Application environment. One of `dev`, `test` or `production`. " .
                "Overrides the value extracted from the runtime environment itself."
            )
            ->addOption(
                self::OPTION_IGNORE_CACHE,
                null,
                InputOption::VALUE_NONE,
                "Ignores cached configuration data and always uses data fetched from S3."
            )
            ->addOption(
                self::OPTION_LOCAL_DIRECTORY_PATH,
                null,
                InputOption::VALUE_REQUIRED,
                "Read application data files from a local directory. " .
                "Intended for testing purposes only. " .
                "Overrides the `--" . self::OPTION_IGNORE_CACHE . "` option."
            );
    }

    /**
     * Reads common CLI options into class properties
     *
     * @param InputInterface $input
     * @return void
     */
    protected function getCommonOptions(InputInterface $input): void
    {
        if ($input->getOption(self::OPTION_IGNORE_CACHE)) {
            $this->useCache = false;
        }
        if ($input->getOption(self::OPTION_ENVIRONMENT)) {
            $this->env = $input->getOption(self::OPTION_ENVIRONMENT);
        }
        if ($input->getOption(self::OPTION_LOCAL_DIRECTORY_PATH)) {
            $this->localDirPath = $input->getOption(self::OPTION_LOCAL_DIRECTORY_PATH);
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

        $commonHeaderInfo = ['Environment' => $this->getEnv()];
        if ($this->localDirPath === null) {
            $commonHeaderInfo['Use cache?'] = ($this->getUseCache() ? "Yes" : "No");
        } else {
            $commonHeaderInfo['Local path'] = $this->localDirPath;
            $commonHeaderInfo['Local path expanded'] = realpath($this->localDirPath);
        }

        $rows = array_merge($commonHeaderInfo, $headerInfo);

        $maxLen = ['k' => 0, 'v' => 0];

        foreach ($rows as $k => $v) {
            if (strlen($k) > $maxLen['k']) {
                $maxLen['k'] = strlen($k);
            }
            if (strlen($v) > $maxLen['v']) {
                $maxLen['v'] = strlen($v);
            }
        }

        $output->writeln("");
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
                "the --" . self::OPTION_ENVIRONMENT . " argument.\n\n" .
                "Defaults to using application data sourced from Amazon S3, and will use cached\n" .
                "data (if it exists) unless the `" . self::OPTION_IGNORE_CACHE . "` option is set.\n\n" .
                "This behaviour can be overriden by using the `" . self::OPTION_LOCAL_DIRECTORY_PATH .
                "` option. This will\n" .
                "look for application data in the provided local directory path (use this for testing\n" .
                "purposes only - always use Amazon S3 sourced data for live web applications).\n";
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
        return new DataLoader($this->getEnv(), $this->awsSdk, $this->psrCache, $this->localDirPath);
    }
}
