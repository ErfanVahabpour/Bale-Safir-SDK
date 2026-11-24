<?php

namespace EFive\Bale\Safir\Tests\Feature;

use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\Laravel\Facades\Safir as SafirFacade;
use EFive\Bale\Safir\Laravel\SafirServiceProvider;
use EFive\Bale\Safir\SafirManager;
use Orchestra\Testbench\TestCase;

final class LaravelServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [SafirServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('bale-safir.default', 'default');
        $app['config']->set('bale-safir.base_url', 'https://safir.test/api/v3');
        $app['config']->set('bale-safir.clients', [
            'default' => [
                'api_access_key' => 'default-key',
                'bot_id' => 100,
            ],
            'campaigns' => [
                'api_access_key' => 'campaign-key',
                'bot_id' => 200,
            ],
        ]);
    }

    public function testProviderBindsTheManagerAndDefaultApi(): void
    {
        $manager = $this->app->make(SafirManager::class);

        self::assertSame($manager, $this->app->make('bale-safir'));
        self::assertSame($manager->client(), $this->app->make(Api::class));
        self::assertSame($manager->client(), $this->app->make('bale-safir.client'));
    }

    public function testFacadeResolvesNamedClientsThroughTheManager(): void
    {
        $manager = $this->app->make(SafirManager::class);

        self::assertSame($manager, SafirFacade::getFacadeRoot());
        self::assertSame($manager->client('campaigns'), SafirFacade::client('campaigns'));
        self::assertSame(['default', 'campaigns'], array_keys(SafirFacade::getClients()));
    }
}
