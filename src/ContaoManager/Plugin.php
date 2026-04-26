<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use VenneMedia\VenneKiContaoBundle\VenneKiContaoBundle;

final class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(VenneKiContaoBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
