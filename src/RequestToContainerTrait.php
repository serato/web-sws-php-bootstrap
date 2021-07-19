<?php

namespace Serato\SwsApp;

use Slim\Container;
use Psr\Http\Message\ServerRequestInterface as Request;

trait RequestToContainerTrait
{
    /** @var string */
    private $containerKey = 'requestFinal';

    protected function setRequestToContainer(Request $request, ?Container $container): void
    {
        if ($container !== null) {
            $container[$this->containerKey] = $request;
        }
    }

    protected function getRequestFromContainer(?Container $container): ?Request
    {
        if ($container !== null && $container->has($this->containerKey)) {
            return $container->get($this->containerKey);
        }
        return null;
    }
}
