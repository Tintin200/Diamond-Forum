<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    // Load .env.test for test environment, fallback to .env
    $envFile = dirname(__DIR__).'/.env.test';
    if (file_exists($envFile)) {
        (new Dotenv())->bootEnv($envFile);
    } else {
        (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
    }
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
