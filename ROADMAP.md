# Roadmap

This document tracks planned features, improvements, and known issues for the OES Core Plugin.
See [DEPRECATIONS.md](./DEPRECATIONS.md) for all currently deprecated and planned removals.

## Legend
- 🔴 Not started
- 🟡 In progress
- 🟢 Done
- ⏸️ On hold / blocked

## Unreleased
- [ ] 🟡 Update all OES modules single options (e.g. schema_options_single, check config keys)

## Planned
- [ ] Write and edit help texts on admin pages
- [ ] Multilingual - Ensure proper handling of alt image text
- [ ] Map schema parameters directly to json-ld parameter (e.g. in get_schema_config)
- [ ] Add contributor role to schema
- [ ] Move shortcode logic to custom blocks

## Improvements
- [ ] improve site health feature by adding more data
- [ ] improve caching storage
- [ ] improve config update value normalization when storing in database
- [ ] improve export format; e.g. add download option, add list export (now it's only single we want to export all elements of a specific type at once)
- [ ] improve admin notices
- [ ] improve integration of OES content on WordPress dashboard
- [ ] improve styling PDF version of popups
- [ ] improve date formatting

## Checks
- [ ] Verify the removal of `oes-ignore-alphabet-filter` from `filter.js`. Potential issues with timeline, EV
- [ ] Date model with more than two languages

## CleanUps
- [ ] clean up and standardize namespaces
- [ ] Standardize usage of `$oes` vs `OES()`
- [ ] Standardize usage of `oes_write_log()`
- [ ] clean up oes_add_style/oes_add_script/$oes_assets
- [ ] include admin classes on admin pages only if needed
- [ ] clean up JavaScript: use vanilla instead of jQuery
 
## Under Consideration
- Shortcode verification and preview in admin pages
- WP-CLI commands
- map OES schema types to json-ld schema types per default

## Known Issues
- [ ] Inserting GND shortcode creates a block instead of inline content
