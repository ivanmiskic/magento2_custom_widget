# Null_Blueprint — Magento 2.4.8 module cookbook

Date: 2026-09-06
Status: implemented on branch feature/null-blueprint-2.0
Repo: https://github.com/ivanmiskic/magento2_custom_widget
Module: `Null_Blueprint`
Composer: `null/module-blueprint`

## 1. Problem

The current repository is a 2016–2017 `Inchoo_CatalogWidget` sample: eight source files, `setup_version` in `module.xml`, untyped PHP, a phtml that calls helpers, a 272-byte README, no composer package, no license, no tests. It is not a usable template for Magento Open Source 2.4.8 or for AI-assisted module generation.

This rewrite turns the same GitHub repo into a professional, composer-installable Magento 2.4.8 Open Source module that is both a real feature and a copyable blueprint.

## 2. Goals

- Ship one coherent domain (Campaign) that merchandisers can use.
- Teach every common Magento 2 module pattern through that domain, indexed in `docs/PATTERNS.md` for humans and AI.
- Meet Magento 2.4.8 Open Source coding standards (PHP 8.2+, declarative schema, UI components, repositories, ViewModels).
- Make the GitHub repository look like a maintained public module: README, CI, license, changelog, contributing, issue/PR templates.

## 3. Non-goals (v1)

- GraphQL (REST only).
- Adobe Commerce / B2B / staging-specific APIs.
- Hyvä / headless storefront variants.
- Message queues, MSI inventory APIs, import/export framework, Page Builder content types.
- Backward compatibility with `Inchoo_CatalogWidget` widget IDs or class names. This is a 2.0.0 rewrite, not an upgrade path.
- Publishing to Packagist in this work (composer.json must be Packagist-ready; publish is a later step).

## 4. Target platform

| Item | Value |
| --- | --- |
| Magento | Open Source 2.4.8 |
| PHP | 8.2 or 8.3 |
| License | OSL-3.0 |
| Install layout | Repo root = module root (composer-style) |
| Manual install path | Copy/clone into `app/code/Null/Blueprint` |
| Composer install path | `vendor/null/module-blueprint` |

`app/code/Null/Blueprint` is documented as the **destination**, not the GitHub tree. The repository root contains `composer.json`, `registration.php`, `etc/`, and the rest of the module.

## 5. Architecture

One Magento module. One bounded context: Campaign.

A merchandiser creates a dated campaign (title, stores, status, schedule, image, product conditions). That entity drives admin CRUD, the storefront widget (the modern replacement of the 2016 Inchoo product widget), campaign list/detail pages, REST, cron expiry, CLI seed/export, email on expire, system config, one catalog plugin, and one save observer.

```
Admin / REST  →  CampaignRepository  →  Resource / DB
                                         ↓
Cron / CLI / Email                     Widget / Storefront ViewModels → phtml
                                         ↓
                                   Product collection (conditions SQL)
```

No `ObjectManager::getInstance()` in module code. Factories and proxies only where Magento requires them. All PHP files declare `declare(strict_types=1);` and use constructor DI.

## 6. Domain model

### 6.1 Table `null_blueprint_campaign`

| Column | Type | Notes |
| --- | --- | --- |
| `campaign_id` | int identity PK | |
| `identifier` | varchar(64) unique | URL key, `[a-z0-9-]` |
| `title` | varchar(255) | required |
| `description` | text | WYSIWYG, nullable |
| `image` | varchar(255) | media relative path, nullable |
| `status` | smallint | `0` = draft, `1` = active, `2` = expired |
| `start_at` | datetime nullable | inclusive |
| `end_at` | datetime nullable | inclusive end of day in store TZ when set from admin date |
| `conditions_serialized` | mediumtext | Magento widget condition tree |
| `sort_by` | varchar(32) | `name` \| `price` \| `position` \| `created_at` |
| `sort_order` | varchar(4) | `asc` \| `desc` |
| `products_count` | int | default `10` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

Indexes: unique `identifier`, index `status`, index `(start_at, end_at)`.

### 6.2 Table `null_blueprint_campaign_store`

| Column | Type |
| --- | --- |
| `campaign_id` | int FK cascade delete |
| `store_id` | smallint |

