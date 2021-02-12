php=php
perl=perl
composer=composer
phpcs=$(php) vendor/squizlabs/php_codesniffer/bin/phpcs
phpunit=$(php) vendor/phpunit/phpunit/phpunit
phpdoc=$(php) vendor/phpdocumentor/phpdocumentor/bin/phpdoc
phpdocmd=$(php) vendor/evert/phpdoc-md/bin/phpdocmd
yaml2json=$(perl) -MJSON -MYAML -eprint -e'to_json(YAML::Load(join""=><>),{pretty=>1,canonical=>1})'
getversion=$(perl) -MYAML -eprint -e'YAML::Load(join""=><>)->{version}'
V=`$(getversion) < composer.yaml`

all: | vendor test docs

info:
	@echo $(php)
	@$(php) -v
	@echo $(perl)
	@$(perl) -v

docs:
	if [ -d $@ ]; then git rm -f $@/*.md; else mkdir $@; fi
	$(phpdoc) -d src/ -t $@ --template=xml --visibility=public >phpdoc.out
	$(phpdocmd) docs/structure.xml docs/ > phpdocmd.out
	git add docs/*.md
	git clean -xdf docs

clean:
	git clean -xdf -e vendor

vendor: composer.json
	$(composer) --prefer-dist install >composer.out

composer.json: composer.yaml
	$(yaml2json) < $? > $@
	git add -v -- $@

test: lint
	$(phpcs) --warning-severity=0 --standard=PSR2 src
	$(phpunit) --verbose tests/

lint:
	for file in `find src tests -name '*.php' | sort`; do $(php) -l $$file || exit 1; done

archive: | clean composer.json
	$(composer) archive

release:
	git push --all
	git tag -m "Release version $V" -s v$V
	git push --tags

.PHONY: all info docs clean test archive release
