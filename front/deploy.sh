#!/bin/bash
set -o allexport

# Load the default .env file
if [ -f ".env" ]; then
  source .env
fi

# Override the environment file if FRONT_ENVIRONMENT is set
if [ -n "$FRONT_ENVIRONMENT" ]; then
  ENV_FILE=".env.$FRONT_ENVIRONMENT"
  if [ -f "$ENV_FILE" ]; then
    source $ENV_FILE
    echo "Using '$ENV_FILE' env file."
  else
    echo "Environment file '$ENV_FILE' not found. Using the default .env file."
  fi
else
  echo "Using the default .env file."
fi

set +o allexport

[ ! -d "dist" ] && mkdir "dist"

CONFIG_FILE=vite.config.js

if [ ! -f "$CONFIG_FILE" ]; then
  echo 'You must run the script from the "front" directory.'
  exit
fi

if [ -z "$VITE_BASE_PATH" ]; then
  BASE_PATH="/"
else
  BASE_PATH="$VITE_BASE_PATH"
fi

# Install the project dependencies using Yarn
yarn install

# Build the project using Yarn with the specified base path
yarn run build --base=$BASE_PATH

# Remove the existing front_end.html and front_assets directory
rm -rf ../app/Views/front_end.html ../public/front_assets

# Move the built index.html to the CI views directory and rename it to front_end.html
mv dist/index.html ../app/Views/front_end.html

# Copy the built front_assets directory to the public CI directory
cp -R dist/front_assets ../public