Primary key `(campaign_id, store_id)`. Store `0` means all stores.

### 6.3 Status rules

- Admin may save draft or active.
- Cron sets `status = 2` when `end_at` is in the past and status is active.
- Storefront, widget, and REST list endpoints return only campaigns that are active and inside `[start_at, end_at]` for the current store (null dates mean open-ended).
- Identifier is required and unique globally.

### 6.4 Interfaces

- `Api\Data\CampaignInterface`
- `Api\Data\CampaignSearchResultsInterface`
- `Api\CampaignRepositoryInterface` — `getById`, `getByIdentifier`, `getList`, `save`, `delete`, `deleteById`

Implementation lives under `Model/`. No service contracts for widget-only helpers.

## 7. Feature inventory

Every item below is in scope for v1. File paths are the implementation contract.

### 7.1 Module skeleton

- `registration.php` — `Null_Blueprint`
- `etc/module.xml` — no `setup_version`; sequence: `Magento_Catalog`, `Magento_CatalogWidget`, `Magento_Widget`, `Magento_Cms`, `Magento_Store`
- `composer.json` — `null/module-blueprint`, `type: magento2-module`, require `php: ~8.2.0||~8.3.0`, `magento/framework`, `magento/module-catalog`, `magento/module-catalog-widget`, `magento/module-widget`
- `etc/di.xml` — repository preference, logger, virtual types only if needed
- `etc/acl.xml`, `etc/adminhtml/menu.xml`, `etc/adminhtml/routes.xml`, `etc/adminhtml/system.xml`, `etc/config.xml`
- `etc/frontend/routes.xml` — frontName `campaigns`
- `etc/db_schema.xml`, `etc/db_schema_whitelist.json` (generated)
- `etc/events.xml`, `etc/crontab.xml`, `etc/email_templates.xml`
- `etc/webapi.xml`
- `i18n/en_US.csv`

### 7.2 Admin (UI components)

- Menu: **Marketing → Blueprint Campaigns** (`Null_Blueprint::campaign`)
- ACL resources: `Null_Blueprint::campaign` (view), `::campaign_save`, `::campaign_delete`, `::config`
- Grid: `view/adminhtml/ui_component/null_blueprint_campaign_listing.xml` — columns, filters, mass delete, mass status, inline status
- Form: `view/adminhtml/ui_component/null_blueprint_campaign_form.xml` — general, schedule, store view, image uploader, WYSIWYG description, product conditions, sort + count
- Controllers under `Controller/Adminhtml/Campaign/`: `Index`, `NewAction`, `Edit`, `Save`, `Delete`, `MassDelete`, `MassStatus`, `InlineEdit`, `Upload` (image)
- Layouts: `blueprint_campaign_index.xml`, `blueprint_campaign_edit.xml`, `blueprint_campaign_new.xml`
- Admin route frontName: `blueprint`

### 7.3 Widget (replaces Inchoo catalog widget)

Widget id: `null_blueprint_campaign_products`  
Class: `Block/Widget/CampaignProducts.php`  
Placeholder: `view/adminhtml/web/images/widget-campaign.png`

Parameters:

- `mode` — `campaign` (select existing campaign) or `custom` (inline conditions). Default `campaign`.
- `campaign_id` — visible when `mode=campaign`; source model lists active campaigns.
- `title` — optional override.
- `condition` — Magento conditions type; visible when `mode=custom`.
- `collection_sort_by` / `collection_sort_order` — source models; used in custom mode, ignored when a campaign supplies them.
- `products_count`, `show_pager`, `products_per_page`, `cache_lifetime`
- `template` — `grid` (`Null_Blueprint::widget/campaign/grid.phtml`) or `highlight` (`Null_Blueprint::widget/campaign/highlight.phtml`)

Containers: `content`, `content.top`, `content.bottom`.

The block extends Magento’s catalog widget products list only where condition SQL is required. Collection building is overridden to apply campaign or custom conditions, sort, and visibility. Identities include campaign id + product ids for FPC.

### 7.4 Storefront

