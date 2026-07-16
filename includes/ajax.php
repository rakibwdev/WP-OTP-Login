<?php
if (!defined('ABSPATH')) exit;

/**
 * SEND OTP
 */
add_action('wp_ajax_nopriv_wpotp_send', 'wpotp_send');
add_action('wp_ajax_wpotp_send', 'wpotp_send');

function wpotp_send(){

    check_ajax_referer('wpotp_nonce', 'nonce');

    $input = isset($_POST['input']) ? sanitize_text_field($_POST['input']) : '';
    $type  = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $ip    = $_SERVER['REMOTE_ADDR'];

    if(empty($input)){
        wp_send_json_error(['msg' => 'Input required']);
    }

    // Normalize phone
    if($type === 'phone'){
        $input = preg_replace('/[^0-9]/', '', $input);
    }

    // Validate email
    if($type === 'email' && !is_email($input)){
        wp_send_json_error(['msg' => 'Invalid email']);
    }

    /**
     * 🔐 RATE LIMIT SYSTEM
     */

    // 1️⃣ Cooldown (1 request per 60 sec)
    $cooldown_key = 'wpotp_cd_' . md5($input);
    if(get_transient($cooldown_key)){
        wp_send_json_error(['msg' => 'Wait 60 seconds before requesting again']);
    }

    // 2️⃣ Max 3 requests per 5 minutes (IP + input)
    $limit_key = 'wpotp_limit_' . md5($input . $ip);
    $attempts  = (int) get_transient($limit_key);

    if($attempts >= 3){
        wp_send_json_error(['msg' => 'Too many OTP requests. Try again later']);
    }

    set_transient($limit_key, $attempts + 1, 5 * MINUTE_IN_SECONDS);
    set_transient($cooldown_key, true, 60);

    /**
     * 🔢 GENERATE OTP
     */
    $otp = wp_rand(100000, 999999);

    set_transient('wpotp_' . md5($input), $otp, 300); // 5 min

    /**
     * 📧 SEND EMAIL
     */
    if($type === 'email'){
        wp_mail($input, 'Your OTP Code', 'Your OTP is: ' . $otp);
    }

    /**
     * 📱 SEND SMS
     */
    if($type === 'phone'){

        $response = wp_remote_post(get_option('wpotp_sms_url'), [
            'timeout' => 15,
            'body' => [
                'api_key' => get_option('wpotp_sms_key'),
                'to' => $input,
                'message' => "Your OTP is: $otp"
            ]
        ]);

        // Optional: check error
        if(is_wp_error($response)){
            wp_send_json_error(['msg' => 'SMS sending failed']);
        }
    }

    do_action('wpotp_after_send', $input, $otp);

    wp_send_json_success([
        'msg' => 'OTP sent successfully',
        'cooldown' => 60
    ]);
}


/**
 * VERIFY OTP
 */
add_action('wp_ajax_nopriv_wpotp_verify', 'wpotp_verify');
add_action('wp_ajax_wpotp_verify', 'wpotp_verify');

function wpotp_verify(){

    check_ajax_referer('wpotp_nonce', 'nonce');

    $input = isset($_POST['input']) ? sanitize_text_field($_POST['input']) : '';
    $otp   = isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';

    if(empty($input) || empty($otp)){
        wp_send_json_error(['msg' => 'All fields required']);
    }

    $key   = 'wpotp_' . md5($input);
    $saved = get_transient($key);

    // OTP expired
    if(!$saved){
        wp_send_json_error(['msg' => 'OTP expired']);
    }

    /**
     * 🔐 VERIFY ATTEMPT LIMIT
     */
    $attempt_key = 'wpotp_attempts_' . md5($input);
    $attempts = (int) get_transient($attempt_key);

    if($attempts >= 5){
        wp_send_json_error(['msg' => 'Too many attempts. Try again later']);
    }

    // Wrong OTP
    if((string)$saved !== (string)$otp){
        set_transient($attempt_key, $attempts + 1, 300);
        wp_send_json_error(['msg' => 'Invalid OTP']);
    }

    /**
     * ✅ SUCCESS
     */
    delete_transient($key);
    delete_transient($attempt_key);

    $user_id = wpotp_login($input);

    if(!$user_id){
        wp_send_json_error(['msg' => 'Login failed']);
    }

    wp_send_json_success([
        'msg' => 'Login successful'
    ]);
}


/**
 * LOGIN / REGISTER USER
 */
function wpotp_login($input){

    if(is_email($input)){

        $user = get_user_by('email', $input);

        if(!$user){
            $user_id = wp_create_user($input, wp_generate_password(), $input);

            if(is_wp_error($user_id)){
                return false;
            }
        } else {
            $user_id = $user->ID;
        }

    } else {

        // Phone login
        $users = get_users([
            'meta_key'   => 'phone',
            'meta_value' => $input,
            'number'     => 1
        ]);

        if(!empty($users)){
            $user_id = $users[0]->ID;
        } else {

            $username = 'user_' . wp_rand(1000,9999);
            $email    = $username . '@otp.local';

            $user_id = wp_create_user($username, wp_generate_password(), $email);

            if(is_wp_error($user_id)){
                return false;
            }

            update_user_meta($user_id, 'phone', $input);
        }
    }

    wp_set_auth_cookie($user_id, true);

    do_action('wpotp_after_login', $user_id);

    return $user_id;
}