# Build
# ==============================================================================
FROM ubuntu:jammy AS build

# System
# ------------------------------------------------------------------------------

RUN apt update && apt upgrade -y

RUN apt install -y \
    software-properties-common \
    curl \
    git \
    ""

# PHP
# ------------------------------------------------------------------------------
RUN <<EOF
set -eux

PHP_VERSION=8.3
PHP_CONFIG="/etc/php/${PHP_VERSION}"

LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
DEBIAN_FRONTEND=noninteractive TZ=UTC apt install -y \
    "php${PHP_VERSION}-cli" \
    "php${PHP_VERSION}-common" \
    "php${PHP_VERSION}-mbstring" \
    "php${PHP_VERSION}-bcmath" \
    "php${PHP_VERSION}-zip" \
    "php${PHP_VERSION}-intl" \
    "php${PHP_VERSION}-xml" \
    "php${PHP_VERSION}-xdebug" \
    "php${PHP_VERSION}-curl" \
    "php${PHP_VERSION}-pdo-sqlite" \
    ""

sed -i 's/^error_reporting = .\+$/error_reporting = E_ALL/'            "${PHP_CONFIG}/cli/php.ini"
sed -i 's/^display_errors = .\+$/display_errors = On/'                 "${PHP_CONFIG}/cli/php.ini"
sed -i 's/^;opcache\.enable=.\+$/opcache.enable=1/'                    "${PHP_CONFIG}/cli/php.ini"
sed -i 's/^;opcache\.enable_cli=.\+$/opcache.enable_cli=1/'            "${PHP_CONFIG}/cli/php.ini"
tee -a "${PHP_CONFIG}/mods-available/xdebug.ini" > /dev/null <<"EOT"
xdebug.output_dir = /project/.xdebug
xdebug.profiler_output_name = callgrind.out.%t.%r
xdebug.client_host = host.docker.internal
xdebug.mode = debug
xdebug.start_with_request = trigger
EOT
EOF

# Composer
# ------------------------------------------------------------------------------
# https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
RUN <<EOF
set -eux
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
RESULT=$?
rm composer-setup.php
exit $RESULT
EOF

# Npm
# ------------------------------------------------------------------------------
# https://github.com/nodesource/distributions
RUN <<EOF
set -eux
curl -fsSL https://deb.nodesource.com/setup_lts.x | bash
apt install -y nodejs
EOF

# Scratch
# ==============================================================================
FROM scratch
COPY --from=build / /
CMD "/bin/bash"
