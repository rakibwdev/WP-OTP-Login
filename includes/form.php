<?php

function wpotp_form() {
ob_start(); ?>

<div class="wpotp-box">

<input type="text" id="phone" placeholder="+8801XXXXXXXXX">
<button id="send_otp">Send OTP</button>

<div id="otp_area" style="display:none;">
    <input type="text" id="otp" placeholder="Enter OTP">
    <button id="verify_otp">Verify OTP</button>
</div>

<div id="recaptcha-container"></div>

<p id="msg"></p>

</div>

<?php
return ob_get_clean();
}

add_shortcode('wp_otp_login', 'wpotp_form');