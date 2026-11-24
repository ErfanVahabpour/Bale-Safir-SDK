<?php

namespace EFive\Bale\Safir;

use EFive\Bale\Safir\Exceptions\ClientNotFoundException;
use GuzzleHttp\ClientInterface;

/**
 * Resolves one or more named Safir credentials, mainly for Laravel applications.
 */
final class SafirManager
{
    /** @var array<string, Api> */
    private array $instances = [];

    public function __construct(
        private readonly array $config,
        private readonly ?ClientInterface $httpClient = null
    ) {
    }

    public function client(?string $name = null): Api
    {
        $name ??= (string) ($this->config['default'] ?? 'default');
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $profiles = $this->config['clients'] ?? [];
        $profile = $profiles[$name] ?? null;

        if (! is_array($profile)) {
            if ($name === (string) ($this->config['default'] ?? 'default')) {
                $profile = $this->config;
            } else {
                throw new ClientNotFoundException(sprintf('No Safir client named "%s" is configured.', $name));
            }
        }

        $settings = array_replace($this->config, $profile);
        $this->instances[$name] = new Api(
            apiAccessKey: isset($settings['api_access_key']) ? (string) $settings['api_access_key'] : null,
            botId: $settings['bot_id'] ?? null,
            httpClient: $this->httpClient,
            baseUrl: (string) ($settings['base_url'] ?? SafirClient::BASE_URL),
            timeout: (int) ($settings['timeout'] ?? 30),
            connectTimeout: (int) ($settings['connect_timeout'] ?? 10)
        );

        return $this->instances[$name];
    }

    public function hasClient(string $name): bool
    {
        return isset($this->config['clients'][$name]) || $name === (string) ($this->config['default'] ?? 'default');
    }

    /**
     * @return array<string, array>
     */
    public function getClients(): array
    {
        return $this->config['clients'] ?? [];
    }

    public function getDefaultClientName(): string
    {
        return (string) ($this->config['default'] ?? 'default');
    }

    public function __call(string $method, array $arguments): mixed
    {
        return $this->client()->{$method}(...$arguments);
    }
}
