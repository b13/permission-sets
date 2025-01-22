
- run ``php -d memory_limit=2G .Build/bin/phpstan analyse -c Build/phpstan.neon``
- run ``php -d memory_limit=2G .Build/bin/php-cs-fixer fix --config=Build/php-cs-fixer.php --dry-run --stop-on-violation --using-cache=no``
- run ``php -d memory_limit=2G .Build/bin/phpunit -c Build/phpunit/FunctionalTests.xml Tests/Functional``