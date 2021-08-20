php=php
perl=perl
composer=composer
phpcs=$(php) vendor/bin/phpcs
phpunit=$(php) vendor/bin/phpunit
yaml2json=$(perl) -MJSON -MYAML -eprint -e'to_json(YAML::Load(join""=><>),{pretty=>1,canonical=>1})'

all: | vendor test

clean:
	git clean -xdf -e vendor

vendor: composer.json
	@echo " --> $@"
	$(composer) --prefer-dist install >composer.out

composer.json: composer.yaml
	@echo " --> $@"
	$(yaml2json) < $? > $@
	git add -v -- $@

test: lint
	@echo " --> $@"
	$(phpcs) --warning-severity=0 --standard=PSR2 src
	$(phpunit) --color=always --verbose tests/

.lint/%.php: %.php
	@echo " --> $@"
	mkdir -p -- "`dirname -- "$@"`"
	$(php) -l "$?"
	touch $@

lint:
	@echo " --> $@"
	find src tests -name '*.php' -print0 | sort -zuV | sed -zr 's|^|.lint/|' | xargs -0 -r -- $(MAKE) $(MFLAGS) --

.PHONY: all clean test lint
