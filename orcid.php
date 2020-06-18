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
include_once( plugin_dir_path( __FILE__ ) . 'config.php');
include( MY_PLUGIN_PATH . 'orcid-functions.php');

/************************
 * WORDPRESS HOOKS
 ************************/

/**
 * add actions to the (de)install activation hooks
 */
register_activation_hook(__FILE__, 'orcid_install');
register_deactivation_hook(__FILE__, 'orcid_uninstall');

//add_action('wp_enqueue_scripts', 'orcid_scripts');
//add_action('admin_enqueue_scripts', 'orcid_scripts');
add_action('admin_menu', 'orcid_create_menu');

/**
 * install procedures:
 * schedule daily event to update publication lists
 */
function orcid_install()
{
    // empty for now
}

/**
 * un-install procedures:
 * remove any scheduled tasks
 */
function orcid_uninstall()
{
    // empty for now
}

/**
 * add javascript and stylesheets to both the admin page and front-end.
 * hooked by 'wp_enqueue_scripts' and 'admin_enqueue_scripts'
 */
function orcid_scripts()
{
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
function register_shortcodes(){
	add_shortcode('orcid-data', 'orcid_data_function');
}
/**
 * hook into WordPress
 */
add_action( 'init', 'register_shortcodes');

/**
 * create the admin menu
 * hooked by admin_menu event
 */
function orcid_create_menu()
{
    add_menu_page('My ORCiD Retrieval and Display Information', 'My ORCiD Profile',
        'edit_posts', __FILE__, 'orcid_settings_form');
}

/**
 * create and handle the settings form
 * hooked by orcid_create_menu
 */
function orcid_settings_form()
{
    $user_ob = wp_get_current_user();
    $user = $user_ob->ID;
    //=================================================
    // we are not validating orcid_id's for now
    // just leave this as '' (blank)
    $valid = '';
    $download_from_orcid_flag = FALSE;
	//
	// this option (display a title line/header) is not available to the user
	// it is set to 'yes' in the orcid config backend
	// it can be set to 'no' when individual orcid sections are to be displayed
	// e.g. when using short words
	$display_sections['display_header'] = 'yes';
    //=================================================
    // process a form submission if it has occurred
    if (isset($_POST['submit'])) {
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
        // check_admin_referer('orcid_nonce')?
        // this used for security to validate form data came from current site.
        // see: https://codex.wordpress.org/Function_Reference/check_admin_referer
        // nonce: https://wordpress.org/support/article/glossary/#nonce
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
        check_admin_referer('orcid_nonce');

	    if (isset($_POST['orcid_id'])) {
		    $orcid_id = $_POST['orcid_id'];
	    } else {
		    $orcid_id = '';
	    }
        //
        // we can either download the data from orcid.org OR use the cached value
        // we download the data IFF ($download_from_orcid_flag = TRUE)
        // 1) orcid_id has changed
        // 2) there is no cached xml data
        // 3) the cached value is older than ORCID_CACHE_TIMEOUT (in seconds)
        //
        $download_from_orcid_flag = FALSE;
        //
        // 1) orcid_id has changed
        $orcid_id_db = get_user_meta($user, '_orcid_id', TRUE);
        if($orcid_id !== $orcid_id_db){
            $download_from_orcid_flag = TRUE;
        }
        //
        // 2) there is no cached xml data
        // empty($foo) is better than ($foo == '')
        if(empty(get_user_meta($user, '_orcid_xml', TRUE))){
            $download_from_orcid_flag = TRUE;
        }
        //
        // 3) the cached value is older than ORCID_CACHE_TIMEOUT (in seconds)
        $current_time = time();
        // last download time
        $orcid_xml_download_time = intval(get_user_meta($user, '_orcid_xml_download_time', TRUE));
        //
        $time_diff = $current_time - $orcid_xml_download_time;
        if($time_diff >= ORCID_CACHE_TIMEOUT){
            $download_from_orcid_flag = TRUE;
        }

	    if (isset($_POST['display_personal'])) {
		    $display_sections['display_personal'] = $_POST['display_personal'];
	    } else {
		    $display_sections['display_personal'] = 'no';
	    }
	    if (isset($_POST['display_education'])) {
		    $display_sections['display_education'] = $_POST['display_education'];
	    } else {
		    $display_sections['display_education'] = 'no';
	    }
	    if (isset($_POST['display_employment'])) {
		    $display_sections['display_employment'] = $_POST['display_employment'];
	    } else {
		    $display_sections['display_employment'] = 'no';
	    }
	    if (isset($_POST['display_works'])) {
		    $display_sections['display_works'] = $_POST['display_works'];
	    } else {
		    $display_sections['display_works'] = 'no';
	    }
	    if (isset($_POST['display_fundings'])) {
		    $display_sections['display_fundings'] = $_POST['display_fundings'];
	    } else {
		    $display_sections['display_fundings'] = 'no';
	    }
	    if (isset($_POST['display_peer_reviews'])) {
		    $display_sections['display_peer_reviews'] = $_POST['display_peer_reviews'];
	    } else {
		    $display_sections['display_peer_reviews'] = 'no';
	    }
	    if (isset($_POST['display_invited_positions'])) {
		    $display_sections['display_invited_positions'] = $_POST['display_invited_positions'];
	    } else {
		    $display_sections['display_invited_positions'] = 'no';
	    }
	    if (isset($_POST['display_memberships'])) {
		    $display_sections['display_memberships'] = $_POST['display_memberships'];
	    } else {
		    $display_sections['display_memberships'] = 'no';
	    }
	    if (isset($_POST['display_qualifications'])) {
		    $display_sections['display_qualifications'] = $_POST['display_qualifications'];
	    } else {
		    $display_sections['display_qualifications'] = 'no';
	    }
	    if (isset($_POST['display_research_resources'])) {
		    $display_sections['display_research_resources'] = $_POST['display_research_resources'];
	    } else {
		    $display_sections['display_research_resources'] = 'no';
	    }
	    if (isset($_POST['display_services'])) {
		    $display_sections['display_services'] = $_POST['display_services'];
	    } else {
		    $display_sections['display_services'] = 'no';
	    }
        update_user_meta($user, '_orcid_id', $orcid_id);
        update_user_meta($user, '_orcid_display_personal', $display_sections['display_personal']);
        update_user_meta($user, '_orcid_display_education', $display_sections['display_education']);
        update_user_meta($user, '_orcid_display_employment', $display_sections['display_employment']);
        update_user_meta($user, '_orcid_display_works', $display_sections['display_works']);
        update_user_meta($user, '_orcid_display_fundings', $display_sections['display_fundings']);
	    update_user_meta($user, '_orcid_display_peer_reviews', $display_sections['display_peer_reviews']);
	    update_user_meta($user, '_orcid_display_invited_positions', $display_sections['display_invited_positions']);
	    update_user_meta($user, '_orcid_display_memberships', $display_sections['display_memberships']);
	    update_user_meta($user, '_orcid_display_qualifications', $display_sections['display_qualifications']);
	    update_user_meta($user, '_orcid_display_research_resources', $display_sections['display_research_resources']);
	    update_user_meta($user, '_orcid_display_services', $display_sections['display_services']);

    } else {
        // if no NEW data has been submitted, use values from the database as defaults in the form
        $orcid_id = get_user_meta($user, '_orcid_id', TRUE);
        $display_sections['display_personal'] = get_user_meta($user, '_orcid_display_personal', TRUE);
        $display_sections['display_education'] = get_user_meta($user, '_orcid_display_education', TRUE);
        $display_sections['display_employment'] = get_user_meta($user, '_orcid_display_employment', TRUE);
        $display_sections['display_works'] = get_user_meta($user, '_orcid_display_works', TRUE);
        $display_sections['display_fundings'] = get_user_meta($user, '_orcid_display_fundings', TRUE);
	    $display_sections['display_peer_reviews'] = get_user_meta($user, '_orcid_display_peer_reviews', TRUE);
	    $display_sections['display_invited_positions'] = get_user_meta($user, '_orcid_display_invited_positions', TRUE);
	    $display_sections['display_memberships'] = get_user_meta($user, '_orcid_display_memberships', TRUE);
	    $display_sections['display_qualifications'] = get_user_meta($user, '_orcid_display_qualifications', TRUE);
	    $display_sections['display_research_resources'] = get_user_meta($user, '_orcid_display_research_resources', TRUE);
	    $display_sections['display_services'] = get_user_meta($user, '_orcid_display_services', TRUE);
    }
    ?>
    <div class="wrap">
        <h2>ORCiD Profile Settings</h2>
        <?php if ($valid != '') {
            echo "<h2>Oops, looks like there was a problem:<br />$valid</h2>";
        }
        ?>
        <form method="POST" id="orcidForm">
            <!-- wp_nonce_field used for security (see above comment) -->
            <?php wp_nonce_field('orcid_nonce'); ?>
            <!-- need to replace table with CSS -->
            <table>
                <tr>
                    <td><label for="orcid_id">ORCiD ID</label></td>
                    <td>
                        <input type="text" name="orcid_id" id="orcid_id"
                               value="<?php echo esc_attr__($orcid_id); ?>">
                    </td>
                </tr>
                <!-- need a list of checkboxes here to hold display options -->
                <tr><td>Display Sections</td>
                    <td>
                        <div>
                        <input type="checkbox" id="display_personal" name="display_personal" value="yes"
                            <?php if ($display_sections['display_personal'] == 'yes') echo 'checked'; ?> />
                        <label for="display_personal">Personal</label>
                    </div>
                    <div>
                        <input type="checkbox" id="display_education" name="display_education" value="yes"
                            <?php if ($display_sections['display_education'] == 'yes') echo 'checked'; ?> />
                        <label for="display_education">Education</label>
                    </div>
                    <div>
                        <input type="checkbox" id="display_employment" name="display_employment" value="yes"
                            <?php if ($display_sections['display_employment'] == 'yes') echo 'checked'; ?> />
                        <label for="display_employment">Employment</label>
                    </div>
                    <div>
                        <input type="checkbox" id="display_works" name="display_works" value="yes"
                        <?php if ($display_sections['display_works'] == 'yes') echo 'checked'; ?> />
                        <label for="display_works">Works</label>
                    </div>
                    <div>
                        <input type="checkbox" id="display_fundings" name="display_fundings" value="yes"
                            <?php if ($display_sections['display_fundings'] == 'yes') echo 'checked'; ?> />
                        <label for="display_fundings">Fundings</label>
                    </div>
                        <div>
                            <input type="checkbox" id="display_peer_reviews" name="display_peer_reviews" value="yes"
			                    <?php if ($display_sections['display_peer_reviews'] == 'yes') echo 'checked'; ?> />
                            <label for="display_peer_reviews">Peer Reviews</label>
                        </div>
                        <div>
                            <input type="checkbox" id="display_invited_positions" name="display_invited_positions" value="yes"
			                    <?php if ($display_sections['display_invited_positions'] == 'yes') echo 'checked'; ?> />
                            <label for="display_peer_reviews">Invited Positions</label>
                        </div>
                        <div>
                            <input type="checkbox" id="display_memberships" name="display_memberships" value="yes"
			                    <?php if ($display_sections['display_memberships'] == 'yes') echo 'checked'; ?> />
                            <label for="display_peer_reviews">Memberships</label>
                        </div>
                        <div>
                            <input type="checkbox" id="display_qualifications" name="display_qualifications" value="yes"
			                    <?php if ($display_sections['display_qualifications'] == 'yes') echo 'checked'; ?> />
                            <label for="display_peer_reviews">Qualifications</label>
                        </div>
                        <div>
                            <input type="checkbox" id="display_research_resources" name="display_research_resources" value="yes"
			                    <?php if ($display_sections['display_research_resources'] == 'yes') echo 'checked'; ?> />
                            <label for="display_peer_reviews">Research Resources</label>
                        </div>
                        <div>
                            <input type="checkbox" id="display_services" name="display_services" value="yes"
			                    <?php if ($display_sections['display_services'] == 'yes') echo 'checked'; ?> />
                            <label for="display_peer_reviews">Services</label>
                        </div>
                    </td>

                </tr>
                <tr>
                    <td><input type="submit" name="submit" value="Save Settings" class="button-primary"/></td>
                </tr>
            </table>
        </form>
    </div>

    <div class="wrap" id="orcid_wrapper">
        <?php
        if($download_from_orcid_flag) {
            echo '<p>Downloading XML data from orcid.org</p>' . PHP_EOL;
            $orcid_xml = download_orcid_data($orcid_id);
            update_user_meta($user, '_orcid_xml', $orcid_xml);
            //
            // keep track of when download occurred
            update_user_meta($user, '_orcid_xml_download_time', strval(time()));
        } else{
            echo '<p>Using cached XML data</p>' . PHP_EOL;
            $orcid_xml = get_user_meta($user, '_orcid_xml', TRUE);
        }

        $orcid_html = format_orcid_data_as_html($orcid_xml, $display_sections);
        echo $orcid_html;
        ?>
    </div>
<?php
}
?>
