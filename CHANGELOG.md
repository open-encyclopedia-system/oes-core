# Changelog

## 3.0.0 - 2026-09-22

### Added
- New rights model
- New application plugin initialization logic
- `OES_List_Table` (extends `WP_List_Table`)
- Factory service and improved data model factory
- Cache database table
- New blocks: Context Link, Archive Toggle All, Field (uses shortcode `oes_field`), Abstract, Post Link, Featured Post
- New LOD APIs: ROR, ORCID
- REST API export
- Site health feature
- Shortcodes: audit field value, breadcrumbs
- Update function for OES database tables
- Filter `oes/template_redirect_index_additional_objects` — object filtering on index pages
- Filter `oes/lod_render_shortcode` — modify LOD shortcode output (e.g. during export)
- Filter block styles "Details" and "Classic"
- Language-dependent templates for terms and index pages
- Optional schema tabs for module pages
- General schema option: publisher
- Schema.org types for OES schema
- `schema_type` and `oes_type` parameters for OES objects (replace `type`)
- "Template Type" setting
- Parameter `hide_menu` (replaces `oes_hide_obsolete_menu_structure()`)
- Context parameters for OES Blocks (pass a specific post ID)
- New parameters for block "Display Title": tag selection, display-as-link option
- New parameters for block "Title": HTML tag, link flag
- New parameters for block "Filter Alphabet": label for "all", include-empty flag
- New parameters for block "Author Byline": ORCID display, skip-sorting option
- New parameter for block "Index": group fields

### Changed
- Renamed `OES Manual` → `Guidelines`
- Renamed `Project` → `Application` throughout
- Renamed schema properties: Excerpt → Abstract, Literature → Bibliography, Terms → Subjects
- Reorganized settings and tools pages
- Reorganized and expanded schema settings
- Redesigned settings/tools administration GUI and stabilized config tools
- Redesigned OES dashboard, operations/feature display, page and container icons
- Restyled `oes-filter-item-count`
- Improved internal page generation
- Improved caching via new cache database table
- Added LOD query support via React
- Made LOD preview optional; metadata now configurable
- Made language switch block available outside the navigation
- Added sticky style for block "Table of Contents"
- Added editor style class for Guideline admin pages
- Cleaned up version information notice
- Consolidated XML processing into export formats module

### Deprecated
- Constant `OES_LIVEMODE` — use `OES_LIVE_MODE` instead
- See [DEPRECATIONS.md](./DEPRECATIONS.md) for the full list

### Removed
- "Show OES Objects" setting (admin-only feature)
- Legacy options `hide_version_tab` and `oes_admin-hide_version-tab` (now admin-only by default)
- Parameters 3 and 5 from filter `oes/api_gnd_display_entry`
- Global `$oes_archive_data['archive']['post_type']` — use `$oes_archive['post_type']` instead
- Filter `oes/schema_types` — use `oes/oes_types` instead
- Filters `oes/set_archive_data_caching_enabled`, `oes/schema_options_single`
- Functions `oes_replace_for_serializing()`, `oes_stripslashes_array()`, `oes_replace_for_form()`, `copy_post_meta()`
- Obsolete CSS
- HTML attribute `oes-post-filter-[ID]` — replace with `date=...`
- HTML attribute `oes-post-[language]`

### Fixed
- Warning on custom post archive page
- Editor style for iframe (since WP 7.0.0)

## 2.4.4
### New Filters / Functions
- Added filter: 'oes/get_literature_field_display_value' 
- Added function: oes_normalize_path_for_localhost() (removes leading /oes prefix in path)

### Roles / Permissions
- Modify dashboard view for users with role subscriber
- Remove role read-only
- Add role blocked

### UI / CSS
- Modified admin CSS oes-grey-out to make it darker

## 2.4.3
### Fixes
- Fixed pattern popup
- Fixed OES Image Panel block when images are deleted
- Fixed GND API call (https instead of http)

### Improvements
- Improved filter items: clean name and key when splitting text fields
- Prepare editor styles for WordPress 7.0.0

### New
- Added new filter css classes for classic theme

## 2.4.2
### Fixes
- Fixed title for archive when there are no entries
- Fixed filter processing for alphabet filter when no other filters are applied
- Fixed filter items list: now sorted case-insensitively by default
- Fixed dashicons alignment in "Print" and "Cite as" block
- Added localized schema templates for archives

### Improvements
- Improved caching: transients now stored without autoload
- Improved table of contents block cleanup

