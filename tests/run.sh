#!/bin/bash

apache2-foreground &

cd /tmp/

sleep 1

env TEST_URL=http://localhost/ php vendor/phpunit/phpunit/phpunit --verbose tests/
exit=$?

kill %1

wait

exit $exit
