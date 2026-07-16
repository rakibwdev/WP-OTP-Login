jQuery(document).ready(function($){

    let method = wpotp_data.method;

    // Apply button color
    $('.wpotp-btn').css('background', wpotp_data.btn_color);

    // UI CONTROL
    if(method === 'email'){
        $('#user_email').show();
        $('#user_phone').hide();
        $('#login_type').hide();
    }

    else if(method === 'phone'){
        $('#user_phone').show();
        $('#user_email').hide();
        $('#login_type').hide();
    }

    else if(method === 'both'){
        $('#login_type').show();

        $('#login_type').val('email').trigger('change');

        $('#login_type').on('change', function(){
            let type = $(this).val();

            if(type === 'email'){
                $('#user_email').show();
                $('#user_phone').hide();
            } else {
                $('#user_phone').show();
                $('#user_email').hide();
            }
        });
    }

    /**
     * SEND OTP
     */
    $('#send_otp').click(function(){

        let type = $('#login_type').val();
        let input = (type === 'phone') ? $('#user_phone').val() : $('#user_email').val();

        if(!input){
            alert('Please enter email or phone');
            return;
        }

        // Loader ON
        $('#wpotp-loader').show();
        $('#send_otp').prop('disabled', true);

        $.post(wpotp_data.ajax_url,{
            action:'wpotp_send',
            input:input,
            type:type,
            nonce:wpotp_data.nonce
        },function(res){

            $('#wpotp-loader').hide();

            if(res.success){

                $('#wpotp-msg').text(res.data.msg).css('color','green');
                $('#otp_area').show();

                // Cooldown timer (from backend)
                let time = res.data.cooldown || 60;

                let interval = setInterval(function(){
                    time--;
                    $('#send_otp').text('Wait ' + time + 's');

                    if(time <= 0){
                        clearInterval(interval);
                        $('#send_otp').prop('disabled', false).text('Send OTP');
                    }

                },1000);

            } else {
                $('#send_otp').prop('disabled', false);
                $('#wpotp-msg').text(res.data.msg).css('color','red');
            }

        });

    });


    /**
     * VERIFY OTP
     */
    $('#verify_otp').click(function(){

        let type = $('#login_type').val();
        let input = (type === 'phone') ? $('#user_phone').val() : $('#user_email').val();
        let otp   = $('#otp').val();

        if(!otp){
            alert('Enter OTP');
            return;
        }

        $('#wpotp-loader').show();
        $('#verify_otp').prop('disabled', true);

        $.post(wpotp_data.ajax_url,{
            action:'wpotp_verify',
            input: input,
            otp: otp,
            nonce: wpotp_data.nonce
        },function(res){

            $('#wpotp-loader').hide();
            $('#verify_otp').prop('disabled', false);

            if(res.success){

                $('#wpotp-msg').text(res.data.msg).css('color','green');

                // Redirect
                window.location.href = '/dashboard';

            } else {

                $('#wpotp-msg').text(res.data.msg).css('color','red');

            }
        });

    });

});