<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('wpotp_method');
delete_option('wpotp_sms_url');
delete_option('wpotp_sms_key');
delete_option('wpotp_btn_color');

// Multisite support
if (is_multisite()) {
    global $wpdb;

    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);

        delete_option('wpotp_method');
        delete_option('wpotp_sms_url');
        delete_option('wpotp_sms_key');
        delete_option('wpotp_btn_color');

        restore_current_blog();
    }
}