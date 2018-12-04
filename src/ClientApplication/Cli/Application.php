<?php

namespace Serato\SwsApp\ClientApplication\Cli;

use Symfony\Component\Console\Application as BaseApplication;
use Aws\Sdk as AwsSdk;
use Psr\Cache\CacheItemPoolInterface;
use Serato\SwsApp\ClientApplication\Cli\Command\ConfirmPasswordCommand;
use Serato\SwsApp\ClientApplication\Cli\Command\ShowMissingCredentialsCommand;
use Serato\SwsApp\ClientApplication\Cli\Command\ViewConfigCommand;

class Application extends BaseApplication
{
    public const NAME = 'Serato Client Application Data CLI';
    public const VERSION = '0.1';

    /** @var string */
    private $env;

    /** @var AwsSdk */
    private $awsSdk;

    /** @var CacheItemPoolInterface */
    private $psrCache;

    /**
     * Constructs the object
     *
     * @param string                    $name       The name of the application
     * @param string                    $version    The version of the application
     * @param string                    $env        Application environment
     * @param AwsSdk                    $awsSdk     AWS SDK
     * @param CacheItemPoolInterface    $psrCache   PSR-6 cache item pool
     *
     * @return void
     */
    public function __construct(
        string $name,
        string $version,
        string $env,
        AwsSdk $awsSdk,
        CacheItemPoolInterface $psrCache
    ) {
        parent::__construct($name, $version);
        $this->env = $env;
        $this->awsSdk = $awsSdk;
        $this->psrCache = $psrCache;
        $this->loadCommands();
    }

    /**
     * Creates a new instance of the object
     *
     * @param string                    $env        Application environment
     * @param AwsSdk                    $awsSdk     AWS SDK
     * @param CacheItemPoolInterface    psrCache    PSR-6 cache item pool
     * @return self
     */
    public static function create(string $env, AwsSdk $awsSdk, CacheItemPoolInterface $psrCache): self
    {
        return new self(self::NAME, self::VERSION, $env, $awsSdk, $psrCache);
    }

    private function loadCommands(): void
    {
        $this->add(new ConfirmPasswordCommand($this->env, $this->awsSdk, $this->psrCache));
        $this->add(new ShowMissingCredentialsCommand($this->env, $this->awsSdk, $this->psrCache));
        $this->add(new ViewConfigCommand($this->env, $this->awsSdk, $this->psrCache));
            
        # Show missing params
        #  - For default env
        #  - For all apps
        #  - (option) env
        #  - (option) app (name or ID??)
    }
}
