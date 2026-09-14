# Null_Blueprint Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the 2016 `Inchoo_CatalogWidget` sample with `Null_Blueprint`, a Magento Open Source 2.4.8 Campaign module that is both a working feature and an AI-copyable cookbook.

**Architecture:** One composer-style Magento module at repo root. Campaign is the only domain. Admin/REST write through `CampaignRepository`; storefront/widget/cron read visibility-filtered collections. Shared `ProductCollectionBuilder` turns serialized Magento conditions into a product collection.

**Tech Stack:** PHP 8.2/8.3, Magento Open Source 2.4.8, declarative schema, UI components, REST (no GraphQL), PHPUnit + phpcs Magento2 + phpstan.

## Global Constraints

- Module name `Null_Blueprint`, composer `null/module-blueprint`, namespace `Null\Blueprint\`
- `declare(strict_types=1);` and constructor DI on every PHP class
- No `ObjectManager::getInstance()` in module code
- No `setup_version` in `module.xml`
- SPDX / OSL-3.0 headers; author Ivan Miskic (same style as Null_Slider)
- Empty product conditions yield an empty product collection (never the whole catalog)
- REST list is visibility-filtered; admin grid is not
- Detail URL is only `campaigns/view/id/{id}`
- CI unit suite runs only Magento-free classes; Magento-dependent tests live in `unit-magento`

---

### Task 1: Module skeleton and schema

**Files:** `registration.php`, `composer.json`, `etc/module.xml`, `etc/acl.xml`, `etc/db_schema.xml`, `etc/db_schema_whitelist.json`, `etc/config.xml`, `etc/adminhtml/menu.xml`, `etc/adminhtml/routes.xml`, `etc/adminhtml/system.xml`, `etc/frontend/routes.xml`, `etc/di.xml`, `.gitignore`

- [ ] Write composer-style module root and declarative schema for `null_blueprint_campaign` + `null_blueprint_campaign_store`
- [ ] Remove leftover `Inchoo_*` files after new module registers

### Task 2: Domain layer

**Files:** `Api/*`, `Model/Campaign.php`, `Model/CampaignRepository.php`, `Model/CampaignVisibility.php`, `Model/Campaign/IdentifierValidator.php`, `Model/Config.php`, `Model/ResourceModel/Campaign.php`, `Model/ResourceModel/Campaign/Collection.php`, `Model/ResourceModel/Campaign/Grid/Collection.php`, `Model/Config/Source/*`

- [ ] Campaign interface constants, repository, store link persistence, visibility + identifier validators
- [ ] Unit tests for `CampaignVisibility` and `IdentifierValidator`

### Task 3: Admin CRUD

**Files:** admin controllers, UI listing/form, DataProvider, buttons, image upload, layouts

- [ ] Marketing → Blueprint Campaigns grid and form (WYSIWYG, image, stores, schedule, conditions textarea, sort/count)

### Task 4: Widget and storefront

**Files:** `etc/widget.xml`, `Block/Widget/CampaignProducts.php`, `Model/ProductCollectionBuilder.php`, ViewModels, frontend layouts/templates/LESS, widget placeholder image

- [ ] Widget modes `campaign` / `custom`; list + detail pages; empty states; FPC identities

### Task 5: Plugin, observer, cron, email, CLI, REST

**Files:** plugin, observer, cron, mail, console commands, `etc/webapi.xml`, `etc/events.xml`, `etc/crontab.xml`, `etc/email_templates.xml`

- [ ] Badge plugin, cache observer, expire cron + email, seed/export CLI, REST CRUD

### Task 6: Quality and GitHub surface

**Files:** tests, `phpunit.xml.dist`, `phpstan.neon`, `.phpcs.xml`, CI, README, PATTERNS, ARCHITECTURE, CHANGELOG, CONTRIBUTING, LICENSE, SECURITY, issue/PR templates

- [ ] Professional repo docs and CI that run without a Magento install
