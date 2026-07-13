<?php

// RATE LIMIT FUNCTION
function wpotp_rate_limit($phone) {
    $key = 'wpotp_limit_' . md5($phone);
    $count = get_transient($key);

    if ($count && $count >= 5) {
        return false;
    }

    set_transient($key, ($count ? $count + 1 : 1), 300);
    return true;
}


// SEND OTP (SMS)
add_action('wp_ajax_send_otp_sms', 'wpotp_send_otp_sms');
add_action('wp_ajax_nopriv_send_otp_sms', 'wpotp_send_otp_sms');

function wpotp_send_otp_sms() {

    check_ajax_referer('wpotp_nonce', 'nonce');

    $phone = sanitize_text_field($_POST['phone']);

    if (!wpotp_rate_limit($phone)) {
        wp_send_json_error(['message' => 'Too many requests']);
    }

    $otp = wp_rand(100000, 999999);
    set_transient('otp_' . $phone, $otp, 300);

    wp_remote_post(esc_url_raw(get_option('wpotp_sms_url')), [
        'body' => [
            'api_key' => sanitize_text_field(get_option('wpotp_sms_key')),
            'to' => $phone,
            'message' => "OTP: $otp"
        ]
    ]);

    wp_send_json_success(['message' => 'OTP sent']);
}


// VERIFY OTP
add_action('wp_ajax_verify_otp_sms', 'wpotp_verify_otp_sms');
add_action('wp_ajax_nopriv_verify_otp_sms', 'wpotp_verify_otp_sms');

function wpotp_verify_otp_sms() {

    check_ajax_referer('wpotp_nonce', 'nonce');

    $phone = sanitize_text_field($_POST['phone']);
    $otp   = sanitize_text_field($_POST['otp']);

    if (get_transient('otp_' . $phone) == $otp) {

        wpotp_login_user($phone);
        delete_transient('otp_' . $phone);

        wp_send_json_success();
    }

    wp_send_json_error(['message' => 'Invalid OTP']);
}


// FIREBASE LOGIN
add_action('wp_ajax_nopriv_firebase_login', 'wpotp_firebase_login');

function wpotp_firebase_login() {

    check_ajax_referer('wpotp_nonce', 'nonce');

    $phone = sanitize_text_field($_POST['phone']);
    wpotp_login_user($phone);

    wp_send_json_success();
}


// COMMON LOGIN FUNCTION
function wpotp_login_user($phone) {

    $users = get_users([
        'meta_key' => 'phone',
        'meta_value' => $phone,
        'number' => 1
    ]);

    if (!empty($users)) {
        $user_id = $users[0]->ID;
    } else {
        $username = 'user_' . preg_replace('/[^0-9]/', '', $phone);

        if (username_exists($username)) {
            $username .= wp_rand(100,999);
        }

        $user_id = wp_create_user($username, wp_generate_password(), $username.'@otp.local');

        if (!is_wp_error($user_id)) {
            update_user_meta($user_id, 'phone', $phone);
        }
    }

    wp_set_auth_cookie($user_id);
}