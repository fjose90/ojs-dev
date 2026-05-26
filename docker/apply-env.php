<?php

$configPath = '/var/www/html/config.inc.php';
$templatePath = '/var/www/html/config.TEMPLATE.inc.php';

if (!file_exists($configPath)) {
    if (!copy($templatePath, $configPath)) {
        fwrite(STDERR, "Failed to create config.inc.php from template\n");
        exit(1);
    }
}

$config = file_get_contents($configPath);
if ($config === false) {
    fwrite(STDERR, "Failed to read config.inc.php\n");
    exit(1);
}

$values = [
    'host' => getenv('OJS_DB_HOST') ?: 'db',
    'username' => getenv('OJS_DB_USER') ?: 'ojs',
    'password' => getenv('OJS_DB_PASSWORD') ?: 'ojs',
    'name' => getenv('OJS_DB_NAME') ?: 'ojs',
];

foreach ($values as $key => $value) {
    $replacement = sprintf('%s = "%s"', $key, addcslashes($value, "\\\"") );
    $config = preg_replace('/^' . preg_quote($key, '/') . '\s*=.*$/m', $replacement, $config, 1, $count);
    if ($count !== 1) {
        fwrite(STDERR, "Failed to update {$key} in config.inc.php\n");
        exit(1);
    }
}

if (file_put_contents($configPath, $config) === false) {
    fwrite(STDERR, "Failed to write config.inc.php\n");
    exit(1);
}