#!/bin/bash

cd -- "$(dirname -- "$0")"

echo "# run lightweight tests"
make -s test || exit 1
echo

tag="libcurl-objcurl-php"

echo "# build $tag"
docker build -t "$tag" . || exit 1

echo
sleep 1
echo

echo "# run $tag"
container=$(docker run -d -P "$tag" || exit 1)

echo
sleep 1
echo

echo "# get port"
export TEST_URL="$(docker port "$container" 80/tcp | sed -nr 's|^([0-9a-f:]+):([0-9]+)$|http://[\1]:\2|p')"

echo "URL=$TEST_URL"
sleep 1
echo

echo "# run full tests"
make -s test

echo
sleep 1
echo

echo "# stop container"
docker container stop "$container"
echo
