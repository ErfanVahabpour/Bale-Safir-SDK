<?php

namespace EFive\Bale\Safir\Tests\Unit;

use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\Exceptions\ClientNotFoundException;
use EFive\Bale\Safir\SafirManager;
use PHPUnit\Framework\TestCase;

final class SafirManagerTest extends TestCase
{
    public function testItResolvesDefaultAndNamedClientProfiles(): void
    {
        $manager = new SafirManager([
            'default' => 'default',
            'timeout' => 45,
            'clients' => [
                'default' => [
                    'api_access_key' => 'default-key',
                    'bot_id' => 100,
                ],
                'campaigns' => [
                    'api_access_key' => 'campaign-key',
                    'bot_id' => 200,
                ],
            ],
        ]);

        self::assertSame('default', $manager->getDefaultClientName());
        self::assertTrue($manager->hasClient('default'));
        self::assertTrue($manager->hasClient('campaigns'));
        self::assertInstanceOf(Api::class, $manager->client());
        self::assertInstanceOf(Api::class, $manager->client('campaigns'));
        self::assertNotSame($manager->client(), $manager->client('campaigns'));
        self::assertSame($manager->client('campaigns'), $manager->client('campaigns'));
    }

    public function testUnknownClientNameThrowsAnException(): void
    {
        $manager = new SafirManager(['default' => 'default', 'clients' => []]);

        $this->expectException(ClientNotFoundException::class);
        $manager->client('missing');
    }
}
