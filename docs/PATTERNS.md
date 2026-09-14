# Patterns

Copy one heading into a new `Null_*` module. Do not copy the Campaign domain unless you need campaigns.

## Widget

**When:** You need a CMS/widget-instance product block with admin parameters.

**Files:** `etc/widget.xml`, `Block/Widget/CampaignProducts.php`, `view/frontend/templates/widget/campaign/*.phtml`, `view/adminhtml/web/images/widget-campaign.png`

**Recipe:**

1. Declare `<widget id="..." class="...">` with `xsi:type="conditions"` for the Magento product picker.
2. Extend `Magento\CatalogWidget\Block\Product\ProductsList` only if you need condition SQL.
3. Keep identities (`CACHE_TAG`) so FPC can expire the block.
4. Ship an empty-state template. Never render a blank string for “no products”.
5. Do not set `cacheable="false"` on catalog/CMS layout handles.

## UI grid

**When:** You need an admin listing.

**Files:** `view/adminhtml/ui_component/null_blueprint_campaign_listing.xml`, `etc/di.xml` CollectionFactory map, `Ui/Component/Listing/Column/CampaignActions.php`, `view/adminhtml/layout/blueprint_campaign_index.xml`

**Recipe:**

1. Name the listing `{vendor}_{module}_{entity}_listing`.
2. Map `{listing}_data_source` to a SearchResult / grid collection in `di.xml`.
3. Add mass actions that point at `MassDelete` / `MassStatus` controllers.
4. Put row actions in a `Column` class, not in the XML.
5. ACL the dataSource with the view resource.

## UI form

**When:** You need create/edit.

**Files:** `view/adminhtml/ui_component/null_blueprint_campaign_form.xml`, `Model/Campaign/DataProvider.php`, `Block/Adminhtml/Campaign/Edit/*Button.php`, `Controller/Adminhtml/Campaign/Save.php`

**Recipe:**

1. DataProvider extends `AbstractDataProvider` and hydrates image/store arrays.
2. Save controller maps POST → interface setters → repository.
3. Persist failed POST with `DataPersistorInterface`.
4. Buttons implement `ButtonProviderInterface`.
5. Use UI `imageUploader` + a dedicated `Upload` controller.

## Repository

**When:** Any entity other modules or REST should load.

**Files:** `Api/CampaignRepositoryInterface.php`, `Api/Data/CampaignInterface.php`, `Model/CampaignRepository.php`, `etc/di.xml` preferences

**Recipe:**

1. Interface methods: `getById`, `getList`, `save`, `delete`, `deleteById`.
2. Throw `NoSuchEntityException`, `CouldNotSaveException`, `CouldNotDeleteException`.
3. Dispatch a custom `*_save_after` event from `save` and `delete`.
4. Never call the resource model from a controller.
5. Validate identifiers before `resource->save()`.

## Plugin

**When:** You must change Magento output without rewriting a class.

**Files:** `Plugin/Catalog/Block/Product/AbstractProductPlugin.php`, `etc/di.xml` `<plugin>`

**Recipe:**

1. Prefer `after*` on the smallest public method that already returns HTML or data.
2. Keep one plugin per behavior. This module’s plugin only prepends a badge.
3. Escape any string you inject into HTML.
4. Cache expensive lookups on the resolver, not in the plugin.
5. Do not plugin `AbstractBlock::_toHtml` “just in case”.

## Observer

**When:** You react to a save/delete without coupling the repository to cache APIs.

**Files:** `Observer/InvalidateCampaignCache.php`, `etc/events.xml`

**Recipe:**

1. Dispatch a module-owned event name from the repository.
2. Register cache tags on `CacheContext` and clean `block_html` / `full_page` types.
3. Observer must be idempotent.
4. Do not run heavy collection work in an observer if a plugin/cron is clearer.
5. Name the observer `{vendor}_{module}_{intent}`.

## Cron

**When:** Time-based state changes.

**Files:** `Cron/ExpireCampaigns.php`, `etc/crontab.xml`

**Recipe:**

1. One class, one `execute(): void`.
2. Load a narrow collection, then `repository->save()` so events still fire.
3. Put the schedule in `crontab.xml` (`0 1 * * *` here).
4. Keep date math in a plain class (`CampaignVisibility`) so you can unit-test it.
5. Log and continue on per-row failures if you add try/catch later.

## CLI

**When:** Operators need seed/export without the admin.

**Files:** `Console/Command/*Command.php`, `etc/di.xml` `CommandListInterface`

**Recipe:**

1. `setName('vendor:entity:action')`.
2. Return `Command::SUCCESS` / `Command::FAILURE`.
3. Seed must be idempotent; use `--force` to update.
4. Write exports under `var/`.
5. Do not print secrets.

## REST

**When:** Integrations need the entity.

**Files:** `etc/webapi.xml`, repository interface

**Recipe:**

1. Map `/V1/{module}/{entities}` to repository methods.
2. Reuse ACL resources from `etc/acl.xml`.
3. GET list here is visibility-filtered; GET by id is not.
4. No anonymous storefront REST in this module.
5. Let Magento render the standard error envelope.

## Email

**When:** A state change should notify a human.

**Files:** `Mail/CampaignExpiredSender.php`, `etc/email_templates.xml`, `view/frontend/email/campaign_expired.html`

**Recipe:**

1. `TransportBuilder` + `setFromByScope('general')`.
2. Suspend inline translation around send.
3. Recipient from system config, fallback to general store email.
4. Catch and log send failures; do not fail the cron.
5. Keep the HTML template short and translatable.

## db_schema

**When:** You persist a new entity.

**Files:** `etc/db_schema.xml`, `etc/db_schema_whitelist.json`

**Recipe:**

1. No `setup_version` in `module.xml`.
2. Identity PK + unique identifier + status/schedule indexes.
3. Store scope via a link table with FK cascade.
4. After you change schema in a Magento install, regenerate the whitelist.
5. Do not add InstallSchema.php.

## ViewModel

**When:** A template needs data.

**Files:** `ViewModel/*.php`, layout `<argument name="view_model" xsi:type="object">`

**Recipe:**

1. Implement `ArgumentInterface`.
2. Expose `getItems()`, `isEmpty()`, URL getters — no HTML assembly.
3. Inject the ViewModel in layout, not via ObjectManager in phtml.
4. Escape in the template, not in the ViewModel (except URL builders).
5. Reuse the same collection filters as the widget.

## System config

**When:** Merchants need defaults without code.

**Files:** `etc/adminhtml/system.xml`, `etc/config.xml`, `Model/Config.php`

**Recipe:**

1. Tab `null`, section `blueprint`, group `general`.
2. Defaults live in `config.xml`.
3. Wrap `ScopeConfigInterface` in `Model/Config`.
4. Storefront/widget honor `enabled`; admin CRUD does not.
5. ACL the section with `Null_Blueprint::config`.

## ACL

**When:** Any admin or REST route exists.

**Files:** `etc/acl.xml`, controller `ADMIN_RESOURCE`, `etc/adminhtml/menu.xml`

**Recipe:**

1. Parent view resource plus `_save` / `_delete` / `config` children.
2. Menu `resource=` matches the view resource.
3. Controllers that mutate data use the save/delete constants.
4. REST `webapi.xml` repeats the same ids.
5. Do not hide buttons only in UI — enforce ACL on the controller.
