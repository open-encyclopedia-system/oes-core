# Open Encyclopedia System (OES)
Building and maintaining online encyclopedias.

[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/open-encyclopedia-system/oes-core/graphs/commit-activity)
[![Version Requirement](https://img.shields.io/badge/WordPress-6.5.0+-blue.svg)](https://wordpress.org)
[![PHP Requirement](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://www.php.net/supported-versions.php)
[![ACF Requirement](https://img.shields.io/badge/ACF-6.3.4%2B-blue.svg)](https://www.advancedcustomfields.com/)
[![Topic](https://img.shields.io/badge/Topic-Digital%20Humanities-lightgrey.svg)](https://github.com/topics/digital-humanities)
[![Topic](https://img.shields.io/badge/Topic-Open%20Access-lightgrey.svg)](https://github.com/topics/open-access)
[![Topic](https://img.shields.io/badge/Topic-Open%20Data-lightgrey.svg)](https://github.com/topics/open-data)
[![Topic](https://img.shields.io/badge/Topic-Open%20Source-lightgrey.svg)](https://github.com/topics/open-source)
[![Topic](https://img.shields.io/badge/Topic-Publishing-lightgrey.svg)](https://github.com/topics/publishing)
[![Topic](https://img.shields.io/badge/Topic-Linked%20Data-lightgrey.svg)](https://github.com/topics/linked-data)
[![AI-DECLARATION: assist](https://img.shields.io/badge/䷼%20AI--DECLARATION-assist-fef9c3?labelColor=fef9c3)](./AI-DECLARATION.md)

**Tags:** `wordpress-plugin`, `cms`, `php`, `scholarly-publishing`, `publishing`, `digital-humanities`, `social-sciences`
**Requires at least:** `WordPress 6.5.0`
**Tested up to:** `WordPress 7.1.0`
**Requires PHP:** `8.1 or later`
**Requires ACF Plugin:** `6.3.4 or later`
**License:** `GPLv2 or later`
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

> **Note:** Most in-depth guides linked from this README (the OES Manual) are written in German. English
> documentation is limited to this file at present.

## Table of Contents

- [About](#about)
- [Features](#features)
- [Shortcodes](#shortcodes)
- [Demo Version](#demo-version)
- [Requirements](#requirements)
- [Recommended Plugins](#recommended-plugins)
- [Installation](#installation)
- [Documentation](#documentation)
- [Cite This Software](#cite-this-software)
- [Support](#support)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## About

The **Open Encyclopedia System (OES)** is a modular and configurable open-source software framework for creating,
publishing, and maintaining **online encyclopedias** in the **humanities and social sciences**.
Designed to support article-based publishing and Open Access principles, OES offers a sustainable and scholarly
solution for digital editorial workflows.

OES is built on **WordPress**, an open-source content management system. WordPress handles core infrastructure such
as user management, content storage, and plugin architecture.

OES originated between 2016–2020 as part of the **German Research Foundation (DFG)**-funded
project *"From 1914-1918-online to the Open Encyclopedia System,"*
and has been maintained and extended since.

For more information, visit the [official website](https://www.open-encyclopedia-system.org/en/index.html)
or read about [OES's history](https://www.open-encyclopedia-system.org/en/ueber_uns/historie/index.html).

## Features

- Publish versioned and citable articles
- Manage relationships between entities and articles
- Integrate Linked Open Data (LOD) APIs
- Define editorial workflows and access roles
- Control article display via a configurable interface
- Maintain long-term sustainability through version control

## Shortcodes

OES registers several shortcodes for use in templates, for example:

| Shortcode           | Description                          |
|---------------------|--------------------------------------|
| `[oes_post_method]` | Renders a post method of an OES post |
| `[oes_field]`       | Renders a post field                 |
| `[oes_audit]`       | Displays data table for audit        |

> These are illustrative examples — see the full shortcode reference for parameters and additional shortcodes:
> [OES Manual Shortcodes](https://manual.open-encyclopedia-system.org/book/shortcodes/) *(German)*.

## Demo Version

The **OES Demo** is an exemplary and fictional online encyclopedia built with the Open Encyclopedia System
framework. It gives you a first-hand look at OES's editorial and front-end functionality without setting up
your own application first.

- **Live demo:** [Explore the demo encyclopedia](https://demo.open-encyclopedia-system.org/)
- **Demo plugin repository:** [oes-demo](https://github.com/open-encyclopedia-system/oes-demo)

> You can find a more detailed installation guide on how to set up the OES Demo here: 
> [OES Manual Installation & Einrichtung](https://manual.open-encyclopedia-system.org/book/installation-einrichtung/) *(German)*.

## Requirements

| Component                    | Minimum Version            |
|------------------------------|----------------------------|
| WordPress                    | 6.5.0 (tested up to 7.1.0) |
| PHP                          | 8.1+                       |
| Advanced Custom Fields (ACF) | 6.3.4+                     |

OES is compatible with standard WordPress hosting environments, such as Apache/MySQL servers, as well as local
development environments like MAMP or XAMPP.

## Recommended Plugins

While not required, the following plugins are commonly used alongside OES to extend its functionality. See the
[OES Manual — Recommended Plugins](https://manual.open-encyclopedia-system.org/book/ergaenzende-plugins/) *(German)*
for the full list and configuration guidance.

## Installation

### Required Components

- `OES Core` plugin – provides foundational editorial functionality
- an application-specific OES plugin, e.g. the `OES Demo` plugin – provides application-specific customizations
- `Advanced Custom Fields (ACF)` – version 6.3.4 or later
- *(Optional)* `OES Theme` – for a tailored front-end interface

> **Note:** The OES Theme is optional. You may use your own WordPress theme, but certain layout features (e.g.
> custom article styling) are only supported via an OES-compatible theme.

### Quick Start

To get OES running locally or on a server, follow these steps:

1. **Install WordPress** on your system.
2. **Download and activate the required plugins**:
   - [OES Core Plugin](https://github.com/open-encyclopedia-system/oes-core)
   - [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/)
   - An application-specific Plugin, e.g. [OES Demo Plugin](https://github.com/open-encyclopedia-system/oes-demo)
3. *(Optional)* **Download and activate a theme**, e.g. the [OES Theme](https://github.com/open-encyclopedia-system/oes-block-theme).
4. **Access the editorial interface**, configure the application settings, and start creating articles.

> For a guided and more detailed setup, see the [OES Manual Installation & Einrichtung](https://manual.open-encyclopedia-system.org/) *(German)*.

## Documentation

Comprehensive documentation is available at:
[OES Manual](https://manual.open-encyclopedia-system.org/) *(German)*

The manual is regularly updated and includes guidance for installation, configuration, and customization.

Additional application documentation in this repository:

- [ROADMAP.md](./ROADMAP.md) — planned features
- [CHANGELOG.md](./CHANGELOG.md) — release history
- [DEPRECATIONS.md](./DEPRECATIONS.md) — deprecated features and migration notes for upgrading between versions

> **Upgrading an existing installation?** Check [DEPRECATIONS.md](./DEPRECATIONS.md) before updating to a new
> version, as it lists deprecated hooks, functions, and shortcodes that may affect existing applications.

## Cite This Software

If you use this software in academic or research work, please cite it using the metadata provided in
[CITATION.CFF](./CITATION.cff) (see [citation-file-format.github.io](https://citation-file-format.github.io/) for
details on the format). This ensures proper attribution and helps track the software's use in scholarly output.

## Support

This repository does not provide GitHub-based support (e.g. no issue tracker or discussions for user support).

For help with plugin usage and configuration, customization options, or application-based implementation, please
contact: **info@open-encyclopedia-system.org**

## Contributing

We welcome contributions from the academic and technical community. Contributions are currently coordinated
directly rather than through GitHub pull requests — please reach out first so we can discuss scope and approach:

**info@open-encyclopedia-system.org**

## Credits

Developed by:
**Digitale Forschungsinfrastrukturen**, Freie Universität Berlin (FUB-IT)
Funded by: **German Research Foundation (DFG)**

For more detailed credits see [CREDITS.md](./CREDITS.md).

## License

This software is licensed under the **GNU General Public License (GPL v2 or later)**.

© 2026 Freie Universität Berlin, FUB-IT, Digitale Forschungsinfrastrukturen.

For full license terms see [LICENSE.txt](./LICENSE.txt) or
[GPL 2.0](https://www.gnu.org/licenses/gpl-2.0.html).