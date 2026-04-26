<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle\Backend;

use Contao\BackendModule;
use Contao\BackendUser;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use VenneMedia\VenneKiContaoBundle\Service\BotConfig;

/**
 * Backend page under **System → Venne KI**.
 *
 * Single setting: the bot UUID. Once saved, the frontend widget loads
 * automatically on every page (except 4xx/5xx and feeds).
 *
 * Restricted to administrators.
 */
final class KiAssistentSettingsModule extends BackendModule
{
    protected $strTemplate = 'be_ki_settings';

    private const FORM_SUBMIT_TOKEN = 'venne_chatbot_settings';
    private const ACTION_CLEAR = 'clear';

    public function generate(): string
    {
        $user = BackendUser::getInstance();
        // Contao\BackendUser exposes "isAdmin" via __get → PHPStan doesn't see it.
        /** @phpstan-ignore-next-line property.notFound */
        $isAdmin = (bool) $user->isAdmin;
        if (!$isAdmin) {
            return '<div class="tl_error"><p>Nur Administratoren haben Zugriff auf diese Seite.</p></div>';
        }

        return parent::generate();
    }

    protected function compile(): void
    {
        $container = System::getContainer();
        /** @var BotConfig $config */
        $config = $container->get(BotConfig::class);

        if (Input::post('FORM_SUBMIT') === self::FORM_SUBMIT_TOKEN) {
            /** @var CsrfTokenManagerInterface $csrf */
            $csrf = $container->get('contao.csrf.token_manager');
            $tokenName = (string) $container->getParameter('contao.csrf_token_name');
            $submitted = (string) Input::post('REQUEST_TOKEN');
            if (!$csrf->isTokenValid(new CsrfToken($tokenName, $submitted))) {
                Message::addError('Sicherheitstoken ungültig. Bitte die Seite neu laden und erneut speichern.');
            } else {
                $this->handleSubmit($config);
            }
        }

        $portalHost = parse_url(BotConfig::PORTAL_URL, \PHP_URL_HOST);

        $this->Template->botUuid = $config->getBotUuid();
        $this->Template->isConfigured = $config->isConfigured();
        $this->Template->portalUrl = BotConfig::PORTAL_URL;
        $this->Template->portalHost = \is_string($portalHost) && $portalHost !== '' ? $portalHost : BotConfig::PORTAL_URL;
        $this->Template->widgetUrl = BotConfig::WIDGET_URL;
        $this->Template->formAction = (string) Environment::get('request');
        $this->Template->formToken = self::FORM_SUBMIT_TOKEN;
        /** @var CsrfTokenManagerInterface $csrf2 */
        $csrf2 = $container->get('contao.csrf.token_manager');
        $this->Template->requestToken = $csrf2->getDefaultTokenValue();
    }

    private function handleSubmit(BotConfig $config): void
    {
        if ((string) Input::post('ki_action') === self::ACTION_CLEAR) {
            $config->clear();
            Message::addConfirmation('Verbindung getrennt. Die Bot-UUID wurde entfernt.');

            return;
        }

        $rawUuid = trim((string) Input::postRaw('ki_bot_uuid'));
        if ($rawUuid === '') {
            Message::addError('Bitte trage eine Bot-UUID ein, bevor du speicherst.');

            return;
        }

        try {
            $config->persist($rawUuid);
        } catch (\InvalidArgumentException $e) {
            Message::addError(\sprintf(
                'Die Bot-UUID hat kein gültiges UUID-Format. Beispiel: 019dc984-c545-7890-a9ca-1f6fd97f11d4. (%s)',
                $e->getMessage(),
            ));

            return;
        }

        Message::addConfirmation(
            'Verbindung erfolgreich hergestellt. Das Widget ist ab sofort auf allen Frontend-Seiten aktiv.',
        );
    }
}
