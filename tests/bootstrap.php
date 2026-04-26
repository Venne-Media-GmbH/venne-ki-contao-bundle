<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

// Stubs for Contao classes we reference in hook/backend but don't exercise
// in unit tests. Loading the full Contao stack for unit tests is overkill
// and would make the test suite slow and flaky.
if (!class_exists(Contao\PageModel::class, false)) {
    eval('namespace Contao; class PageModel { public string $type = "regular"; }');
}
if (!class_exists(Contao\LayoutModel::class, false)) {
    eval('namespace Contao; class LayoutModel {}');
}
