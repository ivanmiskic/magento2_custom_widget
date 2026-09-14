# Changelog

All notable changes to this project are documented in this file.

## [2.0.0] - 2026-09-14

### Added

- `Null_Blueprint` Magento Open Source 2.4.8 module (`null/module-blueprint`).
- Campaign entity with admin UI grid/form, store scope, schedule, and product conditions.
- Blueprint Campaign Products widget (campaign mode + custom Magento conditions).
- Storefront campaign list and detail pages.
- REST CRUD, cron expiry + email, CLI seed/export, catalog badge plugin, cache observer.
- Pattern index for AI and humans (`docs/PATTERNS.md`).
- CI: PHPUnit (pure classes), PHPStan, PHPCS Magento2 on PHP 8.2 and 8.3.

### Removed

- `Inchoo_CatalogWidget` and widget id `inchoo_products_list`. CMS instances of that widget do not migrate.

### Breaking

- Module name, namespaces, and widget id all changed. Treat 2.0.0 as a new module.
