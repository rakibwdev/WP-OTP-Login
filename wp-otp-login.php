<?php
/*
Plugin Name: WP OTP Login
Description: Login with Email OTP (default) and Phone OTP (SMS API).
Version: 1.0
Author: Rakib Dev Studio
License: GPL2+
*/

if (!defined('ABSPATH')) exit;

define('WPOTP_PATH', plugin_dir_path(__FILE__));
define('WPOTP_URL', plugin_dir_url(__FILE__));

require_once WPOTP_PATH . 'includes/form.php';
require_once WPOTP_PATH . 'includes/ajax.php';

// Scripts
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style('wpotp-style', WPOTP_URL . 'assets/style.css');

    wp_enqueue_script('wpotp-script', WPOTP_URL . 'assets/script.js', ['jquery'], null, true);

    wp_localize_script('wpotp-script', 'wpotp_data', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpotp_nonce'),
        'method' => get_option('wpotp_method', 'email'),
        'btn_color' => get_option('wpotp_btn_color', '#0073aa')
    ]);
});

// Admin Menu
add_action('admin_menu', function(){
    add_options_page('WP OTP Login', 'WP OTP Login', 'manage_options', 'wpotp-settings', 'wpotp_settings_page');
});

// Settings Page
function wpotp_settings_page() {
?>
<div class="wrap">
<h2>WP OTP Login Settings</h2>

<form method="post" action="options.php">
<?php settings_fields('wpotp_group'); ?>

<h3>Login Method</h3>
<select name="wpotp_method">
<option value="email" <?php selected(get_option('wpotp_method'), 'email'); ?>>Email OTP (Default)</option>
<option value="phone" <?php selected(get_option('wpotp_method'), 'phone'); ?>>Phone OTP (SMS)</option>
</select>

<h3>SMS API (Optional)</h3>
<input type="text" name="wpotp_sms_url" placeholder="API URL" value="<?php echo esc_attr(get_option('wpotp_sms_url')); ?>"><br><br>
<input type="text" name="wpotp_sms_key" placeholder="API KEY" value="<?php echo esc_attr(get_option('wpotp_sms_key')); ?>"><br><br>

<h3>Button Color</h3>
<input type="color" name="wpotp_btn_color" value="<?php echo esc_attr(get_option('wpotp_btn_color', '#0073aa')); ?>">

<?php submit_button(); ?>
</form>
</div>
<?php
}

// Register settings
add_action('admin_init', function(){

    register_setting('wpotp_group', 'wpotp_method', ['sanitize_callback'=>'sanitize_text_field']);
    register_setting('wpotp_group', 'wpotp_sms_url', ['sanitize_callback'=>'esc_url_raw']);
    register_setting('wpotp_group', 'wpotp_sms_key', ['sanitize_callback'=>'sanitize_text_field']);
    register_setting('wpotp_group', 'wpotp_btn_color', [
    'sanitize_callback' => 'sanitize_hex_color'
]);

});