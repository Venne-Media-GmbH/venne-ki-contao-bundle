<?php

declare(strict_types=1);

/**
 * Backend-Modul-Registrierung: erscheint unter System → Venne KI.
 */
$GLOBALS['BE_MOD']['system']['venne_chatbot'] = [
    'tables' => [],
    'callback' => VenneMedia\VenneKiContaoBundle\Backend\KiAssistentSettingsModule::class,
    'icon' => 'bundles/vennekicontao/icon.svg',
];

/**
 * Hook: injiziert den Widget-Script-Tag in den <head> jeder Frontend-Seite.
 */
$GLOBALS['TL_HOOKS']['generatePage'][] = [
    VenneMedia\VenneKiContaoBundle\Hook\InjectWidgetHook::class,
    '__invoke',
];
