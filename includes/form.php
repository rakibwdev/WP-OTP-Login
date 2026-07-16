<?php
if (!defined('ABSPATH')) exit;

function wpotp_form() {
ob_start(); ?>

<div class="wpotp-box">

    <!-- Login Type -->
    <select id="login_type" style="display:none;">
        <option value="email">Email</option>
        <option value="phone">Phone</option>
    </select>

    <!-- Email -->
    <input type="email" id="user_email" placeholder="Enter Email" style="display:none;">

    <!-- Phone -->
    <input type="text" id="user_phone" placeholder="Enter Phone Number" style="display:none;">

    <!-- Loader -->
    <div id="wpotp-loader" style="display:none;"></div>

    <!-- Send OTP -->
    <button class="wpotp-btn" id="send_otp">Send OTP</button>

    <!-- OTP AREA (hidden initially) -->
    <div id="otp_area" style="display:none;">

        <input type="text" id="otp" placeholder="Enter OTP">

        <button class="wpotp-btn" id="verify_otp">Verify OTP</button>

    </div>

    <!-- Message -->
    <div id="wpotp-msg"></div>

</div>

<?php
return ob_get_clean();
}
add_shortcode('wp_otp_login', 'wpotp_form');