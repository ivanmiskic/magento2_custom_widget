# Null_Blueprint

[![CI](https://github.com/ivanmiskic/magento2_custom_widget/actions/workflows/ci.yml/badge.svg)](https://github.com/ivanmiskic/magento2_custom_widget/actions/workflows/ci.yml)
[![Magento](https://img.shields.io/badge/Magento-2.4.8-orange)](https://developer.adobe.com/commerce/)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-OSL--3.0-blue)](LICENSE)

Magento Open Source **2.4.8** module cookbook. One real domain — **Campaign** — drives admin CRUD, a catalog products widget, storefront pages, REST, cron, CLI, email, a plugin, and an observer. Copy a pattern into your own `Null_*` module instead of starting from a 2016 gist.

This repository is a **2.0.0 rewrite** of the old `Inchoo_CatalogWidget` sample. There is no upgrade path for the `inchoo_products_list` widget id.

```
Admin / REST  →  CampaignRepository  →  MySQL
                                       ↓
Cron / CLI / Email                   Widget / storefront ViewModels → phtml
                                       ↓
                                 Product collection (conditions SQL)
```

## What a Campaign is

A merchandiser creates a dated campaign: title, identifier, stores, status, schedule, image, product conditions, sort, and product count. Active campaigns inside their schedule window show on `/campaigns/`, on `campaigns/index/view/id/{id}`, and in the **Blueprint Campaign Products** widget.

| Status | Value | Who sets it |
| --- | --- | --- |
| Draft | `0` | Admin |
| Active | `1` | Admin |
| Expired | `2` | Daily cron when `end_at` is past |

## Requirements

- Magento Open Source 2.4.8
- PHP 8.2 or 8.3

## Install

Repo root **is** the module (composer-style). `app/code/Null/Blueprint` is only the install destination.

### Composer (preferred)

```bash
composer config repositories.null-blueprint vcs https://github.com/ivanmiskic/magento2_custom_widget.git
composer require null/module-blueprint:dev-master
mage83 module:enable Null_Blueprint
mage83 setup:upgrade
mage83 cache:flush
```

Use `php bin/magento` instead of `mage83` when you are not on this machine's nspawn setup.

### Manual copy

```bash
git clone https://github.com/ivanmiskic/magento2_custom_widget.git app/code/Null/Blueprint
mage83 module:enable Null_Blueprint
mage83 setup:upgrade
mage83 cache:flush
```

## Use it

1. **Marketing → Blueprint Campaigns** — create a campaign. Paste a Magento widget condition tree, or leave conditions empty and use the widget in Custom mode.
2. **Content → Widgets** — insert **Blueprint Campaign Products**. Mode `campaign` reads the campaign; mode `custom` uses the visual condition builder (same as Magento Catalog Products List).
3. Storefront: `/campaigns/` and `/campaigns/index/view/id/{id}`.
4. Seed samples: `mage83 blueprint:campaign:seed`
5. Export: `mage83 blueprint:campaign:export`

REST (admin token):

| Method | Route |
| --- | --- |
| GET | `/V1/blueprint/campaigns` (visible campaigns for the current store) |
| GET | `/V1/blueprint/campaigns/:campaignId` |
| POST | `/V1/blueprint/campaigns` |
| PUT | `/V1/blueprint/campaigns/:campaignId` |
| DELETE | `/V1/blueprint/campaigns/:campaignId` |

## Copy a pattern

See [docs/PATTERNS.md](docs/PATTERNS.md). Each heading is one Magento 2 pattern: when to copy it, which files, five-line recipe.

Architecture map: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## File map

| Path | Role |
| --- | --- |
| `Api/` | Service contracts |
| `Model/CampaignRepository.php` | Persist + events |
| `etc/db_schema.xml` | Tables |
| `etc/widget.xml` | Campaign products widget |
| `view/adminhtml/ui_component/` | Grid + form |
| `ViewModel/` | Storefront getters |
| `Plugin/Catalog/Block/Product/AbstractProductPlugin.php` | Campaign badge |
| `Cron/ExpireCampaigns.php` | Nightly expiry |
| `Console/Command/` | Seed + export |

## LESS / Grunt

This module ships `view/frontend/web/css/source/_module.less`. After you change it in a project that uses Grunt, compile the **consuming theme** listed in that project's `dev/tools/grunt/configs/themes.js`. Do not add `cacheable="false"` on public catalog or CMS handles.

## Breaking changes

`Inchoo_CatalogWidget` and widget id `inchoo_products_list` are gone. CMS widgets that referenced them must be recreated as `null_blueprint_campaign_products`.

## Quality

```bash
composer install --working-dir=dev/qa
dev/qa/vendor/bin/phpunit --testsuite unit
dev/qa/vendor/bin/phpstan analyse
dev/qa/vendor/bin/phpcs --standard=.phpcs.xml
```

Integration tests under `Test/Integration` need a Magento 2.4.8 install. They are not run in GitHub Actions.

## License

[OSL-3.0](LICENSE)
