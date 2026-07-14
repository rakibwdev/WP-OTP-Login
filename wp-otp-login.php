<?php
/*
Plugin Name: WP OTP Login
Description: Login with phone number using OTP (SMS API + Firebase support)
Version: 1.0
Author: Rakib Dev Studio
*/

if (!defined('ABSPATH')) exit;

define('WPOTP_PATH', plugin_dir_path(__FILE__));
define('WPOTP_URL', plugin_dir_url(__FILE__));

// Includes
require_once WPOTP_PATH . 'includes/form.php';
require_once WPOTP_PATH . 'includes/ajax.php';

// Scripts
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style('wpotp-style', WPOTP_URL . 'assets/style.css');

    wp_enqueue_script('wpotp-script', WPOTP_URL . 'assets/script.js', ['jquery'], null, true);

    // Firebase SDK
    wp_enqueue_script('firebase-app', 'https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js', [], null, true);
    wp_enqueue_script('firebase-auth', 'https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js', [], null, true);

    wp_localize_script('wpotp-script', 'wpotp_data', [
        // 'ajax_url' => admin_url('admin-ajax.php'),
        // 'provider' => get_option('wpotp_provider'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpotp_nonce'),
        'provider' => get_option('wpotp_provider'),

        'firebase' => [
            'apiKey' => get_option('wpotp_firebase_apiKey'),
            'authDomain' => get_option('wpotp_firebase_authDomain'),
            'projectId' => get_option('wpotp_firebase_projectId'),
            'appId' => get_option('wpotp_firebase_appId'),
        ]
    ]);
});


// ADMIN SETTINGS
add_action('admin_menu', function(){
    add_options_page('WP OTP Login', 'WP OTP Login', 'manage_options', 'wpotp-settings', 'wpotp_settings_page');
});

function wpotp_settings_page() {
?>
<div class="wrap">
<h2>WP OTP Login Settings</h2>

<form method="post" action="options.php">
<?php settings_fields('wpotp_group'); ?>

<h3>OTP Provider</h3>
<select name="wpotp_provider">
    <option value="firebase" <?php selected(get_option('wpotp_provider'), 'firebase'); ?>>Firebase</option>
    <option value="sms" <?php selected(get_option('wpotp_provider'), 'sms'); ?>>SMS API</option>
</select>

<h3>SMS API</h3>
<input type="text" name="wpotp_sms_url" placeholder="API URL" value="<?php echo esc_attr(get_option('wpotp_sms_url')); ?>"><br><br>
<input type="text" name="wpotp_sms_key" placeholder="API KEY" value="<?php echo get_option('wpotp_sms_key'); ?>"><br><br>

<h3>Firebase Config</h3>
<input type="text" name="wpotp_firebase_apiKey" placeholder="API Key" value="<?php echo get_option('wpotp_firebase_apiKey'); ?>"><br><br>
<input type="text" name="wpotp_firebase_authDomain" placeholder="Auth Domain" value="<?php echo get_option('wpotp_firebase_authDomain'); ?>"><br><br>
<input type="text" name="wpotp_firebase_projectId" placeholder="Project ID" value="<?php echo get_option('wpotp_firebase_projectId'); ?>"><br><br>
<input type="text" name="wpotp_firebase_appId" placeholder="App ID" value="<?php echo get_option('wpotp_firebase_appId'); ?>"><br><br>

<?php submit_button(); ?>
</form>
</div>
<?php
}

add_action('admin_init', function(){
    register_setting('wpotp_group', 'wpotp_provider');
    register_setting('wpotp_group', 'wpotp_sms_url');
    register_setting('wpotp_group', 'wpotp_sms_key');

    register_setting('wpotp_group', 'wpotp_firebase_apiKey');
    register_setting('wpotp_group', 'wpotp_firebase_authDomain');
    register_setting('wpotp_group', 'wpotp_firebase_projectId');
    register_setting('wpotp_group', 'wpotp_firebase_appId');
});
