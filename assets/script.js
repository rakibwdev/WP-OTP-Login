jQuery(document).ready(function($){

$('#send_otp').click(function(){

    let input = $('#user_input').val();
    let type  = $('#login_type').val();

    $.post(wpotp_data.ajax_url,{
        action:'wpotp_send',
        input:input,
        type:type,
        nonce:wpotp_data.nonce
    },function(){
        $('#otp_area').show();
    });

});

$('#verify_otp').click(function(){

    $.post(wpotp_data.ajax_url,{
        action:'wpotp_verify',
        input:$('#user_input').val(),
        otp:$('#otp').val(),
        nonce:wpotp_data.nonce
    },function(res){
        if(res.success){
            location.reload();
        } else {
            alert('Invalid OTP');
        }
    });

});

 $('.wpotp-btn').css('background', wpotp_data.btn_color);

});