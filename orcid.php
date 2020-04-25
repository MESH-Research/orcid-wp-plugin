<?php
/**
 * Plugin Name: orcid
 * Plugin URI: http://orcid.joshia.msu.domains/
 * Description: Get ORCiD data.
 * Version: 1.0
 * Author: Amaresh R. Joshi
 * Author URI: http://joshia.msu.domains/
 */

define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
include( MY_PLUGIN_PATH . 'includes/orcid-functions.php');

add_action( 'the_content', 'display_orcid_info' );

function display_orcid_info ( $content ) {
    // return $content .= '<p>Thank you for reading!</p>';
    $orcidID = "0000-0003-0265-9119"; // Alan Munn
    //$orcidID = "0000-0003-1822-3109";  // Bronson Hui
    //$orcidID = "0000-0003-3953-7940"; // Chris Long (U of CO at Boulder)
    //$orcidID = "0000-0002-5251-0307"; // Kathleen Fitzpatrick
    //$orcidID = "0000-0002-8143-2408"; // Scott Schopieray

    $orcid_data = get_orcid_data($orcidID);
    return $content .= $orcid_data;
}

