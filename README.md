# Open Encyclopedia System
Tags: publishing, encyclopedia, digital humanities, open access, academic, linked data
Requires at least: 6.5.0
Tested up to: 6.8.2
Requires PHP: 8.1 or later
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Building and maintaining online encyclopedias.

## Description
The Open Encyclopedia System (OES) is a modular and configurable software for creating, publishing and maintaining
online encyclopedias in the humanities and social sciences that are accessible worldwide via Open Access. OES adds a
web-based open source software for article-based publication formats to already available options for electronic
publishing and thus closes existing gaps in the Open Access publishing landscape.

The development of the Open Encyclopedia System Software was funded by the German Research Foundation (DFG) from
2016-2020 as part of the project From 1914-1918-online to the Open Encyclopedia System (see GEPRIS entry). For more
information about the project and existing OES applications please visit our
[website](http://www.open-encyclopedia-system.org/).

## Features
* Publish citable articles
* Manage version control for articles
* Define and edit relationships between entities
* Integrate Linked Open Data (LOD) APIs
* Manage users, posts, entities, access rights, and roles
* Customize article display via the configuration interface

## Links
* [Website](https://www.open-encyclopedia-system.org/)

## Dependencies
An OES installation typically includes:

* **OES Core plugin** – foundational functionality
* **OES Project plugin** – application-specific features
* **OES Theme** *(optional)* – for custom front-end display

**Required Plugin:**

* [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/) – version 6.3.4 or later

## Installation
1. Download the OES Core plugin from GitHub and add it to your WordPress plugin directory.
2. Install and activate required dependencies (e.g., ACF).
3. Activate the OES Core plugin.
4. Install a project-specific OES plugin or use the OES Demo plugin (see its documentation).
5. *(Optional)* Install and activate an OES-compatible theme.

## Support
This repository is not suitable for support.
Support is currently provided via our email help desk info@open-encyclopedia-system.org. We answer questions related to
the OES plugin and its usage. For further information about online encyclopaedias and possible customization please
visit our website.

## Documentation
A user and technical manual for the Open Encyclopedia System is available online at:  
[https://manual.open-encyclopedia-system.org/](https://manual.open-encyclopedia-system.org/)

The manual is actively being expanded and updated.  
If you have questions or need help with specific features, feel free to contact our help desk:  
[info@open-encyclopedia-system.org](mailto:info@open-encyclopedia-system.org)

## Demo Version
You can experience the frontend and editorial layer of an exemplary application. This application includes a basic
online encyclopedia and a WordPress theme. Download the OES Demo plugin and the OES theme from github
(https://github.com/open-encyclopedia-system/). Activate plugin and theme and start creating content or import demo data.

## Contributing
If you want to contribute to OES please contact the help desk info@open-encyclopedia-system.org.

## Credits
Developed by the Digitale Infrastrukturen, Freie Universität Berlin FUB IT, with support from the German Research 
Foundation (DFG).

## Licencing
Copyright (C) 2025 
Freie Universität Berlin, FUB IT, Digitale Infrastrukturen
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

# Changelog

## Unreleased
- Improve performance of search indexing

---

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