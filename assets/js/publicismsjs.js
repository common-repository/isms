jQuery(document).ready(function($){
    if($('body').hasClass('woocommerce-edit-address') || $('body').hasClass('woocommerce-checkout')) {
		if(!$('body').hasClass('woocommerce-order-received')){
        var input = document.querySelector("#billing_phone");
        window.intlTelInput(input, {
            //allowDropdown: false,
            // autoHideDialCode: false,
            //autoPlaceholder: "off",
            // dropdownContainer: document.body,
            // excludeCountries: ["us"],
            // formatOnDisplay: false,
            //geoIpLookup: function (callback) {
            //   $.get("http://ipinfo.io", function () {
            //   }, "jsonp").always(function (resp) {
            //       var countryCode = (resp && resp.country) ? resp.country : "";
            //      callback(countryCode);
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
            utilsScript: ismsScript.pluginsUrl + "../assets/prefix/js/utils.js?1581331045115",
        });


       

        $("#billing_phone").keyup(function () {
            $(this).val($(this).val().replace(/^0+/, ''));
        });
		}
    }

});