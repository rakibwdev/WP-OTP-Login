<?php

// SMS SEND
add_action('wp_ajax_send_otp_sms', 'send_otp_sms');
add_action('wp_ajax_nopriv_send_otp_sms', 'send_otp_sms');

function send_otp_sms() {

    $phone = sanitize_text_field($_POST['phone']);
    $otp = rand(100000,999999);

    set_transient('otp_'.$phone, $otp, 300);

    wp_remote_post(get_option('wpotp_sms_url'), [
        'body' => [
            'api_key' => get_option('wpotp_sms_key'),
            'to' => $phone,
            'message' => "OTP: $otp"
        ]
    ]);

    echo 'sent';
    wp_die();
}


// VERIFY SMS OTP
add_action('wp_ajax_verify_otp_sms', 'verify_otp_sms');
add_action('wp_ajax_nopriv_verify_otp_sms', 'verify_otp_sms');

function verify_otp_sms() {

    $phone = $_POST['phone'];
    $otp = $_POST['otp'];

    if (get_transient('otp_'.$phone) == $otp) {

        login_user($phone);
        delete_transient('otp_'.$phone);

        echo 'success';
    } else {
        echo 'invalid';
    }

    wp_die();
}


// FIREBASE LOGIN
add_action('wp_ajax_nopriv_firebase_login', 'firebase_login');

function firebase_login() {

    $phone = sanitize_text_field($_POST['phone']);
    login_user($phone);

    echo 'success';
    wp_die();
}


// COMMON LOGIN FUNCTION
function login_user($phone) {

    $users = get_users([
        'meta_key' => 'phone',
        'meta_value' => $phone
    ]);

    if ($users) {
        $user_id = $users[0]->ID;
    } else {
        $user_id = wp_create_user($phone, wp_generate_password(), $phone.'@otp.com');
        update_user_meta($user_id, 'phone', $phone);
    }

    wp_set_auth_cookie($user_id);
}