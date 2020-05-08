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

define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
include( MY_PLUGIN_PATH . 'orcid-functions.php');
// in seconds
define( 'ORCID_CACHE_TIMEOUT', 3600);
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
 * installation procedures:
 * schedule daily event to update publication lists
 */
function orcid_install()
{
    // empty for now
}

/**
 * uninstallation procedures:
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

        $orcid_id = $_POST['orcid_id'];

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
        if(get_user_meta($user, '_orcid_xml', TRUE) == ''){
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

        $display_sections['displayPersonal'] = $_POST['displayPersonal'];
        $display_sections['displayEducation'] = $_POST['displayEducation'];
        $display_sections['displayEmployment'] = $_POST['displayEmployment'];
        $display_sections['displayWorks'] = $_POST['displayWorks'];
        $display_sections['displayFundings'] = $_POST['displayFundings'];
        $display_sections['displayPeerReviews'] = $_POST['displayPeerReviews'];

        update_user_meta($user, '_orcid_id', $orcid_id);
        update_user_meta($user, '_orcid_display_personal', $display_sections['displayPersonal']);
        update_user_meta($user, '_orcid_display_education', $display_sections['displayEducation']);
        update_user_meta($user, '_orcid_display_employment', $display_sections['displayEmployment']);
        update_user_meta($user, '_orcid_display_works', $display_sections['displayWorks']);
        update_user_meta($user, '_orcid_display_fundings', $display_sections['displayFundings']);
        update_user_meta($user, '_orcid_display_peer_reviews', $display_sections['displayPeerReviews']);

    } else {
        // if no NEW data has been submitted, use values from the database as defaults in the form
        $orcid_id = get_user_meta($user, '_orcid_id', TRUE);
        $display_sections['displayPersonal'] = get_user_meta($user, '_orcid_display_personal', TRUE);
        $display_sections['displayEducation'] = get_user_meta($user, '_orcid_display_education', TRUE);
        $display_sections['displayEmployment'] = get_user_meta($user, '_orcid_display_employment', TRUE);
        $display_sections['displayWorks'] = get_user_meta($user, '_orcid_display_works', TRUE);
        $display_sections['displayFundings'] = get_user_meta($user, '_orcid_display_fundings', TRUE);
        $display_sections['displayPeerReviews'] = get_user_meta($user, '_orcid_display_peer_reviews', TRUE);
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
                        <input type="checkbox" id="displayPersonal" name="displayPersonal" value="yes"
                            <?php if ($display_sections['displayPersonal'] == 'yes') echo 'checked'; ?> />
                        <label for="displayPersonal">Personal</label>
                    </div>
                    <div>
                        <input type="checkbox" id="displayEducation" name="displayEducation" value="yes"
                            <?php if ($display_sections['displayEducation'] == 'yes') echo 'checked'; ?> />
                        <label for="displayEducation">Education</label>
                    </div>
                    <div>
                        <input type="checkbox" id="displayEmployment" name="displayEmployment" value="yes"
                            <?php if ($display_sections['displayEmployment'] == 'yes') echo 'checked'; ?> />
                        <label for="displayEmployment">Employment</label>
                    </div>
                    <div>
                        <input type="checkbox" id="displayWorks" name="displayWorks" value="yes"
                        <?php if ($display_sections['displayWorks'] == 'yes') echo 'checked'; ?> />
                        <label for="displayWorks">Works</label>
                    </div>
                    <div>
                        <input type="checkbox" id="displayFundings" name="displayFundings" value="yes"
                            <?php if ($display_sections['displayFundings'] == 'yes') echo 'checked'; ?> />
                        <label for="displayFundings">Fundings</label>
                    </div>
                    <div>
                        <input type="checkbox" id="displayPeerReviews" name="displayPeerReviews" value="yes"
                            <?php if ($display_sections['displayPeerReviews'] == 'yes') echo 'checked'; ?> />
                        <label for="displayPeerReviews">Peer Reviews</label>
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
            // echo "<h4>Downloading XML data from orcid.org</h4>" . PHP_EOL;
            $orcid_xml = download_orcid_data($orcid_id);
            update_user_meta($user, '_orcid_xml', $orcid_xml);
            //
            // keep track of when download occurred
            update_user_meta($user, '_orcid_xml_download_time', strval(time()));
        } else{
            // echo "<h4>Using cached XML data</h4>" . PHP_EOL;
            $orcid_xml = get_user_meta($user, '_orcid_xml', TRUE);
        }

        $orcid_html = format_orcid_data_as_html($orcid_xml, $display_sections);
        echo $orcid_html;
        ?>
    </div>
<?php
}
?>
