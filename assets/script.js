jQuery(document).ready(function($){


    let method = wpotp_data.method;

    // Apply button color
    $('.wpotp-btn').css('background', wpotp_data.btn_color);

    // Logic control
    if(method === 'email'){
        $('#user_email').show();
    }

    else if(method === 'phone'){
        $('#user_phone').show();
    }

    else if(method === 'both'){
        $('#login_type').show();
        $('#user_email').show(); // default

        $('#login_type').change(function(){
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


// jQuery(document).ready(function($){

//     let method = wpotp_data.method;

//     $('.wpotp-btn').css('background', wpotp_data.btn_color);

//     if(method === 'email'){
//         $('#user_email').show();
//         $('#user_phone').hide();
//         $('#login_type').hide();
//     }

//     else if(method === 'phone'){
//         $('#user_phone').show();
//         $('#user_email').hide();
//         $('#login_type').hide();
//     }

//     else if(method === 'both'){
//         $('#login_type').show();

//         $('#login_type').val('email').trigger('change');

//         $('#login_type').on('change', function(){
//             let type = $(this).val();

//             if(type === 'email'){
//                 $('#user_email').show();
//                 $('#user_phone').hide();
//             } else {
//                 $('#user_phone').show();
//                 $('#user_email').hide();
//             }
//         });
//     }

// });