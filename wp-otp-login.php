<?php
/*
Plugin Name: WP OTP Login
Plugin URI: https://yourwebsite.com/wp-otp-login
Description: Secure login system using One-Time Password (OTP) via Email or Phone (SMS). Allows users to authenticate without passwords.
Version: 1.0.0
Author: Rakib Dev Studio
Author URI: https://yourwebsite.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpotp
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) exit;

define('WPOTP_PATH', plugin_dir_path(__FILE__));
define('WPOTP_URL', plugin_dir_url(__FILE__));

require_once WPOTP_PATH . 'includes/form.php';
require_once WPOTP_PATH . 'includes/ajax.php';

/**
 * Enqueue Scripts
 */
function wpotp_enqueue_scripts() {

    wp_enqueue_style('wpotp-style', WPOTP_URL . 'assets/style.css', [], '1.0');

    wp_enqueue_script('wpotp-script', WPOTP_URL . 'assets/script.js', ['jquery'], '1.0', true);

    wp_localize_script('wpotp-script', 'wpotp_data', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wpotp_nonce'),
        'method'   => get_option('wpotp_method', 'email'),
        'btn_color'=> get_option('wpotp_btn_color', '#0073aa')
    ]);
}
add_action('wp_enqueue_scripts', 'wpotp_enqueue_scripts');

/**
 * Admin Menu
 */
function wpotp_admin_menu(){
    add_options_page('WP OTP Login', 'WP OTP Login', 'manage_options', 'wpotp-settings', 'wpotp_settings_page');
}
add_action('admin_menu', 'wpotp_admin_menu');

/**
 * Settings Page
 */
function wpotp_settings_page() {

    if (!current_user_can('manage_options')) return;

    $method = esc_attr(get_option('wpotp_method', 'email'));
?>
<div class="wrap">
<h2>WP OTP Login Settings</h2>

<form method="post" action="options.php">
<?php settings_fields('wpotp_group'); ?>

<h3>Login Method</h3>
<select name="wpotp_method">
    <option value="email" <?php selected($method, 'email'); ?>>Email Only</option>
    <option value="phone" <?php selected($method, 'phone'); ?>>Phone Only</option>
    <option value="both" <?php selected($method, 'both'); ?>>Both</option>
</select>

<h3>SMS API</h3>
<input type="text" name="wpotp_sms_url" value="<?php echo esc_attr(get_option('wpotp_sms_url')); ?>"><br><br>
<input type="text" name="wpotp_sms_key" value="<?php echo esc_attr(get_option('wpotp_sms_key')); ?>"><br><br>

<h3>Button Color</h3>
<input type="color" name="wpotp_btn_color" value="<?php echo esc_attr(get_option('wpotp_btn_color', '#0073aa')); ?>">

<?php submit_button(); ?>
</form>
</div>
<?php
}

/**
 * Register Settings
 */
function wpotp_register_settings(){

    register_setting('wpotp_group', 'wpotp_method', ['sanitize_callback'=>'sanitize_text_field']);
    register_setting('wpotp_group', 'wpotp_sms_url', ['sanitize_callback'=>'esc_url_raw']);
    register_setting('wpotp_group', 'wpotp_sms_key', ['sanitize_callback'=>'sanitize_text_field']);
    register_setting('wpotp_group', 'wpotp_btn_color', ['sanitize_callback'=>'sanitize_hex_color']);

}
add_action('admin_init', 'wpotp_register_settings');

/**
 * Default Options
 */
register_activation_hook(__FILE__, function(){
    add_option('wpotp_method', 'email');
    add_option('wpotp_btn_color', '#0073aa');
});