<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle\Tests\Unit\Hook;

use Contao\LayoutModel;
use Contao\PageModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VenneMedia\VenneKiContaoBundle\Hook\InjectWidgetHook;
use VenneMedia\VenneKiContaoBundle\Service\BotConfig;

#[CoversClass(InjectWidgetHook::class)]
final class InjectWidgetHookTest extends TestCase
{
    private const VALID_UUID = '019dbc75-9358-7345-b0a8-4bd3b82af875';

    protected function setUp(): void
    {
        $GLOBALS['TL_HEAD'] = [];
    }

    public function testDoesNothingIfNotConfigured(): void
    {
        $hook = new InjectWidgetHook($this->config(''));
        $hook($this->page('regular'), new LayoutModel(), new \stdClass());

        self::assertSame([], $GLOBALS['TL_HEAD']);
    }

    public function testInjectsScriptTagOnRegularPage(): void
    {
        $hook = new InjectWidgetHook($this->config(self::VALID_UUID));

        $hook($this->page('regular'), new LayoutModel(), new \stdClass());

        self::assertCount(1, $GLOBALS['TL_HEAD']);
        $snippet = $GLOBALS['TL_HEAD'][0];
        self::assertStringContainsString('src="https://venne-ki.de/widget.js"', $snippet);
        self::assertStringContainsString('data-bot="'.self::VALID_UUID.'"', $snippet);
        self::assertStringContainsString('defer', $snippet);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function skippedPageTypesProvider(): iterable
    {
        yield '401' => ['error_401'];
        yield '403' => ['error_403'];
        yield '404' => ['error_404'];
        yield '503' => ['error_503'];
        yield 'logout' => ['logout'];
        yield 'news feed' => ['news_feed'];
        yield 'calendar feed' => ['calendar_feed'];
    }

    #[DataProvider('skippedPageTypesProvider')]
    public function testSkipsWidgetOnErrorAndFeedPages(string $pageType): void
    {
        $hook = new InjectWidgetHook($this->config(self::VALID_UUID));

        $hook($this->page($pageType), new LayoutModel(), new \stdClass());

        self::assertSame([], $GLOBALS['TL_HEAD'], 'Widget must not be injected on '.$pageType);
    }

    public function testInitializesGlobalIfMissing(): void
    {
        unset($GLOBALS['TL_HEAD']);
        $hook = new InjectWidgetHook($this->config(self::VALID_UUID));

        $hook($this->page('regular'), new LayoutModel(), new \stdClass());

        self::assertIsArray($GLOBALS['TL_HEAD']);
        self::assertCount(1, $GLOBALS['TL_HEAD']);
    }

    private function page(string $type): PageModel
    {
        $page = new PageModel();
        $page->type = $type;

        return $page;
    }

    private function config(string $uuid): BotConfig
    {
        // Set in Contao\Config singleton via reflection — production code reads
        // through Config::get() which the BotConfig wraps.
        $config = new BotConfig();
        $key = (new \ReflectionClass(BotConfig::class))->getConstant('KEY_BOT_UUID');
        \Contao\Config::set(\is_string($key) ? $key : 'venneChatbotBotUuid', $uuid);

        return $config;
    }
}
