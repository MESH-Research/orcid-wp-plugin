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

//add_action( 'the_content', 'display_orcid_info' );
//
//function display_orcid_info ( $content ) {
//    // return $content .= '<p>Thank you for reading!</p>';
//    $orcidID = "0000-0003-0265-9119"; // Alan Munn
//    //$orcidID = "0000-0003-1822-3109";  // Bronson Hui
//    //$orcidID = "0000-0003-3953-7940"; // Chris Long (U of CO at Boulder)
//    //$orcidID = "0000-0002-5251-0307"; // Kathleen Fitzpatrick
//    //$orcidID = "0000-0002-8143-2408"; // Scott Schopieray
//
//    $orcid_data = get_orcid_data($orcidID);
//    return $content .= $orcid_data;
//}

/************************
 * WORDPRESS HOOKS
 ************************/

// ++++++++++++++++++++++++++++++++++++++++++++
// this is where most of the lookup and validate stuff is
// require_once(plugin_dir_path(__FILE__) . 'ip_import_engine.php');
// ++++++++++++++++++++++++++++++++++++++++++++

//
// add actions to the (de)install activation hooks
//
register_activation_hook(__FILE__, 'orcid_install');
register_deactivation_hook(__FILE__, 'orcid_uninstall');

add_action('wp_enqueue_scripts', 'orcid_scripts');
add_action('admin_enqueue_scripts', 'orcid_scripts');

add_action('admin_menu', 'orcid_create_menu');

// ++++++++++++++++++++++++++++++++++++++++++++
//
// - create an EVENT called: impactpubs_daily_update
// - schedule it to run daily
// - add an ACTION to this EVENT: impactpubs_update_lists
// - this action updates the publication for for ALL users
//
// want to use timeout , NOT daily update
//
// add_action('impactpubs_daily_update', 'impactpubs_update_lists');
// ++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++
//
// shortcode
//
// see: https://www.smashingmagazine.com/2012/05/wordpress-shortcodes-complete-guide/
// When a shortcode is inserted in a WordPress post or page,
// it is replaced with some other content.
// In other words, we instruct WordPress to find the macro
// that is in square brackets ([]) and replace it with the
// appropriate dynamic content, which is produced by a PHP function.
//
// - create a shortcode: publications
// - set the function that gets called when the shortcode is used: impactpubs_display_pubs
// - shortcodes can be called with arguments: [publications name=' . $user_ob->user_login . ']
//
// add_shortcode('publications', 'impactpubs_display_pubs');
// ++++++++++++++++++++++++++++++++++++++++++++

//installation procedures:
//schedule daily event to update publication lists
function orcid_install()
{
    // want to use caching and timeouts , NOT daily update
    // wp_schedule_event(current_time('timestamp'), 'daily', 'impactpubs_daily_update');
}

// uninstallation procedures:
// remove scheduled tasks
function orcid_uninstall()
{
    // wp_clear_scheduled_hook('impactpubs_daily_update');
}

// add javascript and stylesheets to both the admin page and front-end.
// hooked by 'wp_enqueue_scripts' and 'admin_enqueue_scripts'
function orcid_scripts()
{
    // wp_enqueue_style('ip_style', plugins_url('ip_style.css', __FILE__));
    // wp_enqueue_script('ip_script', plugins_url('ip_script.js', __FILE__), array('jquery'), null, true);
}

//create the admin menu
//hooked by admin_menu event
function orcid_create_menu()
{
    add_menu_page('My ORCiD Retrieval and Display Information', 'My ORCiD Profile',
        'edit_posts', __FILE__, 'orcid_settings_form');
}


// create and handle the settings form
// hooked by orcid_create_menu
//
// 1. process form submission if it has occured
// 2. display the form
// 3. below the form is where the results (or the error) gets displayed
function orcid_settings_form()
{
    $user_ob = wp_get_current_user();
    $user = $user_ob->ID;
    //=================================================
    // we are not validating orcid_id's for now
    // just leave this as '' (blank)
    $valid = '';
    //=================================================
    // process a form submission if it has occurred
    if (isset($_POST['submit'])) {
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
        // what is: check_admin_referer('impactpubs_nonce')?????
        // this used for security to validate form data came from current site.
        // see: https://codex.wordpress.org/Function_Reference/check_admin_referer
        // nonce: https://wordpress.org/support/article/glossary/#nonce
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++
        check_admin_referer('orcid_nonce');

        $orcid_id = $_POST['orcid_id'];

        // for now we are not checking for validation errors
        update_user_meta($user, '_orcid_id', $orcid_id);

    } else {
        // if no NEW data has been submitted, use values from the database as defaults in the form
        $orcid_id = get_user_meta($user, '_orcid_id', TRUE);
    }
    ?>
    <div class="wrap">
        <h2>ORCiD Profile Settings</h2>
        <?php if ($valid != '') {
            echo "<h2>Oops, looks like there was a problem:<br />$valid</h2>";
        }
        ?>
        <form method="POST" id="impactpubsForm">
            <!-- wp_nonce_field used for security -->
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
                <tr>
                    <td><input type="submit" name="submit" value="Save Settings" class="button-primary"/></td>
                </tr>
            </table>
        </form>
    </div>

    <div class="wrap" id="orcid_wrapper">
        <?php
        // get data from orcid.org
        $orcid_xml = download_orcid_data($orcid_id);
        // format as xml
        $orcid_html = format_orcid_data_as_html($orcid_xml);
        echo $orcid_html;
        ?>
    </div>
    <?php
}
?>
