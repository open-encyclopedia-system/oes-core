# Open Encyclopedia System Plugin

Welcome to the main Open Encyclopedia System (OES) repository on GitHub. OES is a modular and configurable software for creating, publishing and maintaining online encyclopedias in the humanities and social sciences that are accessible worldwide via Open Access. OES adds a web-based open source software for lemma-based publication formats to already available options for electronic publishing and thus closes existing gaps in the Open Access publishing landscape. 

The development of the Open Encyclopedia System Software was funded by the German Research Foundation (DFG) from 2016-2020 as part of the project From 1914-1918-online to the Open Encyclopedia System (see GEPRIS entry). For more information about the project and existing OES applications please visit our [website](http://www.open-encyclopedia-system.org/).

OES is a WordPress plugin, developed on WordPress version 5.4.2. 
A typical OES application consists of a OES plugin and an additional OES project plugin which implements the OES features for the application. 


## Documentation (Coming Soon)

We are working on a detailed user manual and a technical function reference. Support is currently provided via our email help desk info@open-encyclopedia-system.org. We answer questions related to the OES plugin and its usage.


## Getting Started

### Dependencies

OES depends on the WordPress plguin ACF:
* Advanced Custom Fields, version 5.9., URL: https://www.advancedcustomfields.com/.

We recommend using the WordPress plugin Classic Editor to create and edit posts:
* Classic Editor, version 1.6., URL: https://wordpress.org/plugins/classic-editor/.

OES requires the php extension apcu.

### Installation

1. Install OES plugin and dependencies
Download the OES plugin sources into the WordPress plugin directory. Download and activate the plugins listet under 'Dependencies'. Activate the OES plugin.

2. Configure Solr (Optional)
If you want to use solr as a database for your project you need to initialize the solr database and a core. Inside the OES plugin directory navigate to 'solr/solr-config-sample.php'. Customize the attributes inside the file and save as 'solr/solr-config.php'. The solr/sample-solr-7.5.0-core-config-dir directory can be used as core directory also.


## Demo Version (Coming Soon)

You will soon be able to experience the frontend and editorial layer of an exemplary application. This application will include a basic online encyclopedia and a WordPress theme.


## Support

This repository is not suitable for support.

Support is currently provided via our email help desk info@open-encyclopedia-system.org. We answer questions related to the OES plugin and its usage. For further information about online encyclopaedias and possible customization please visit our [website](http://www.open-encyclopedia-system.org/). 


## Contributing

If you want to contribute to OES please contact the help desk info@open-encyclopedia-system.org.


## Licencing

Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. 

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