### New
- Added new block: Back to Top 
- Added new shortcode: Audit bidirectional ACF relationship fields for a given post type 
- Added new icon: arrow up 
- Added new filter: archive caching keys 
- Added new method: archive processing row data 
- Added parameter prefix to shortcode oes_field 
- Added display value support for fields of type file

## 2.4.1
### Fixes
- Fixed language filter in archives
- Fixed caching issue with multilingual index

### Improvements
- Improved filter logic
- Improved archive class
- Improved search: now accent-independent
- Improved caching: allow project-level implementations

### New
- Added status parameter to batch tool
- Added new block: Share Link
- Added new filter: oes/data_prepared
- Added new icon: list

### Changes
- Replaced oes-alphabet-filter-[alphabet] with data-alphabet="[alphabet]"

## 2.4.0
### Removed
- Filters:
    - `oes/page_version_container` (use `[Container_KEY]_Class` instead)
    - `oes/menu_container_custom_html`
    - `oes/page_container_content` (use `display_page_content` instead)
    - `oes/menu_container_subpages`
    - `oes/menu_container_posts_header`
    - `oes/menu_container_posts` (use `modify_posts` container instead)
    - `oes/menu_container_terms`
    - `oes/menu_container_terms_more_string`
    - `oes/theme_logos`
- Functions:
    - \OES\Schema\get_post_type_params, use OES()->post_types[$postType][$param] instead

### Moved to OES Legacy
- `oes_body_class`
- `oes_prepare_attachment`
- `oes_prepare_single`
- `oes_prepare_tax`
- `oes_prepare_search`
- `oes_prepare_taxonomies`
- `oes_prepare_data_other`
- `oes_prepare_index`

### Removed from filters
- `oes/theme_archive_list_before` (use global `$oes_archive` instead)
- `oes/theme_archive_list_after`

### New
- Introduced `OES_Archive_Loop` class in `oes_get_archive_loop_html`

## 2.3.5
* improve - add default language when converting date to language specific format
* fix - display two GND boxes with same GND ID
* fix - GND css

## 2.3.4
* new - enable language dependent templates
* new - add language depenendent body class
* new - filter 'oes/calculate_value_part_considered_id' to modify considered ID while calculating formula
* improve - add non-latin characters for table of contents anchors
* improve - term display title when language dependent
* improve - enable shortcodes in archive data drop down
* improve - store note and popup information per post ID
* improve - dealing with sub_fields in factory mode
* fix - figures labels in table, panel overlay

## 2.3.3
* new - display status of bidirectional fields
* new - configuration for order in which sort results are displayed per post type
* new - post methods to modify pattern calculation
* new - introducting shortcode tools (to create shortcodes displaying data like maps)
* new - block "Search Terms"
* new - introducing attachment class
* new - introducing filter for not-latin alphabet filter
* new - add parent field value for oes_field shortcode
* new - consider date fields for field display title option
* new - introducing OES panel class and its decendents
* improve - date formatting options
* improve - template redirection
* improve - block options for block theme
* improve - for classic theme: table of content positions
* improve - clean up css and js files
* fix - operation tool

## 2.3.2
* fix - classic language switch on search page
* fix - save schema options

## 2.3.1
* change - data model, default for "editorial_tab" is false
* fix - cookie behaviour
* fix - index archive page without filter
* fix - sorting in metadata
* fix - LOD copy behaviour
* fix - import tool
* fix - taxonomy archive, alphabet filter
* fix - save media language label config
* fix - figure table language dependent labels
* fix - use alternatives for IntDateFormatter if intl is not included
* improve - factory processing for language dependent fields
* improve - post sorting in frontend
* improve - sort search results after post type is optional

## 2.3.0
* new - OES Factory
* new - OES template blocks and styles
* new - OES popup editor tool
* move - "Data Model" from "OES Settings" to "OES Tools"
* move - oesApplyFilter => oesFilter.apply
* move - $oes_frontpage replace by $oes_post->is_frontpage
* move - ACF field option "date_format" is replaced by global format parameter in OES settings
* remove - Post Type Arguments (like "edit screen"), ACF has now configuration option. Change your data model with the OES Factory or model.json.
* remove - OES()->main_language, oes_nav_language. Main language is always "language0"
* remove - $oes_redirect_archive, use action hook "template_redirect" instead
* remove - \OES\Admin\get_block_data, use $block instead
* move - tools "Delete", "Update", "Cache" => OES Legacy (Module) Plugin
* move - \OES\get_post_dtm_parts_from_array => OES Legacy (Module) Plugin, use \OES\Formula\calculate_value instead
* move - media blocks: include OES Module "Media" if you want to use legacy blocks 'Panel', 'Image Panel', 'Gallery Panel'.