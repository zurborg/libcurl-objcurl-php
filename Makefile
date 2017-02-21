php=php
perl=perl
composer=$(php) composer.phar
phpcs=$(php) vendor/squizlabs/php_codesniffer/scripts/phpcs
phpunit=$(php) vendor/phpunit/phpunit/phpunit
phpdoc=$(php) vendor/phpdocumentor/phpdocumentor/bin/phpdoc
phpdocmd=$(php) vendor/evert/phpdoc-md/bin/phpdocmd
yaml2json=$(perl) -MJSON -MYAML -eprint -e'encode_json(YAML::Load(join""=><>))'
getversion=$(perl) -MYAML -eprint -e'YAML::Load(join""=><>)->{version}'
V=`$(getversion) < composer.yaml`

all: | vendor test documentation

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
	git clean -xdf -e composer.phar -e vendor

vendor: composer.json
	$(composer) --prefer-dist install >composer.out

composer.json: composer.yaml
	$(yaml2json) < $< > $@~
	mv $@~ $@
	-rm composer.lock
	git add $@

test:
	$(phpcs) --warning-severity=0 --standard=PSR2 src
	$(phpunit) --verbose tests/

archive: | clean composer.json
	$(composer) archive

release:
	git push --all
	git tag -m "Release version $V" -s v$V
	git push --tags

.PHONY: all info docs clean test archive release
