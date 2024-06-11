<?php
/**
 * Plugin Name: Brro Web Development Tools
 * Plugin URI: https://github.com/ronaldpostma/brro-webdev
 * Description: Brro web development tools
 * Version: 1.4.9
 * Author: Ronald Postma 
 * Author URI: https://brro.nl/
 * 
 */
//
// Include php function files
// 
require_once plugin_dir_path(__FILE__) . '/php/brro-webdev-settings.php';
// 
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . '/php/brro-webdev-admin.php';
}
// 
require_once plugin_dir_path(__FILE__) . '/php/brro-webdev-global.php';
//
// Load script for Elementor Editor Panel
add_action( 'elementor/editor/after_enqueue_scripts', 'brro_enqueue_script_elementor_editor' );
function brro_enqueue_script_elementor_editor() {
    $developer_mode = get_option('brro_developer_mode', 0);
    if ($developer_mode == 1 && is_user_logged_in() ) {
        wp_enqueue_script( 'brro-webdev-backend-elementor-script', plugins_url( '/js/brro-webdev-backend-elementor-script.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
        // Localize script with data from your settings
        $script_data = array(
            'desktopEnd' => get_option('brro_desktop_end'),
            'desktopRef' => get_option('brro_desktop_ref'),
            'desktopStart' => get_option('brro_desktop_start'),
            'tabletRef'  => get_option('brro_tablet_ref'),
            'tabletStart'  => get_option('brro_tablet_start'),
            'mobileRef'  => get_option('brro_mobile_ref'),
            'mobileStart'  => get_option('brro_mobile_start'),
            'developerMode'  => get_option('brro_developer_mode'),
        );
        wp_localize_script('brro-webdev-backend-elementor-script', 'pluginSettings', $script_data);
    }
}
//
// Load script for site back- and frontend
add_action( 'wp_enqueue_scripts', 'brro_enqueue_script_elementor_frontend' );
function brro_enqueue_script_elementor_frontend() {
    $developer_mode = get_option('brro_developer_mode', 0);
    if ($developer_mode == 1 && is_user_logged_in() ) {
        wp_enqueue_script( 'brro-webdev-frontend-inspector-script', plugins_url( '/js/brro-webdev-frontend-inspector-script.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
    }
}
//
// Load CSS
add_action( 'wp_enqueue_scripts', 'brro_enqueue_css_frontend' );
function brro_enqueue_css_frontend() {
    $developer_mode = get_option('brro_developer_mode', 0); 
    // Enqueue only if '$developer_mode' is "1 / Developer Mode"
    if ($developer_mode == 1 && is_user_logged_in() ) {
        // CSS file for inspector mode 
        wp_enqueue_style('brro-webdev-inspector-style', plugins_url( '/css/brro-webdev-inspector-style.css', __FILE__ ), [], '1.0.0' );
    }
}
//
// Load assets for wp admin area
add_action( 'admin_enqueue_scripts', 'brro_webdev_enqueue_admin_assets');
function brro_webdev_enqueue_admin_assets() {
    if (is_admin()) {
        // For all users
        wp_enqueue_style( 'brro-webdev-wp-admin', plugins_url( '/css/brro-webdev-wp-admin.css', __FILE__ ), [], '1.0.0', 'all' );
        wp_enqueue_script( 'brro-webdev-wp-admin', plugins_url( '/js/brro-webdev-wp-admin.js', __FILE__ ), ['jquery'], '1.0.0', true );
        // Localize script with data from your settings
        $script_data = array(
            'helpUrl' => get_option('brro_client_help_url'),
        );
        wp_localize_script('brro-webdev-wp-admin', 'pluginSettings', $script_data);
        // 
        // For specific users
        $user = get_current_user_id();
        $get_editors = get_option('brro_editors', '2,3,4,5');
        $editors = array_filter(array_map('intval', explode(',', $get_editors)), function($id) {
		    return $id > 0;
	    }); 
        // Client user / editors
        if (in_array($user, $editors)) {
            wp_enqueue_style( 'brro-webdev-wp-admin-editors', plugins_url( '/css/brro-webdev-wp-admin-editors.css', __FILE__ ), [], '1.0.0', 'all' );
        } 
        // Main Brro admin & 3rd parties
        if ( current_user_can('administrator') ) {
            wp_enqueue_style( 'brro-webdev-wp-admin-admin', plugins_url( '/css/brro-webdev-wp-admin-admin.css', __FILE__ ), [], '1.0.0', 'all' );
        }
    }
}
//
// Custom CSS for inspector mode
function brro_add_inspector_css() {
    $developer_mode = get_option('brro_developer_mode', 0); 
    // Enqueue only if '$developer_mode' is "1 / Developer Mode"
    if ($developer_mode == 1 && is_user_logged_in() ) {
        // Fetching individual settings for each condition
        $blend_mode_setting = get_option('brro_blend_mode', 'screen'); // Default for blend mode
        $parent_border_color = get_option('brro_parent_border_color', '#ff0000'); // Example default color
        $child_border_color = get_option('brro_child_border_color', '#00ff00'); // Example default color
        $child_child_border_color = get_option('brro_child_child_border_color', '#0000ff'); // Example default color
        $widget_text_color = get_option('brro_widget_text_color', '#ddd'); // Example default color
        $desktopEnd = get_option('brro_desktop_end', '1600px');
        // Constructing the CSS string with dynamic values
        $custom_css = "
        .elementor-container-inspector .e-con::before,
        .inspect-parent .e-con::before,
        .inspect-child .e-con::before,
        .inspect-child-child .e-con::before,
        .elementor-container-inspector .elementor-widget::before,
        .inspect-widget .elementor-widget::before {
            mix-blend-mode: {$blend_mode_setting};
        }
        .elementor-container-inspector  .e-con.e-parent::before,
        .inspect-parent .e-con.e-parent::before {
            border-color: {$parent_border_color};
        }
        .elementor-container-inspector  .e-con.e-child::before,
        .inspect-child .e-con.e-child::before {
            border-color: {$child_border_color};
        }
        .elementor-container-inspector  .e-con.e-child .e-con.e-child::before,
        .inspect-child-child .e-con.e-child .e-con.e-child::before {
            border-color: {$child_child_border_color};
        }
        .elementor-container-inspector .elementor-widget::before,
        .inspect-widget .elementor-widget::before {
            color: {$widget_text_color};
        }
        .inspect-edges span.edge.inner.left {
            left: calc(50% - ({$desktopEnd}px / 2) - 100%);
        }
        .inspect-edges span.edge.inner.right {
            left: calc(50% + ({$desktopEnd}px / 2));
        }";

        // Outputting the CSS
        echo '<style>' . $custom_css . '</style>';
    }
}
add_action('wp_head', 'brro_add_inspector_css');
/*
*
* Update mechanism
*
*/
function brro_check_for_plugin_update($checked_data) {
    if (empty($checked_data->checked)) return $checked_data;

    // Define the plugin slug
    $plugin_slug = 'brro-webdev';
    $plugin_path = plugin_basename(__FILE__);

    // Fetch the latest plugin info from your custom URI
    $response = brro_get_plugin_update_info();

    // Ensure the plugin_path key is set and valid before comparing versions
    if ($response && isset($checked_data->checked[$plugin_path]) && version_compare($checked_data->checked[$plugin_path], $response->new_version, '<')) {
        $checked_data->response[$plugin_path] = (object) [
            'url' => $response->url,
            'slug' => $plugin_slug,
            'package' => $response->package,
            'new_version' => $response->new_version,
            'tested' => $response->tested,
        ];
    }

    return $checked_data;
}
add_filter('pre_set_site_transient_update_plugins', 'brro_check_for_plugin_update');
function brro_get_plugin_update_info() {
    $update_info_url = 'https://base.brro.nl/git-webhook/brro-plugin-info.json';
    $response = wp_remote_get($update_info_url);

    if (is_wp_error($response)) {
        return false; // Bail early on request error
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (!is_null($data)) {
        return $data;
    }

    return false;
}