<?php
namespace Serato\SwsApp\Slim\Controller;

/**
 * Controller Scopes
 */
class Scopes
{
    protected $scopes = [];

    /**
     * Creates a new Scopes instance
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Returns an array of scopes
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Adds a scope
     *
     * @param string $scope
     *
     * @return self
     */
    public function addScope(string $scope): self
    {
        $this->scopes[] = $scope;
        return $this;
    }

    /**
     * Adds an array of scopes
     *
     * @param array $scopes
     *
     * @return self
     */
    public function addScopes(array $scopes): self
    {
        $this->scopes = array_merge($this->scopes, $scopes);
        return $this;
    }
}
