<?php
/**
 * Plugin Name: orcid
 * Plugin URI: http://orcid.joshia.msu.domains/
 * Description: Get ORCiD data.
 * Version: 1.0
 * Author: Amaresh R. Joshi
 * Author URI: http://joshia.msu.domains/
 */

/**
 * ... insert any required ownership and copyright boilerplate here
 */
include_once( plugin_dir_path( __FILE__ ) . 'config.php' );
include( MY_PLUGIN_PATH . 'orcid-functions.php' );

/************************
 * WORDPRESS HOOKS
 ************************/

/**
 * add actions to the (de)install activation hooks
 */
register_activation_hook( __FILE__, 'orcid_install' );
register_deactivation_hook( __FILE__, 'orcid_uninstall' );

//add_action('wp_enqueue_scripts', 'orcid_scripts');
//add_action('admin_enqueue_scripts', 'orcid_scripts');
add_action( 'admin_menu', 'orcid_create_menu' );

/**
 * install procedures:
 * schedule daily event to update publication lists
 */
function orcid_install() {
	// empty for now
}

/**
 * un-install procedures:
 * remove any scheduled tasks
 */
function orcid_uninstall() {
	// empty for now
}

/**
 * add javascript and stylesheets to both the admin page and front-end.
 * hooked by 'wp_enqueue_scripts' and 'admin_enqueue_scripts'
 */
function orcid_scripts() {
	// empty for now
	// wp_enqueue_style('orcid_style', plugins_url('ip_style.css', __FILE__));
	// wp_enqueue_script('orcid_script', plugins_url('ip_script.js', __FILE__), array('jquery'), null, true);
}

/************************
 * SHORTCODE HOOKS
 ************************/
/**
 * register the shortcode
 */
function register_shortcodes() {
	add_shortcode( 'orcid-data', 'orcid_data_function' );
}

/**
 * hook into WordPress
 */
add_action( 'init', 'register_shortcodes' );

/**
 * create the admin menu
 * hooked by admin_menu event
 */
function orcid_create_menu() {
	add_menu_page( 'My ORCiD Retrieval and Display Information', 'My ORCiD Profile',
		'edit_posts', __FILE__, 'orcid_settings_form' );
}

/**
 * create and handle the settings form
 * hooked by orcid_create_menu
 */
function orcid_settings_form() {
	$user_ob = wp_get_current_user();
	$user    = $user_ob->ID;
	//=================================================
	$download_from_orcid_flag = false;
	//=================================================
	// process a form submission if it has occurred
	if ( isset( $_POST['submit'] ) ) {
		//+++++++++++++++++++++++++++++++++++++++++++++++++++++
		// check_admin_referer('orcid_nonce')?
		// this used for security to validate form data came from current site.
		// see: https://codex.wordpress.org/Function_Reference/check_admin_referer
		// nonce: https://wordpress.org/support/article/glossary/#nonce
		//+++++++++++++++++++++++++++++++++++++++++++++++++++++
		check_admin_referer( 'orcid_nonce' );

		if ( isset( $_POST['orcid_id'] ) ) {
			$orcid_id = $_POST['orcid_id'];
		} else {
			//$orcid_id = '';
			$orcid_id = get_user_meta( $user, '_orcid_id', true );
		}
		//
		// we can either download the data from orcid.org OR use the cached value
		// we download the data IFF ($download_from_orcid_flag = TRUE)
		// 1) orcid_id has changed
		// 2) there is no cached xml data
		// 3) the cached value is older than ORCID_CACHE_TIMEOUT (in seconds)
		//
		$download_from_orcid_flag = false;
		//
		// 1) orcid_id has changed
		$orcid_id_db = get_user_meta( $user, '_orcid_id', true );
		if ( $orcid_id !== $orcid_id_db ) {
			$download_from_orcid_flag = true;
		}
		//
		// 2) there is no cached xml data
		// empty($foo) is better than ($foo == '')
		if ( empty( get_user_meta( $user, '_orcid_xml', true ) ) ) {
			$download_from_orcid_flag = true;
		}
		//
		// 3) the cached value is older than ORCID_CACHE_TIMEOUT (in seconds)
		$current_time = time();
		// last download time
		$orcid_xml_download_time = intval( get_user_meta( $user, '_orcid_xml_download_time', true ) );
		//
		$time_diff = $current_time - $orcid_xml_download_time;
		if ( $time_diff >= ORCID_CACHE_TIMEOUT ) {
			$download_from_orcid_flag = true;
		}
	}
	?>
    <div class="wrap">
        <h2>ORCiD Profile Settings</h2>
        <form method="POST" id="orcidForm">
            <!-- wp_nonce_field used for security (see above comment) -->
			<?php wp_nonce_field( 'orcid_nonce' ); ?>
            <!-- need to replace table with CSS -->
            <table>
                <tr>
                    <td><label for="orcid_id">ORCiD ID</label></td>
                    <td>
                        <input type="text" name="orcid_id" id="orcid_id"
                               value="<?php echo esc_attr__( $orcid_id ); ?>">
                    </td>
                </tr>
                <tr>
                    <td><input type="submit" name="submit" value="Update" class="button-primary"/></td>
                </tr>
            </table>
        </form>
    </div>

	<?php
	if ( $download_from_orcid_flag ) {
		echo '<p>Downloading XML data from orcid.org</p>' . PHP_EOL;
		$orcid_xml = orcid_download_data( $orcid_id );
		update_user_meta( $user, '_orcid_xml', $orcid_xml );
		//
		// keep track of when download occurred
		update_user_meta( $user, '_orcid_xml_download_time', strval( time() ) );
	} else {
		echo '<p>Using cached XML data</p>' . PHP_EOL;
		$orcid_xml = get_user_meta( $user, '_orcid_xml', true );
	}

	//
	// this option (display a title line/header) is not available to the user
	// it is set to 'yes' in the orcid config backend
	// it can be set to 'no' when individual orcid sections are to be displayed
	// e.g. when using short words
	$display_sections['display_header'] = 'yes';

	$display_sections['display_personal']           = 'yes';
	$display_sections['display_education']          = 'yes';
	$display_sections['display_employment']         = 'yes';
	$display_sections['display_works']              = 'yes';
	$display_sections['display_fundings']           = 'yes';
	$display_sections['display_peer_reviews']       = 'yes';
	$display_sections['display_invited_positions']  = 'yes';
	$display_sections['display_memberships']        = 'yes';
	$display_sections['display_qualifications']     = 'yes';
	$display_sections['display_research_resources'] = 'yes';
	$display_sections['display_services']           = 'yes';
	$orcid_html                                     = orcid_format_data_as_html( $orcid_xml, $display_sections );
	echo '<div class="wrap" id="orcid_wrapper">' . $orcid_html . '</div>';
}

?>