<?php

declare(strict_types=1);

use App\Kernel;

// DEBUG LOG
// file_put_contents(__DIR__ . '/../var/log/debug_entry.log', date('c') . " - Request received: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
