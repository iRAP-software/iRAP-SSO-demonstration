#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

# Settings
PROJECT_NAME="sso-example"

SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT") 
cd $SCRIPTPATH

CONTAINER_IMAGE="`echo $PROJECT_NAME`"

docker kill $PROJECT_NAME
docker rm $PROJECT_NAME

docker run -d \
-p 80:80 \
--restart=always \
--name="$PROJECT_NAME" \
$CONTAINER_IMAGE

