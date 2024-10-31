var RedPay = RedPay || {
    apiKey: "",
    setApiKey: function (value) {
        this.apiKey = value;
    },
    sandBoxMode: false,
    setSandBoxMode: function (value) {
        this.sandBoxMode = value
    },
    reference: "",
    setReference: function (value) {
        this.reference = value;
    },
    loadScript: function (src) {
        return new Promise(function (resolve, reject) {
            var s;
            s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    },
    openpay: {
		success_callbak : function (response) {
			var token_id = response.data.id;
			jQuery("form[name='checkout']").append("<input type='hidden' name='token' id='token' value=" + token_id + "></input>");
			jQuery("form[name='checkout']").submit()
		},
		error_callbak : function (response) {
			var desc = response.data.description != undefined ?
				response.data.description : response.message;
			console.log(desc);
			jQuery("form[name='checkout']").submit()
		},
        onSubmitForm: function () {
			if(jQuery('#redpay_type').val().split("-")[1] == "9"){
				jQuery('#formOpenpay').append("<input type='hidden' value=" + `${jQuery('#billing_first_name').val()} ${jQuery('#billing_last_name').val()}` + " data-openpay-card='holder_name'></input>");
				jQuery('#formOpenpay').append("<input type='hidden' value=" + jQuery('#redpay_ccNo').val().replace(/ /g, '') + " data-openpay-card='card_number'></input>");
				jQuery('#formOpenpay').append("<input type='hidden' value=" + jQuery('#redpay_expdate').val().split("/")[0] + " data-openpay-card='expiration_month'></input>");
				jQuery('#formOpenpay').append("<input type='hidden' value=" + jQuery('#redpay_expdate').val().split("/")[1] + " data-openpay-card='expiration_year'></input>");
				jQuery('#formOpenpay').append("<input type='hidden' value=" + jQuery('#redpay_cvv').val() + " data-openpay-card='cvv2'></input>");
				window.OpenPay.token.extractFormAndCreate('formOpenpay', this.RedPay.openpay.success_callbak, this.RedPay.openpay.error_callbak);
			}else jQuery("form[name='checkout']").submit();
        }.bind(this),
        init: function () {
            jQuery('body').append("<form id='formOpenpay'></form>");
            this.RedPay.loadScript("https://js.openpay.mx/openpay.v1.min.js")
                .then(() => {
                    this.RedPay.loadScript("https://js.openpay.mx/openpay-data.v1.min.js")
                        .then(() => {
                            var apiKey = this.RedPay.sandBoxMode ? "pk_e0f4a2ab589a470a86cab0b52c718529" : "pk_ebc389e5a0134864a366f6529b5d76f1";
                            var merchantId = this.RedPay.sandBoxMode ? "m2jtyy9jkrh6q3fxtpwa" : "mxqq5eipmyswif2fgjdx";
                            window.OpenPay.setId(merchantId);
                            window.OpenPay.setApiKey(apiKey);
							window.OpenPay.setSandboxMode(this.RedPay.sandBoxMode);
                            var deviceSessionId = window.OpenPay.deviceData.setup("formOpenpay", "deviceSessionId");
							jQuery("form[name='checkout']").append("<input type='hidden' name='deviceId' id='deviceId' value=" + deviceSessionId + "></input>");
                        })
                });
			jQuery('#place_order').removeAttr("type").attr("type", "button");
			jQuery("#place_order").click(this.RedPay.openpay.onSubmitForm);
		}.bind(this)
    },
    cyberSource: {
        init: function () {
            var scriptUrl = this.RedPay.sandBoxMode ? "https://h.online-metrix.net/fp/tags.js?org_id=45ssiuz3&session_id=redcompaniesmx" : "https://h.online-metrix.net/fp/tags.js?org_id=9ozphlqx&session_id=redpaymx";
            this.RedPay.loadScript(url + reference)
                .then(() => {
                    var src = scriptUrl.replace(".js", "") + this.RedPay.reference
                    var iframe = "<noscript><iframe style=\"width:'100px',height:'100px',border:0,position:'absolute',top:'-5000px'\" src='" + src + "'></iframe></noscript>";
                    jQuery('body').append(iframe)
                })
        }.bind(this)
    },
    init: function () {
        var url = this.sandBoxMode ? "https://appredpayapiclientmxdev.azurewebsites.net/api/Pay/CardTypes/" + this.apiKey : "https://api.redpayonline.com/api/Pay/CardTypes/" + this.apiKey;
        jQuery.ajax({
            url: url,
            type: 'GET',
            success: function (json) {
                let found = json.find(p => p.text == "Visa");
                if (found != undefined) {
                    switch (found.value.split("-")[1]) {
                        case "9":
                            this.openpay.init();
                            break;
                        case "10":
                            this.cyberSource.init();
                            break;
                        default:
                            break;
                    }
                }
            }.bind(this)
        });
    }
};