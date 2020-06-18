<?php
define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// time in seconds before reloading orcid data from orcid.org
define( 'ORCID_CACHE_TIMEOUT', 3600);
// XSLT file to convert orcid xml to html
define( 'ORCID_XSLT', MY_PLUGIN_PATH . 'orcid-data-all.xsl');
// append orcid_id to this to access a users orcid data
define( 'ORCID_URL', 'https://pub.orcid.org/v3.0/');

