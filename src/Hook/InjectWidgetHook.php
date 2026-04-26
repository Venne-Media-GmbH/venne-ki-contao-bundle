<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle\Hook;

use Contao\LayoutModel;
use Contao\PageModel;
use VenneMedia\VenneKiContaoBundle\Service\BotConfig;

/**
 * Contao `generatePage` hook.
 *
 * Appends the Venne KI widget loader to `$GLOBALS['TL_HEAD']` so Contao's
 * core emits it as a `<script>` tag inside the rendered `<head>`.
 *
 * Guardrails:
 *   - No-op if the plugin has not been configured yet.
 *   - No-op for error pages, logouts and feeds — a chat widget on a 404 or
 *     RSS feed is useless.
 *   - Emits a `defer` script so the page paint is not blocked.
 */
final class InjectWidgetHook
{
    /**
     * Contao page types we deliberately skip.
     */
    private const SKIPPED_PAGE_TYPES = [
        'error_401',
        'error_403',
        'error_404',
        'error_503',
        'logout',
        'news_feed',
        'calendar_feed',
    ];

    public function __construct(private readonly BotConfig $config)
    {
    }

    public function __invoke(PageModel $page, LayoutModel $layout, object $pageRegular): void
    {
        if (!$this->config->isConfigured()) {
            return;
        }

        if (\in_array((string) $page->type, self::SKIPPED_PAGE_TYPES, true)) {
            return;
        }

        $snippet = \sprintf(
            '<script src="%s" data-bot="%s" defer></script>',
            htmlspecialchars(BotConfig::WIDGET_URL, \ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->config->getBotUuid(), \ENT_QUOTES, 'UTF-8'),
        );

        if (!isset($GLOBALS['TL_HEAD']) || !\is_array($GLOBALS['TL_HEAD'])) {
            $GLOBALS['TL_HEAD'] = [];
        }
        $GLOBALS['TL_HEAD'][] = $snippet;
    }
}
