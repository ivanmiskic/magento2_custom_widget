# Contributing

## Local Magento

Install the module into a Magento Open Source 2.4.8 project (composer VCS or `app/code/Null/Blueprint`). Enable it with `mage83 module:enable Null_Blueprint && mage83 setup:upgrade` on this machine, or `php bin/magento` on a stock host.

## QA without Magento

```bash
composer install --working-dir=dev/qa
dev/qa/vendor/bin/phpunit --testsuite unit
dev/qa/vendor/bin/phpstan analyse
dev/qa/vendor/bin/phpcs --standard=.phpcs.xml
```

`Test/Integration` needs a Magento bootstrap. Run it from the Magento root after the module is installed:

```bash
vendor/bin/phpunit -c dev/tests/integration/phpunit.xml.dist \
  app/code/Null/Blueprint/Test/Integration
```

(or the equivalent path under `vendor/null/module-blueprint`).

## Code rules

- `declare(strict_types=1);` and constructor DI.
- No `ObjectManager::getInstance()` in module code.
- No `cacheable="false"` on public layout handles.
- New Magento patterns get a heading in `docs/PATTERNS.md`.
- PHP files use the Null_Blueprint OSL-3.0 header.
