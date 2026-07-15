<?php
if (!defined('ABSPATH')) exit;

function wpotp_form() {
ob_start(); ?>

<div class="wpotp-box">

    <!-- Dropdown (only for BOTH) -->
    <select id="login_type" style="display:none;">
        <option value="email">Email</option>
        <option value="phone">Phone</option>
    </select>

    <!-- Email Field -->
    <input type="email" id="user_email" placeholder="Enter Email" style="display:none;">

    <!-- Phone Field -->
    <input type="text" id="user_phone" placeholder="Enter Phone Number" style="display:none;">

    <button class="wpotp-btn" id="send_otp">Send OTP</button>

    <input type="text" id="otp_code" placeholder="Enter OTP">

    <button class="wpotp-btn" id="verify_otp">Verify OTP</button>

    <div id="wpotp-msg"></div>

</div>

<?php
return ob_get_clean();
}
add_shortcode('wp_otp_login', 'wpotp_form');