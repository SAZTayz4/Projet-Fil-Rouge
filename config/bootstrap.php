<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $_SERVER['APP_DEBUG'] ?? true);
$kernel->boot();

// Rendre le kernel accessible globalement
$GLOBALS['kernel'] = $kernel; 