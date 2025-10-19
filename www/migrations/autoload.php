<?php
require_once __DIR__ . '/../wordpress/wp-load.php';

function loadEnv(string $basePath = __DIR__ . '/../../'): void
{
    $envPath = rtrim($basePath, '/') . '/.env';
    $envDevPath = rtrim($basePath, '/') . '/.env.dev';

    if (file_exists($envPath)) {
        $envFile = $envPath;
        echo "Načítavam konfiguráciu z .env\n";
    } elseif (file_exists($envDevPath)) {
        $envFile = $envDevPath;
        echo "Načítavam konfiguráciu z .env.dev\n";
    } else {
        exit("Chýba .env aj .env.dev\n");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));

        // odstráni úvodzovky na začiatku a na konci, ak sú
        $value = trim($value, "\"'");

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

loadEnv();
