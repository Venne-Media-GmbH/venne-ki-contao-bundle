<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VenneMedia\VenneKiContaoBundle\Service\BotConfig;

#[CoversClass(BotConfig::class)]
final class BotConfigTest extends TestCase
{
    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function uuidProvider(): iterable
    {
        // valid UUIDs (any version, lowercase + uppercase + mixed)
        yield 'v4 lowercase' => ['fbd3036f-0f1c-4b48-b6c1-c7f3aa5f6d3f', true];
        yield 'v7 lowercase' => ['019dbc75-9358-7345-b0a8-4bd3b82af875', true];
        yield 'v7 uppercase' => ['019DBC75-9358-7345-B0A8-4BD3B82AF875', true];

        // invalid
        yield 'empty' => ['', false];
        yield 'too short' => ['019dbc75-9358', false];
        yield 'no dashes' => ['019dbc7593587345b0a84bd3b82af875', false];
        yield 'wrong chars' => ['019dbc75-9358-7345-b0a8-zzzzzzzzzzzz', false];
        yield 'whitespace only' => ['   ', false];
    }

    #[DataProvider('uuidProvider')]
    public function testIsValidUuid(string $value, bool $expected): void
    {
        self::assertSame($expected, BotConfig::isValidUuid($value));
    }
}
