jQuery(document).ready(function($){

let provider = wpotp_data.provider;

// FIREBASE INIT
if(provider === 'firebase'){

    firebase.initializeApp(wpotp_data.firebase);
    const auth = firebase.auth();

    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        size: 'normal'
    });

    $('#send_otp').click(function(){

        let phone = $('#phone').val();

        auth.signInWithPhoneNumber(phone, window.recaptchaVerifier)
        .then(function (confirmationResult) {
            window.confirmationResult = confirmationResult;
            $('#otp_area').show();
        });

    });

    $('#verify_otp').click(function(){

        let code = $('#otp').val();

        confirmationResult.confirm(code).then(function (result) {

            $.post(wpotp_data.ajax_url, {
                action: 'firebase_login',
                phone: result.user.phoneNumber
            }, function(){
                location.reload();
            });

        });

    });

}

// SMS FLOW
else {

    $('#send_otp').click(function(){

        $.post(wpotp_data.ajax_url, {
            // action: 'send_otp_sms',
            // phone: $('#phone').val()
            action: 'send_otp_sms',
            phone: phone,
            nonce: wpotp_data.nonce
        }, function(){
            $('#otp_area').show();
        });

    });

    $('#verify_otp').click(function(){

        $.post(wpotp_data.ajax_url, {
            action: 'verify_otp_sms',
            phone: $('#phone').val(),
            otp: $('#otp').val()
        }, function(res){
            if(res === 'success') location.reload();
            else alert('Invalid OTP');
        });

    });

}

});


