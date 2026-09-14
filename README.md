# Null_Blueprint

[![CI](https://github.com/ivanmiskic/magento2-agentic-blueprint/actions/workflows/ci.yml/badge.svg)](https://github.com/ivanmiskic/magento2-agentic-blueprint/actions/workflows/ci.yml)
[![Magento](https://img.shields.io/badge/Magento-2.4.8-orange)](https://developer.adobe.com/commerce/)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-OSL--3.0-blue)](LICENSE)

Agentic Magento Open Source **2.4.8** template. Point an AI coding agent (or a human) at this repo when generating a `Null_*` module. The code is a real Campaign feature so every file exists for a reason — not a junk drawer of disconnected snippets.

**What this repo is for:** copy a current Magento 2 pattern without the defaults that wreck production stores — `cacheable="false"` on public pages, `ObjectManager` in module code, unbounded product collections, helpers in phtml, `InstallSchema`, and fat blocks that do SQL.

## How an agent should use it

1. Read [docs/PATTERNS.md](docs/PATTERNS.md) first. Each heading is one pattern: when to copy, which files, five-line recipe.
2. Copy **one** pattern into the target `Null_*` module. Do not paste the Campaign domain unless the task is campaigns.
3. Keep the hard rules below. If a generated file violates one, it is wrong even if it “works” locally.
4. Use [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) only when you need the data flow, not as a license to add GraphQL, Hyvä, message queues, or MSI.

## Hard rules (do not generate these)

| Do not | Do this instead |
| --- | --- |
| `cacheable="false"` on `default`, CMS, catalog, search, home, or any widget nested on those pages | One such flag makes the **entire page** uncacheable for FPC/Varnish. There is no “only this block” bypass. Use cache identities, AJAX + empty shell, or `customer-data` for private bits. |
| `$block->setCacheable(false)` / `setData('cacheable', false)` in `_prepareLayout()` | Same FPC kill. Guest vs logged-in UI is not a reason. |
| `ObjectManager::getInstance()` in module code | Constructor DI. Factories only where Magento requires them. |
| `$block->helper()` / business logic in phtml | ViewModel getters; escape in the template. |
| `InstallSchema` / `setup_version` | `etc/db_schema.xml` + whitelist. |
| Empty product conditions → whole catalog | Empty conditions → empty collection. |
| Plugin on `AbstractBlock::_toHtml` “just in case” | Smallest `after*` on the method that already returns the HTML or data you need. |
| Repository `load()` from a controller | `CampaignRepository` (or the target module’s repository). |
| Anonymous storefront REST for merchandising data | Blocks / ViewModels. REST here is ACL-gated. |

`cacheable="false"` is allowed only when the **whole page** is private: account, checkout, RMA, job forms, email handles.

## The sample domain

One entity — **Campaign** — exercises the patterns an agent actually needs: service contracts, declarative schema, UI component grid/form, widget + conditions, storefront ViewModels, REST, cron, CLI, email, one narrow plugin, one observer.

```
Admin / REST  →  CampaignRepository  →  MySQL
                                       ↓
Cron / CLI / Email                   Widget / storefront ViewModels → phtml
                                       ↓
                                 Product collection (conditions SQL)
```

Merchandiser fields: title, identifier, stores, status, schedule, image, product conditions, sort, product count.

| Status | Value | Who sets it |
| --- | --- | --- |
| Draft | `0` | Admin |
| Active | `1` | Admin |
| Expired | `2` | Daily cron when `end_at` is past |

Storefront: `/campaigns/` and `/campaigns/index/view/id/{id}`. Widget: **Blueprint Campaign Products** (`campaign` mode or `custom` conditions).

## Requirements

- Magento Open Source 2.4.8
- PHP 8.2 or 8.3

## Install

Repo root **is** the module. `app/code/Null/Blueprint` is only the install destination.

### Composer

```bash
composer config repositories.null-blueprint vcs https://github.com/ivanmiskic/magento2-agentic-blueprint.git
composer require null/module-blueprint:dev-master
php bin/magento module:enable Null_Blueprint
php bin/magento setup:upgrade
php bin/magento cache:flush
```

On this machine’s nspawn Magento hosts, use `mage83` in place of `php bin/magento`.

### Manual copy

```bash
git clone https://github.com/ivanmiskic/magento2-agentic-blueprint.git app/code/Null/Blueprint
php bin/magento module:enable Null_Blueprint
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Try the sample

1. **Marketing → Blueprint Campaigns** — create a campaign. Paste a widget condition tree, or leave conditions empty and use the widget in Custom mode.
2. **Content → Widgets** — **Blueprint Campaign Products**.
3. Seed: `php bin/magento blueprint:campaign:seed`
4. Export: `php bin/magento blueprint:campaign:export`

REST (admin / integration token, not anonymous):

| Method | Route |
| --- | --- |
| GET | `/V1/blueprint/campaigns` (visible for the current store) |
| GET | `/V1/blueprint/campaigns/:campaignId` |
| POST | `/V1/blueprint/campaigns` |
| PUT | `/V1/blueprint/campaigns/:campaignId` |
| DELETE | `/V1/blueprint/campaigns/:campaignId` |

## Pattern index

| Need | Start here |
| --- | --- |
| CMS/widget product block | `etc/widget.xml`, `Block/Widget/CampaignProducts.php` |
| Admin grid / form | `view/adminhtml/ui_component/` |
| Persist an entity | `Api/`, `Model/CampaignRepository.php`, `etc/db_schema.xml` |
| Storefront data | `ViewModel/` + layout `view_model` argument |
| Narrow catalog plugin | `Plugin/Catalog/Block/Product/AbstractProductPlugin.php` |
| Save-side cache flush | `Observer/InvalidateCampaignCache.php` |
| Cron / CLI / email / REST / config / ACL | matching headings in [docs/PATTERNS.md](docs/PATTERNS.md) |

LESS lives in `view/frontend/web/css/source/_module.less`. Compile the **consuming theme** in that project’s `dev/tools/grunt/configs/themes.js`.

## Quality

```bash
composer install --working-dir=dev/qa
dev/qa/vendor/bin/phpunit --testsuite unit
dev/qa/vendor/bin/phpstan analyse
dev/qa/vendor/bin/phpcs --standard=.phpcs.xml
```

`Test/Integration` needs a Magento 2.4.8 install. GitHub Actions runs unit tests, PHPStan, and PHPCS only.

## License

[OSL-3.0](LICENSE)
