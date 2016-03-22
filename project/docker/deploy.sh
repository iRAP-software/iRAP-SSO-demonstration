#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

# Setup for relative paths.
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 
cd $SCRIPTPATH

# load the variables
source ../../settings/docker_settings.sh

# Settings
PROJECT_NAME="sso-example"

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 
cd $SCRIPTPATH

CONTAINER_IMAGE="`echo $PROJECT_NAME`"

docker kill $PROJECT_NAME
docker rm $PROJECT_NAME

docker run -d \
$NETWORK_BIND_1 \
--restart=always \
--name="$PROJECT_NAME" \
$CONTAINER_IMAGE
