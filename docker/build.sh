#!/usr/bin/env bash
#
# Build a production docker image of the app
#

# Bail out on first error
set -e

apt-get install -y git

# Get the directory of the build script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
mkdir "$DIR/packaged"

# Get the current git commit sha
HASH=$(git rev-parse HEAD)

# Package the app
cd $DIR/../
# "archive" gives us useful tools - we can use .gitattributes
# to `export-ignore` extraneous files
git archive --format=tar --worktree-attributes $HASH | tar -xf - -C $DIR/packaged

# Production Build Steps
## (Decision between export-ignore'ing docker/develop command or not)
cd $DIR/packaged
composer install --no-dev
#npm install && npm run prod && rm -fr node_modules

# Get the production .env file
## This assumes we're running in Jenkins as user "jenkins"
#/var/lib/jenkins/.venv/bin/aws s3 cp s3://shippingdocker-secrets/env-prod .env
cp .env.example .env
php artisan key:generate

# Build the Docker image with latest code
cd $DIR/packaged
cp ../Dockerfile ./
docker build \
    -t versions.eng.gla.ac.uk:5555//billy/glasgow_projects:latest \
    -t versions.eng.gla.ac.uk:5555//billy/glasgow_projects:$HASH .
docker push versions.eng.gla.ac.uk:5555//billy/glasgow_projects:$HASH
docker push versions.eng.gla.ac.uk:5555//billy/glasgow_projects:latest

# Clean up packaged directory
cd $DIR/packaged
PWD=$(pwd)
if [ "$PWD" == "$DIR/packaged" ]; then
    rm -rf ./* && rm -rf ./.git* && rm .env* && rm .dockerignore
    touch .gitkeep
fi
