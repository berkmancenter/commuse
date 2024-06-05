#!/bin/bash

# Don't overwrite if .env is test configuration
if ! grep -q "commuse_tests" .env; then
  mv .env .env.bak 2>/dev/null
fi

cp .env.test .env

# Run db migrations
composer install
php spark migrate --all

# Install and start selenium server
selenium-standalone install
pkill -f 'selenium-standalone'
selenium-standalone start &

# Build front-end app
cd front && ./deploy.sh && cd ..

# Run tests
php vendor/bin/codecept run --steps

cp .env.bak .env

# Remove backup file if default .env is in place
if grep -q "database.default.hostname" .env; then
  rm .env.bak 2>/dev/null
fi
