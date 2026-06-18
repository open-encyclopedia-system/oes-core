# Open Encyclopedia System (OES)
Building and maintaining online encyclopedias.

[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/open-encyclopedia-system/OES/graphs/commit-activity)
[![Version Requirement](https://img.shields.io/badge/WordPress-6.5.0+-blue.svg)](https://wordpress.org)
[![PHP Requirement](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://www.php.net/supported-versions.php)
[![Topic](https://img.shields.io/badge/Topic-Digital%20Humanities-lightgrey.svg)](#)
[![Topic](https://img.shields.io/badge/Topic-Open%20Access-lightgrey.svg)](#)
[![Topic](https://img.shields.io/badge/Topic-Publishing-lightgrey.svg)](#)
[![Topic](https://img.shields.io/badge/Topic-Linked%20Data-lightgrey.svg)](#)
[![AI-DECLARATION: assist](https://img.shields.io/badge/䷼%20AI--DECLARATION-assist-fef9c3?labelColor=fef9c3)](./AI-DECLARATION.md)

**Tags:** `publishing`, `encyclopedia`, `digital humanities`, `open access`, `academic`, `linked data`
**Requires at least:** `WordPress 6.5.0`
**Tested up to:** `WordPress 6.9.4`
**Requires PHP:** `8.1 or later`
**License:** `GPLv2 or later`
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

The **Open Encyclopedia System (OES)** is a modular and configurable open-source software framework for creating, publishing, and maintaining **online encyclopedias** in the **humanities and social sciences**. Designed to support article-based publishing and Open Access principles, OES offers a sustainable and scholarly solution for digital editorial workflows.

OES is built on **WordPress**, an open-source content management system. WordPress handles core infrastructure such as user management, content storage, and plugin architecture.

Developed between 2016–2020 with funding from the **German Research Foundation (DFG)**, OES emerged from the project *“From 1914-1918-online to the Open Encyclopedia System”*.

For more information, visit the [official website](https://open-encyclopedia-system.org/).

## Cite this software

If you use this software, please cite it using the metadata in the file CITATION.CFF (for more information about CFF see https://citation-file-format.github.io/).

## Features

- Publish versioned and citable articles
- Manage relationships between entities and articles
- Integrate Linked Open Data (LOD) APIs
- Define editorial workflows and access roles
- Control article display via a configurable interface
- Maintain long-term sustainability through version control

## Installation

OES is a WordPress-based plugin suite, which is compatible with standard WordPress hosting environments, such as Apache/MySQL servers or local development environments like MAMP or XAMPP. A typical installation includes:

### Required Components

- `OES Core` plugin – provides foundational editorial functionality
- `OES Project` plugin – includes project-specific customizations
- `Advanced Custom Fields (ACF)` – version 6.3.4 or later
- (Optional) `OES Theme` – for a tailored front-end interface.
> Note: The OES Theme is optional. You may use your own WordPress theme, but certain layout features (e.g. custom article styling) are only supported via an OES-compatible theme.

### Steps

1. Download the OES Core plugin from GitHub and add it to your WordPress plugin directory.
2. Install and activate **Advanced Custom Fields (ACF)**.
3. Activate the OES Core plugin.
4. Install a project-specific OES plugin or use the [OES Demo plugin](https://github.com/open-encyclopedia-system/).
5. (Optional) Install and activate the OES-compatible theme.

## Quick Start

To get OES running locally or on a server, follow these steps:

1. **Install WordPress** (v6.5.0 or later) and PHP 8.1+ on your system.
2. **Download and install the required plugins**:
- [OES Core Plugin](#) → Place it in `/wp-content/plugins/`
- [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/) → Install via WordPress Admin
- Optionally install:
- [OES Project Plugin](#)
- [OES Theme](#)
3. **Activate the plugins** in your WordPress admin dashboard.
4. **Access the editorial interface**, configure the project settings, and start creating articles.

For a guided setup, see the [OES Manual](https://manual.open-encyclopedia-system.org/).

## Documentation

Comprehensive documentation is available at:
[https://manual.open-encyclopedia-system.org/](https://manual.open-encyclopedia-system.org/)

The manual is regularly updated and includes guidance for installation, configuration, and customization.

For technical support, please contact:
info@open-encyclopedia-system.org

## Demo Version

Explore the editorial interface and front-end of an example application with the **OES Demo plugin**, which includes:

- A minimal online encyclopedia setup
- A compatible WordPress theme
- Optional demo content for testing

### Steps

1. Install the `OES Demo Plugin` and the `OES Theme` from the [OES GitHub repository](https://github.com/open-encyclopedia-system/).
2. Activate both in the WordPress admin dashboard.
3. Navigate to the settings to import the provided demo content.

## Support

This repository does not provide GitHub-based support.
For help with:

- Plugin usage and configuration
- Customization options
- Project-based implementation

Please contact:
info@open-encyclopedia-system.org

## Contributing

We welcome contributions from the academic and technical community.
To get involved, please email:

info@open-encyclopedia-system.org

## Credits

Developed by:
**Digitale Infrastrukturen**, Freie Universität Berlin (FUB IT)
Funded by: **German Research Foundation (DFG)**

## License

This software is licensed under the **GNU General Public License (GPL v2 or later)**.

© 2025 Freie Universität Berlin, FUB IT, Digitale Infrastrukturen.

For full license terms, see:
[https://www.gnu.org/licenses/old-licenses/gpl-2.0.html](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
