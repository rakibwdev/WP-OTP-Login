<?php
if (!defined('ABSPATH')) exit;

function wpotp_form() {
ob_start(); ?>

<div class="wpotp-box">

<select id="login_type">
    <option value="email">Email</option>
    <option value="phone">Phone</option>
</select>

<input type="text" id="user_input" placeholder="Enter Email or Phone">
<button class="wpotp-btn" id="send_otp">Send OTP</button>

<div id="otp_area" style="display:none;">
    <input type="text" id="otp" placeholder="Enter OTP">
<button class="wpotp-btn" id="verify_otp">Verify OTP</button>
</div>

<p id="msg"></p>

</div>

<?php
return ob_get_clean();
}
add_shortcode('wp_otp_login', 'wpotp_form');