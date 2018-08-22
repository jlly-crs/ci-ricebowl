#!/bin/bash

set -ex

apt-get update

composer global require -n "hirak/prestissimo:^0.3"

/usr/bin/env COMPOSER_BIN_DIR=$HOME/bin composer --working-dir=$HOME require pantheon-systems/terminus "^1"

echo 'export PATH=$PATH:$HOME/bin:$HOME/terminus/bin' >> $BASH_ENV
echo 'export CIRCLE_ENV=ci-$CIRCLE_BUILD_NUM' >> $BASH_ENV
source $BASH_ENV

set +ex

mkdir $HOME/.ssh
eval $(ssh-agent -s)
ssh-add <(echo $SSH_PRIVATE_KEY | base64 --decode)
echo "StrictHostKeyChecking no" >> "$HOME/.ssh/config"