- `campaigns/` — list of currently visible campaigns (`Controller/Index/Index`, `ViewModel/CampaignList`, `view/frontend/templates/campaign/list.phtml`)
- `campaigns/view/id/{id}` — detail (`Controller/Index/View`). Identifier is a unique key and REST field only; no custom router or URL rewrite in v1.
- Detail page renders campaign content + the same product listing ViewModel the widget uses
- Empty states: dedicated templates, not blank output
- Layout XML + `view/frontend/web/css/source/_module.less` (Luma/Blank). After LESS changes, document that a project using Grunt should compile the consuming theme (`dev/tools/grunt/configs/themes.js`) — this module only ships `_module.less`
- No business logic in phtml. Escape everything. No `$block->helper()` calls.

### 7.5 Catalog plugin

`Plugin/Catalog/Block/Product/AbstractProductPlugin.php` implements `afterGetProductPriceHtml`. When `Model/CampaignBadgeResolver` reports the product is in an active visible campaign, the plugin prepends a small badge HTML string (escaped campaign title). The resolver loads matching campaign product ids once per request and caches them in a private property. This is the module’s one plugin example; it must stay this narrow.

### 7.6 Observer

`Observer/InvalidateCampaignCache.php` on a custom event `null_blueprint_campaign_save_after` dispatched from the repository after successful save/delete. Flushes campaign block tags and the widget cache tag.

### 7.7 Cron

`Cron/ExpireCampaigns.php` daily (`0 1 * * *`). Finds active campaigns with `end_at < now`, sets expired, dispatches save event, sends the expire email if a recipient is configured.

### 7.8 CLI

- `blueprint:campaign:seed` — creates two sample campaigns (one active, one draft) if identifiers `sample-active` and `sample-draft` are free. `--force` updates them.
- `blueprint:campaign:export` — writes `var/export/null_blueprint_campaigns.json` (or `--file=`).

### 7.9 Email

Template `view/frontend/email/campaign_expired.html`. Sent from cron to `blueprint/general/notification_email` (fallback: general store contact). Identity: general.

### 7.10 System config

Section `blueprint` under Stores → Configuration → Null → Blueprint:

- `general/enabled` — module storefront + widget output (admin CRUD always on)
- `general/default_products_count` — `10`
- `general/notification_email`
- `general/list_enabled` — hide storefront list route when `0`

`etc/config.xml` supplies those defaults.

### 7.11 REST

| Method | Route | ACL |
| --- | --- | --- |
| GET | `/V1/blueprint/campaigns` | `Null_Blueprint::campaign` |
| GET | `/V1/blueprint/campaigns/:campaignId` | `Null_Blueprint::campaign` |
| POST | `/V1/blueprint/campaigns` | `Null_Blueprint::campaign_save` |
| PUT | `/V1/blueprint/campaigns/:campaignId` | `Null_Blueprint::campaign_save` |
| DELETE | `/V1/blueprint/campaigns/:campaignId` | `Null_Blueprint::campaign_delete` |

SearchCriteria on GET list. No anonymous storefront REST in v1 (admin token / integration). Storefront uses blocks, not a public API.

### 7.12 Quality / repo

- `README.md` — badges (CI, Magento 2.4.8, PHP 8.2+, OSL-3.0), screenshots or SVG diagram, both install paths, enable/upgrade commands, what a Campaign is, file map, “copy a pattern into your Null_* module”
- `docs/PATTERNS.md` — one heading per pattern (Widget, UI grid, UI form, Repository, Plugin, Observer, Cron, CLI, REST, Email, db_schema, ViewModel, System config, ACL). Each heading: when to copy, file paths, five-line recipe
- `docs/ARCHITECTURE.md` — short module map (this spec’s architecture condensed)
- `CHANGELOG.md` — Keep a Changelog; `2.0.0` rewrite notes
- `CONTRIBUTING.md`, `LICENSE` (OSL-3.0), `SECURITY.md`
- `.github/workflows/ci.yml` — PHP 8.2 + 8.3, phpcs Magento2, phpstan, unit tests
- `.github/ISSUE_TEMPLATE`, `pull_request_template.md`
- `.gitignore` — vendor, generated, `.idea`, OS junk
- Code comments: Magento docblocks + a single `PATTERN:` tag on the primary file for that pattern. No tutorial essays in every class.

## 8. Data flow

### Write

