<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle\Service;

use Contao\Config;

/**
 * Persists the bound bot UUID in Contao's `tl_settings` (key-value store).
 *
 * Only ONE key is written:
 *   - venneChatbotBotUuid  — the UUID of the bot in the Venne KI portal
 *
 * Why no API-Key?
 *   The widget runs entirely in the visitor's browser; access is gated by the
 *   Domain-Whitelist configured per bot in the portal — not by a server-side
 *   secret. Adding an API key here would only sit in the HTML source and offer
 *   zero additional protection.
 */
final class BotConfig
{
    public const WIDGET_URL = 'https://venne-ki.de/widget.js';
    public const PORTAL_URL = 'https://venne-ki.de';

    private const KEY_BOT_UUID = 'venneChatbotBotUuid';
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public function getBotUuid(): string
    {
        return (string) (Config::get(self::KEY_BOT_UUID) ?? '');
    }

    public function isConfigured(): bool
    {
        return self::isValidUuid($this->getBotUuid());
    }

    public function persist(string $botUuid): void
    {
        $botUuid = strtolower(trim($botUuid));
        if (!self::isValidUuid($botUuid)) {
            throw new \InvalidArgumentException(\sprintf(
                'Bot-UUID "%s" hat kein gültiges UUID-Format.',
                $botUuid,
            ));
        }
        Config::persist(self::KEY_BOT_UUID, $botUuid);
    }

    public function clear(): void
    {
        Config::persist(self::KEY_BOT_UUID, '');
    }

    public static function isValidUuid(string $value): bool
    {
        return $value !== '' && (bool) preg_match(self::UUID_PATTERN, $value);
    }
}
