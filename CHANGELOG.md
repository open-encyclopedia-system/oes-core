
# Changelog

## 2.4.2
### Fixes
- Fixed title for archive when there are no entries
- Fixed filter processing for alphabet filter when no other filters are applied
- Fixed filter items list: now sorted case-insensitively by default
- Fixed dashicons alignment in "Print" and "Cite as" block

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