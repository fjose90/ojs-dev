#!/bin/sh
set -e

# 0. Marcar o volume montado como seguro para o Git (dono é o usuário do host)
git config --global --add safe.directory '*'

# 1. Aplicar variáveis de ambiente no config.inc.php
php /var/www/html/docker/apply-env.php

# 2. Instalar dependências Composer
composer --working-dir=/var/www/html/lib/pkp install --no-interaction --no-progress
composer --working-dir=/var/www/html/plugins/generic/citationStyleLanguage install --no-interaction --no-progress
composer --working-dir=/var/www/html/plugins/paymethod/paypal install --no-interaction --no-progress

# 3. Instalar dependências JS e compilar assets
# lib/ui-library primeiro — primevue e outros ficam no seu próprio named volume
npm --prefix /var/www/html/lib/ui-library install
npm --prefix /var/www/html install

# NODE_PATH faz o Vite/Rollup resolver pacotes de lib/ui-library
# (como primevue/config) sem precisar hoisting manual
export NODE_PATH=/var/www/html/lib/ui-library/node_modules

npm --prefix /var/www/html run build

# 4. Criar diretórios de cache e ajustar permissões para www-data
mkdir -p /var/www/html/cache/opcache
chmod -R 777 /var/www/html/cache/
chmod -R 777 /var/www/html/public/
chmod 666 /var/www/html/config.inc.php
chown -R www-data:www-data /var/www/files
chmod -R 775 /var/www/files

# 5. Iniciar Apache
exec apache2-foreground
