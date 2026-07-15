<?php
if (!defined('ABSPATH')) exit;

// Send OTP
add_action('wp_ajax_nopriv_wpotp_send', 'wpotp_send');
add_action('wp_ajax_wpotp_send', 'wpotp_send');

function wpotp_send(){

    check_ajax_referer('wpotp_nonce', 'nonce');

    $input = sanitize_text_field($_POST['input']);
    $type  = sanitize_text_field($_POST['type']);

    $otp = wp_rand(100000, 999999);
    set_transient('wpotp_' . md5($input), $otp, 300);

    if($type === 'email'){
        wp_mail($input, 'Your OTP Code', 'Your OTP is: ' . $otp);
    }

    if($type === 'phone'){
        wp_remote_post(get_option('wpotp_sms_url'), [
            'body' => [
                'api_key' => get_option('wpotp_sms_key'),
                'to' => $input,
                'message' => "OTP: $otp"
            ]
        ]);
    }

    wp_send_json_success();
}

// Verify OTP
add_action('wp_ajax_nopriv_wpotp_verify', 'wpotp_verify');
add_action('wp_ajax_wpotp_verify', 'wpotp_verify');

function wpotp_verify(){

    check_ajax_referer('wpotp_nonce', 'nonce');

    $input = sanitize_text_field($_POST['input']);
    $otp   = sanitize_text_field($_POST['otp']);

    $saved = get_transient('wpotp_' . md5($input));

    if($saved == $otp){

        wpotp_login($input);
        delete_transient('wpotp_' . md5($input));

        wp_send_json_success();
    }

    wp_send_json_error();
}

// Login / Register
function wpotp_login($input){

    if(is_email($input)){
        $user = get_user_by('email', $input);

        if(!$user){
            $id = wp_create_user($input, wp_generate_password(), $input);
        } else {
            $id = $user->ID;
        }
    } else {
        $users = get_users([
            'meta_key'=>'phone',
            'meta_value'=>$input,
            'number'=>1
        ]);

        if($users){
            $id = $users[0]->ID;
        } else {
            $username = 'user_' . wp_rand(1000,9999);
            $id = wp_create_user($username, wp_generate_password(), $username.'@otp.local');
            update_user_meta($id, 'phone', $input);
        }
    }

    wp_set_auth_cookie($id);
}