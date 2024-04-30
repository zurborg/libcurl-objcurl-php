FROM	php:7.4-apache

RUN	apt-get update && apt-get install -y libicu-dev libzip-dev
RUN	docker-php-ext-install -j$(nproc) intl zip

WORKDIR	/tmp/

COPY	composer.phar /tmp/
COPY	composer.json /tmp/
RUN	php composer.phar --prefer-dist install
COPY	tests/run.sh /tmp/run.sh
COPY	tests/server/ /var/www/html/
COPY	tests/Curl/ /tmp/tests/Curl/
COPY	src/ /tmp/src/

CMD	["/tmp/run.sh"]
