#!/bin/bash

# Don't overwrite if .env is test configuration
if [ -f .env ]; then
  if ! grep -q "commuse_tests" .env; then
    mv .env .env.bak
  fi
fi

cp .env.test .env

# Run db migrations
composer install
php spark migrate:refresh --all

# Install and start selenium server
selenium-standalone install
pkill -9 -f 'selenium-standalone'
selenium-standalone start &
timeout 120 bash -c 'while [[ "$(curl -s -o /dev/null -w ''%{http_code}'' localhost:4444)" != '302' ]]; do sleep 5; done' || false

# Build front-end app
cd front && FRONT_ENVIRONMENT=test ./deploy.sh && cd ..

# Run tests
php vendor/bin/codecept run --steps "$1"
codecept_exit_status=$?

pkill -f 'selenium-standalone'

if [ -f .env.bak ]; then
  cp .env.bak .env
fi

# Remove backup file if default .env is in place
if [ -f .env.bak ]; then
  if grep -q "database.default.hostname" .env; then
    rm .env.bak
  fi
fi

# Exit with the status of the codecept command
exit $codecept_exit_status
