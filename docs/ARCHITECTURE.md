# Architecture

`Null_Blueprint` is one Magento module and one bounded context: **Campaign**.

```
Admin form / REST POST
        │
        ▼
CampaignRepository::save()
        │
        ├── ResourceModel + null_blueprint_campaign
        ├── store rows in null_blueprint_campaign_store
        └── event null_blueprint_campaign_save_after
                └── InvalidateCampaignCache (block_html + full_page tags)

Storefront / widget / REST GET
        │
        ▼
Collection::addStoreFilter + addVisibleFilter
        │
        ├── ViewModel getters → escaped phtml
        └── ProductCollectionBuilder (condition SQL → product collection)

Cron 01:00
        └── ExpireCampaigns → status=expired → email if configured
```

## Rules

- Controllers and blocks do not load models with `load()`. They use the repository or a collection factory.
- phtml has no business logic and no `$block->helper()` calls.
- Empty product conditions yield an empty collection, never the whole catalog.
- `cacheable="false"` is never used on public handles.
- Identifier is a global unique URL key: `[a-z0-9]+(?:-[a-z0-9]+)*`, max 64.

## Detail URL

Only `campaigns/index/view/id/{id}`. Identifier is a unique key and REST field, not a custom router.
