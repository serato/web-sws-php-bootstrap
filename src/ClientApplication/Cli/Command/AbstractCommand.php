<?php
namespace Serato\SwsApp\ClientApplication\Cli\Command;

use Aws\Sdk as AwsSdk;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
    public const ARG_APP_ID = 'app-id';
    public const ARG_PASSWORD = 'password';
    public const OPTION_APP_NAME = 'app-name';
    public const OPTION_ENVIRONMENT = 'env';

    /** @var string */
    private $env;

    /** @var AwsSdk */
    private $awsSdk;

    /** @var CacheItemPoolInterface */
    private $psrCache;

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
        $this->env = $env;
        $this->awsSdk = $awsSdk;
        $this->psrCache = $psrCache;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            self::OPTION_ENVIRONMENT,
            null,
            InputOption::VALUE_REQUIRED,
            "Application environment. One of `dev`, `test` or `production`.\n\n" .
            "Overrides the value extracted from the runtime environment itself."
        );
    }
}
