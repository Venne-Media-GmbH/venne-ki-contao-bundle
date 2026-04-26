<?php

declare(strict_types=1);

namespace VenneMedia\VenneKiContaoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class VenneKiContaoBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
