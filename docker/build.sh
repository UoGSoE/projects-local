#!/usr/bin/env bash
#
# Build a production docker image of the app
#

# Bail out on first error
set -e

# Get the directory of the build script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Get the current git commit sha
HASH=$(git rev-parse HEAD)

# Package the app
cd $DIR/../
# "archive" gives us useful tools - we can use .gitattributes
# to `export-ignore` extraneous files
git archive --format=tar --worktree-attributes $HASH | tar -xf -  -C $DIR/app/packaged

# Production Build Steps
## (Decision between export-ignore'ing docker/develop command or not)
cd $DIR/app/packaged
./develop composer install --no-dev
# ./develop yarn install
# ./develop gulp --production

# Get the production .env file
## This assumes we're running in Jenkins as user "jenkins"
/var/lib/jenkins/.venv/bin/aws s3 cp s3://shippingdocker-secrets/env-prod .env

# Build the Docker image with latest code
cd $DIR/app
docker build \
    -t shippingdocker.com/app:latest \
    -t shippingdocker.com/app:$HASH .

# Clean up packaged directory
cd $DIR/app/packaged
PWD=$(pwd)
if [ "$PWD" == "$DIR/app/packaged" ]; then
    # The "vendor" directory (any any built assets!) will be owned 
    # as user "root" on the Linux file system
    # So we'll use Docker to delete them with a one-off container
    docker run --rm -w /opt -v $(pwd):/opt ubuntu:16.04 bash -c "rm -rf ./* && rm -rf ./.git* && rm .env*"
    touch .gitkeep
fi
