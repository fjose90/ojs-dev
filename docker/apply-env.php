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

// Mapeamento: chave no config.inc.php => variável de ambiente
$values = [
    // Banco de dados
    'host'     => getenv('OJS_DB_HOST')     ?: 'db',
    'username' => getenv('OJS_DB_USER')     ?: 'ojs',
    'password' => getenv('OJS_DB_PASSWORD') ?: 'ojs',
    'name'     => getenv('OJS_DB_NAME')     ?: 'ojs',

    // Geral
    'app_key'  => getenv('OJS_APP_KEY')     ?: '',
    'base_url' => getenv('OJS_BASE_URL')    ?: 'http://localhost:8080',
    'time_zone'=> getenv('OJS_TIME_ZONE')   ?: 'UTC',

    // Segurança
    'salt'           => getenv('OJS_SALT')           ?: 'YouMustSetASecretKeyHere!!',
    'api_key_secret' => getenv('OJS_API_KEY_SECRET') ?: '',
];

foreach ($values as $key => $value) {
    $replacement = sprintf('%s = "%s"', $key, addcslashes($value, "\\\""));
    $config = preg_replace('/^' . preg_quote($key, '/') . '\s*=.*$/m', $replacement, $config, 1, $count);
    if ($count !== 1) {
        fwrite(STDERR, "Warning: could not update '{$key}' in config.inc.php (key not found)\n");
    }
}

// Variáveis booleanas (sem aspas)
$boolValues = [
    'installed' => getenv('OJS_INSTALLED') ?: 'Off',
];

foreach ($boolValues as $key => $value) {
    $replacement = sprintf('%s = %s', $key, $value);
    $config = preg_replace('/^' . preg_quote($key, '/') . '\s*=.*$/m', $replacement, $config, 1, $count);
    if ($count !== 1) {
        fwrite(STDERR, "Warning: could not update '{$key}' in config.inc.php (key not found)\n");
    }
}

// SMTP: descomenta e seta as linhas apenas se as vars estiverem definidas
$smtpServer   = getenv('OJS_SMTP_SERVER');
$smtpPort     = getenv('OJS_SMTP_PORT');
$smtpAuth     = getenv('OJS_SMTP_AUTH');
$smtpUser     = getenv('OJS_SMTP_USERNAME');
$smtpPassword = getenv('OJS_SMTP_PASSWORD');
$emailDefault  = getenv('OJS_EMAIL_DEFAULT') ?: 'log';

$config = preg_replace('/^default\s*=.*$/m', 'default = ' . $emailDefault, $config, 1);

if ($smtpServer) {
    $config = preg_replace('/^;\s*smtp\s*=.*$/m', 'smtp = On', $config, 1);
    $config = preg_replace('/^;\s*smtp_server\s*=.*$/m', 'smtp_server = ' . $smtpServer, $config, 1);
}
if ($smtpPort) {
    $config = preg_replace('/^;\s*smtp_port\s*=.*$/m', 'smtp_port = ' . $smtpPort, $config, 1);
}
if ($smtpAuth) {
    $config = preg_replace('/^;\s*smtp_auth\s*=.*$/m', 'smtp_auth = ' . $smtpAuth, $config, 1);
}
if ($smtpUser) {
    $config = preg_replace('/^;\s*smtp_username\s*=.*$/m', 'smtp_username = ' . $smtpUser, $config, 1);
}
if ($smtpPassword) {
    $config = preg_replace('/^;\s*smtp_password\s*=.*$/m', 'smtp_password = ' . $smtpPassword, $config, 1);
}

if (file_put_contents($configPath, $config) === false) {
    fwrite(STDERR, "Failed to write config.inc.php\n");
    exit(1);
}

echo "config.inc.php updated successfully.\n";