# Open Encyclopedia System
Tags: publishing, encyclopedia
Requires at least: 6.0
Tested up to: 6.6
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
* Set up version control for published articles
* Edit and administer relations between entities
* Use linked open data APIs to GND
* Manage user, posts, entities, access rights and role models
* Use the OES configuration interface to change the display of the published articles

## Links
* [Website](https://www.open-encyclopedia-system.org/)

## Dependencies
An OES application consists of a OES Core plugin, an additional OES project plugin which implements the OES features
for the application and an optional OES theme.

OES depends on the WordPress plugin ACF:
Advanced Custom Fields, version 6.3.4, URL: https://www.advancedcustomfields.com/.

## Installation
1. Download the OES plugin from gitHub and add it to your WordPress plugin directory.
2. Download and activate the dependencies.
3. Activate the OES plugin.
4. Create your OES project plugin or download, activate and initialize the OES Demo plugin (follow the installation instructions in the OES Demo plugin).
5. (Optional) Download and activate an OES theme.

## Support
This repository is not suitable for support.
Support is currently provided via our email help desk info@open-encyclopedia-system.org. We answer questions related to
the OES plugin and its usage. For further information about online encyclopaedias and possible customization please
visit our website.

## Documentation (Coming Soon)
We are working on a detailed user manual and a technical function reference. Support is currently provided via our
email help desk info@open-encyclopedia-system.org. We answer questions related to the OES plugin and its usage.

## Demo Version
You can experience the frontend and editorial layer of an exemplary application. This application includes a basic
online encyclopedia and a WordPress theme. Download the OES Demo plugin and the OES theme from github
(https://github.com/open-encyclopedia-system/). Activate plugin and theme and start creating content or import demo data.

## Contributing
If you want to contribute to OES please contact the help desk info@open-encyclopedia-system.org.

## Licencing
Copyright (C) 2024 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

# Changelog

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