1. Admin form or REST POST/PUT.
2. Controller/API validates required fields (title, identifier, status).
3. `CampaignRepository::save()` persists campaign + store records.
4. Repository dispatches `null_blueprint_campaign_save_after`.
5. Observer invalidates FPC/block tags for that campaign.

### Read

1. Widget / storefront / REST GET.
2. Repository or collection + SearchCriteria + store filter + visibility window.
3. ViewModel exposes typed getters (`getTitle()`, `getItems()`, `isEmpty()`).
4. phtml escapes and renders. Empty collection uses the empty template.

### Product collection

Same idea as the 2016 widget, current APIs: visibility, store filter, prices, condition SQL via `Magento\CatalogWidget` condition combiner + `Sql\Builder`, then sort + page size.

### Expire

Cron loads due campaigns, sets expired, `save()`, optional email.

## 9. Errors

- `getById` / `getByIdentifier`: `NoSuchEntityException`
- `save` / `delete`: `CouldNotSaveException` / `CouldNotDeleteException` with previous exception logged
- Duplicate identifier: `CouldNotSaveException` with a clear phrase: `Campaign identifier "%1" already exists.`
- Admin: Magento UI validation + session error messages; never `window.alert`
- REST: standard Magento error envelope
- Widget/storefront: no exception leak; log + empty state if a campaign is missing or disabled
- Config `general/enabled = 0`: widget and frontend controllers render empty/404, admin still works

## 10. Testing

- Unit (always in CI): `CampaignRepository` (mocked resource), `ViewModel/CampaignList`, source models (`SortBy`, `SortOrder`, `Status`), `Cron/ExpireCampaigns` (mocked repo + clock)
- Integration: `CampaignRepositorySaveTest` lives under `Test/Integration` and is **not** run in GitHub Actions. CONTRIBUTING says to run it inside a Magento 2.4.8 install.
- CI: PHP 8.2 and 8.3 jobs run phpcs (`Magento2`), phpstan level 6, and PHPUnit unit tests only.

## 11. Code standards (locked)

- Namespace `Null\Blueprint\...`
- Magento 2 coding standard: copyright header optional (repo has no corporate copyright; use SPDX `OSL-3.0` in file headers)
- Prefer `db_schema.xml` over Install/UpgradeSchema
- Prefer ViewModels over Block methods for presentation
- Prefer repositories over loading models in controllers
- Admin grids/forms are UI components, not the old Form/Grid blocks
- phtml uses `$escaper` / `$block->escapeHtml` consistently
- LESS only under `view/frontend/web/css/source/_module.less` (and admin if the form needs a small tweak)

## 12. Replacement of the 2016 module

| Old | New |
| --- | --- |
| `Inchoo_CatalogWidget` | `Null_Blueprint` |
| Widget `inchoo_products_list` | `null_blueprint_campaign_products` |
| `Block/Product/ProductsList.php` | `Block/Widget/CampaignProducts.php` |
| Hardcoded sort on collection | Campaign fields + widget overrides |
| `top_products.phtml` + helpers | `highlight.phtml` + ViewModel |
| `setup_version` | declarative schema |
| README 6 lines | professional docs set |

Existing CMS widgets that referenced `inchoo_products_list` will not migrate. README states that in a “Breaking changes” section.

## 13. Success criteria

- `composer validate` succeeds.
- Module enables on Magento Open Source 2.4.8: `module:enable Null_Blueprint && setup:upgrade`.
- Admin can create a campaign with conditions and see it on the grid.
- Widget can be inserted on a CMS page in both modes and render products.
- Storefront list/detail work; expired campaigns disappear after cron.
- REST list/get/save/delete work with an admin token.
- README + `docs/PATTERNS.md` let someone (or an AI) copy one pattern into a new `Null_*` module without reading every class.
- GitHub repo has license, CI badge, changelog, and a file tree that looks like a maintained Magento module.

## 14. Implementation order (for the later plan)

1. Skeleton: composer, registration, module.xml, acl, menu
2. db_schema + models + repository
3. Admin grid/form
4. Widget + templates
5. Storefront list/detail
6. Plugin badge + observer cache + cron + email + CLI + config
7. REST
8. Tests + CI
9. Docs, README, GitHub polish
10. Remove leftover `Inchoo_*` files
