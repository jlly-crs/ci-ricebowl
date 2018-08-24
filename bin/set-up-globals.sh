#!/bin/bash

set -ex

apt-get update
apt-get install -y gpg libfontconfig
curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
apt-get install -y nodejs

npm config set user 0
npm config set unsafe-perm true
composer global require -n "hirak/prestissimo:^0.3"

npm install -g backstopjs
composer install
/usr/bin/env COMPOSER_BIN_DIR=$HOME/bin composer --working-dir=$HOME require pantheon-systems/terminus "^1"

echo 'export PATH=$PATH:$HOME/bin:$HOME/terminus/bin' >> $BASH_ENV
echo 'export CIRCLE_ENV=ci-$CIRCLE_BUILD_NUM' >> $BASH_ENV
source $BASH_ENV

set +ex

mkdir $HOME/.ssh
echo "StrictHostKeyChecking no" >> "$HOME/.ssh/config"
