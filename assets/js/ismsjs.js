jQuery(document).ready(function($){
    if($('body').hasClass('toplevel_page_isms-setting')){

        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
            // allowDropdown: false,
            // autoHideDialCode: false,
            //autoPlaceholder: "off",
            // dropdownContainer: document.body,
            // excludeCountries: ["us"],
            // formatOnDisplay: false,
            // geoIpLookup: function(callback) {
            //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //     var countryCode = (resp && resp.country) ? resp.country : "";
            //     callback(countryCode);
            //   });
            // },
             hiddenInput: "phone",
            // initialCountry: "auto",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
             placeholderNumberType: "MOBILE",
             preferredCountries: ['my', 'jp'],
            separateDialCode: true,
            utilsScript: ismsScript.pluginsUrl+"../assets/prefix/js/utils.js",
        });



        $("#phone").keyup(function() {
            $(this).val($(this).val().replace(/^0+/, ''));
        });
    }

    if($('body').hasClass('user-edit-php') && $('billing_phone').val() != " "){

        var input = document.querySelector("#billing_phone");
        window.intlTelInput(input, {
            // allowDropdown: false,
            // autoHideDialCode: false,
            //autoPlaceholder: "off",
            // dropdownContainer: document.body,
            // excludeCountries: ["us"],
            // formatOnDisplay: false,
            // geoIpLookup: function(callback) {
            //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
            //     var countryCode = (resp && resp.country) ? resp.country : "";
            //     callback(countryCode);
            //   });
            // },
            hiddenInput: "billing_phone",
            // initialCountry: "auto",
            // localizedCountries: { 'de': 'Deutschland' },
            // nationalMode: false,
            // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
            placeholderNumberType: "MOBILE",
            preferredCountries: ['my', 'jp'],
            separateDialCode: true,
            utilsScript: ismsScript.pluginsUrl+"../assets/prefix/js/utils.js",
        });



        $("#billing_phone").keyup(function() {
            $(this).val($(this).val().replace(/^0+/, ''));
        });
    }


    $("#send-sms").click(function(e) {
        e.preventDefault();
        $(this).html("Sending SMS...");
        $.ajax({
            type : 'POST',
            url: ismsajaxurl.ismsscript,
            dataType: 'json',
            data : {
                action  	: 'send_manual_sms',
                dst  		: $('#dst').val(),
                msg  		: $('#msg').val(),
                order_id  	: $('#order_id').val()

            },
            success:function(data) {

                $('#send-sms').html("Send SMS");
                if(data) {
                    $('#msg').val("");
                    $('.isms-response-holder').addClass('isms-bg-success');
                    $('.isms-response-holder').html("Sent Successfully");
                    $('.isms-response-holder').fadeIn('slow');
                }else {
                    $('.isms-response-holder').removeClass('isms-bg-success');
                    $('.isms-response-holder').addClass('isms-bg-danger');
                    $('.isms-response-holder').html("Failed to send");
                    $('.isms-response-holder').fadeIn('slow');
                }
                setTimeout(function(){ $('.isms-response-holder').fadeOut('slow'); }, 5000);
            },

            error: function(errorThrown){
                console.log(errorThrown);

            }
        });

    });

    $('#dt-message-sent').DataTable( {

        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "sAjaxSource":ismsajaxurl.ismsscript+'?action=get_sms_sent',
        "order": [[ 4, "desc" ]],
        'destroy': true,

        "columns": [
            { "data": "order_id" },
            { "data": "msg_type" },
            { "data": "phone_no" },
            { "data": "message" },
            { "data": "date" }
        ]
    } );

    $('#woocommerce-order-actions .save_order').click( function (e) {
        e.preventDefault();
        var order_action = $('select[name="wc_order_action"]').val();
		
        $.ajax({
            type : 'POST',
            url: ismsajaxurl.ismsscript,
            dataType: 'json',
            data : {
                action  	        : 'resend_notification',
                order_action  		: order_action,
                order_id            : $('#post_ID').val(),
                original_process     : $('#original_post_status').val(),
                new_process         : $('#order_status').val()

            },
            success:function(data) {
                console.log(data);
                if(data) {
                 $('#post').submit();
                }else {

                }

            },

            error: function(errorThrown){
                console.log(errorThrown);

            }
        });


    });
});