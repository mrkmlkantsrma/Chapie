<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://Chapie.com
 * @since             1.0.0
 * @package           Chapie
 *
 * @wordpress-plugin
 * Plugin Name:       Chapie
 * Plugin URI:        https://https://Chapie.com
 * Description:       Chapie offers more than just a communication solution to WordPress site users. This WordPress plugin enriches your customer engagement experience with other marketing capabilities.
 * Version:           1.0.0
 * Author:            Chapie
 * Author URI:        https://https://Chapie.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chapie
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
// Plugin version.
define( 'CHAPIE_VERSION', time() );
define( 'CHAPIE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHAPIE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CHAPIE_PLUGIN_FILE', __FILE__ );
define( 'CHAPIE_PLUGIN_ROOT', dirname( __FILE__ ) );
define( 'CHAPIE_PLUGIN_INCLUDES', CHAPIE_PLUGIN_DIR . 'includes/' );
define( 'CHAPIE_PLUGIN_PUBLIC_IMAGES_URL', CHAPIE_PLUGIN_URL . 'public/images' );
$upload_dir = wp_upload_dir();
define( 'CHAPIE_UPLOAD_DIR', $upload_dir['basedir'] );
define( 'CHAPIE_UPLOAD_URL', $upload_dir['baseurl'] );

define( 'CHAPIE_CHATBOX_TABLE', $wpdb->prefix . 'chapie_chatbox_meta' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-chapie-activator.php
 */
function activate_chapie() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chapie-activator.php';
	Chapie_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chapie-deactivator.php
 */
function deactivate_chapie() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chapie-deactivator.php';
	Chapie_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_chapie' );
register_deactivation_hook( __FILE__, 'deactivate_chapie' );

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-chapie.php';

function insert_users_into_custom_table() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'chapie_chat_usermeta';

  $users = get_users();

  foreach ($users as $user) {

      $user_id = $user->ID;
      $email = $user->user_email;
      $name_parts = explode(' ', $user->display_name);
      $fname = isset($name_parts[0]) ? $name_parts[0] : '';
      $lname = isset($name_parts[1]) ? $name_parts[1] : '';
      $unique_id = rand(time(), 100000000);
      $status = 'Offline now';
      $img = get_avatar_url($user_id);

      $user_exists = $wpdb->get_var($wpdb->prepare(
          "SELECT COUNT(*) FROM $table_name WHERE user_id = %d OR email = %s",
          $user_id, $email
      ));

      if ($user_exists == 0) {
          $wpdb->insert(
              $table_name,
              array(
                  'user_id' => $user_id,
                  'unique_id' => $unique_id,
                  'fname' => $fname,
                  'lname' => $lname,
                  'email' => $email,
                  'password' => $unique_id,
                  'img' => $img,
                  'status' => $status
              ),
              array(
                  '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
              )
          );
      }
  }

  if (is_user_logged_in()) {

      $current_user = wp_get_current_user();
      $user_id = $current_user->ID;

      global $wpdb;
      $chapie_chat_usermeta = $wpdb->prefix . 'chapie_chat_usermeta';

      $unique_id = $wpdb->get_var($wpdb->prepare(
          "SELECT unique_id FROM $chapie_chat_usermeta WHERE user_id = %d",
          $user_id
      ));

      if ($unique_id) {

          if (!session_id()) {
              session_start();
          }
          $_SESSION['unique_id'] = $unique_id;
          $_SESSION['user_id'] = $user_id;
      }
  }

}

add_action('init', 'insert_users_into_custom_table');

add_action('wp_footer', 'call_chapie_chatbox');
function call_chapie_chatbox() {
  require plugin_dir_path( __FILE__ ) . 'public/partials/chapie-public-display.php';
}

function ajax_chapie() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-chapie-ajax-loader.php';
  Chapie_Ajax::register_ajax_actions();
}
add_action( 'init', 'ajax_chapie' );

/**
 * admin-menu of plugin.
 */
add_action('admin_menu', 'custom_plugin_menu');
function custom_plugin_menu() {
    add_menu_page('Chapie','Chapie','manage_options','chapie-plugin-menu','chapie_plugin_page','dashicons-buddicons-pm', 29);
    add_submenu_page('chapie-plugin-menu','Settings','Settings','manage_options','chapie-settings','chapie_settings_page');
}

function chapie_plugin_page() {
    echo '<h2>chapie Plugin Main Page</h2>';
}

function chapie_settings_page() {
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_chapie() {

	$plugin = new Chapie();
	$plugin->run();

}
run_chapie();
