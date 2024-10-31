<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
/*
 * Plugin Name: RedPay Payment Gateway
 * Plugin URI: https://redpay.mx
 * Description: Take credit card payments on your store.
 * Author: RedPay
 * Author URI: https://somosredcompanies.com/
 * Developer: Urano Dev
 * Developer URI: https://urano.dev
 * Version: 1.2
 * Requires at least: 5.0
 * WC requires at least: 3.0
 * WC tested up to: 6.0.0
 *
 */
/*
 * Esta acción registra nuestro Plugin si Woocommerce está activo
 */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
    add_filter('woocommerce_payment_gateways', 'add_gateway_redpay');
    add_action('plugins_loaded', 'init_gateway_redpay');
}

function add_gateway_redpay($gateways)
{
    $gateways[] = 'WC_RedPay_Gateway'; // Nombre de la Clase
    return $gateways;
}


function init_gateway_redpay()
{
    class WC_RedPay_Gateway extends WC_Payment_Gateway
    {
        function redpay_scripts() {
            wp_enqueue_script( 'imask-js', plugins_url('imask.js', __FILE__), false );
            wp_enqueue_script( 'redpay-js', plugins_url('redpay.plugin.js', __FILE__), false );
        }

        function redpay_scripts_inline() {
            echo '<style>
            .blockUIblockOverlay {
                z-index: -1;
            }
            .blockOverlay {
                display:none !important;
            }
            </style>
            <script>
            window.RedPay.setApiKey("' . $this->apiKey . '");
            window.RedPay.setSandBoxMode(' . ('no' === $this->production ? 'true' : 'false') . ');
            window.RedPay.setReference("' . str_pad(!isset($order_id) ? 0 : $order_id, 10, '0', STR_PAD_LEFT) . '");
            window.RedPay.init();
            var element = document.getElementById("redpay_expdate");
            var mask = IMask(element, {
                mask: "MM\/YY",
                blocks: {
                    YY: {
                        mask: IMask.MaskedRange,
                        from: 21,
                        to: 35
                        },
                        MM: {
                            mask: IMask.MaskedRange,
                            from: 1,
                            to: 12
                        }
                    }
                }
                );

                var element = document.getElementById("redpay_ccNo");
                var dispatchMask = IMask(element, {
                    mask: [
                    {
                        mask: "0000\ 0000\ 0000\ 0000",
                        regex: /^(?:4[0-9]{12}(?:[0-9]{3})?)$/,
                        startsWith: "4",
                        cardType: "001"
                        },
                        {
                            mask: "0000\ 0000\ 0000\ 0000",
                            regex: /^(?:5[1-5][0-9]{14})$/,
                            startsWith: "5",
                            cardType: "002"
                            },
                            {
                                mask: "0000\ 000000\ 00000",
                                regex: /^(?:3[47][0-9]{13})$/,
                                startsWith: "3",
                                cardType: "003"
                            }
                            ],
                            dispatch: function (appended, dynamicMasked) {
                              var number = (dynamicMasked.value + appended).substring(0, 1);
                              var cardType = jQuery("#redpay_type").val()== null?"":jQuery("#redpay_type").val();
                              return dynamicMasked.compiledMasks.find(function (m) {
                                return number == m.startsWith && cardType.split("-")[0] == m.cardType;
                                });
                            }
                        }
                        )

                        var element = document.getElementById("redpay_cvv");
                        var dispatchMask = IMask(element, {
                            mask: [
                            {
                                mask: "000",
                                cardType: "001"
                                },
                                {
                                    mask: "000",
                                    cardType: "002"
                                    },
                                    {
                                        mask: "0000",
                                        cardType: "003"
                                    }
                                    ],
                                    dispatch: function (appended, dynamicMasked) {
                                      var cardType = jQuery("#redpay_type").val()== null?"":jQuery("#redpay_type").val();
                                      return dynamicMasked.compiledMasks.find(function (m) {
                                        return cardType.split("-")[0] == m.cardType;
                                        });
                                    }
                                }
                                )

                                try {
                                    jQuery("#redpay_type").selectWoo();
                                    } catch (ex) {
                                    }

                                    jQuery("#redpay_type").on("change", function () {
                                        var placeHolderCard = "";
                                        var placeHolderCvv = "";
                                        jQuery("#redpay_ccNo").val("");
                                        jQuery("#redpay_cvv").val("");
                                        if (this.value.split("-")[0] == "001" || this.value.split("-")[0] == "002") {

                                            jQuery("#redpay-paynet-payment-data-container").hide(0);
                                            jQuery("#redpay-oxxo-payment-data-container").hide(0);
                                            jQuery("#redpay-credit-data-container").show(0);

                                            placeHolderCard = "XXXX-XXXX-XXXX-XXXX";
                                            placeHolderCvv = "XXX";

                                        } 
                                        else if (this.value.split("-")[0] == "003") {

                                            jQuery("#redpay-paynet-payment-data-container").hide(0);
                                            jQuery("#redpay-oxxo-payment-data-container").hide(0);
                                            jQuery("#redpay-credit-data-container").show(0);

                                            placeHolderCard = "XXXX-XXXXXX-XXXXX";
                                            placeHolderCvv = "XXXX";
                                        }

                                        else if (this.value.split("-")[0] == "005") {
                                            jQuery("#redpay-credit-data-container").hide(0);
                                            jQuery("#redpay-paynet-payment-data-container").hide(0);
                                            jQuery("#redpay-oxxo-payment-data-container").show(0);
                                        }

                                        else if (this.value.split("-")[0] == "011") {
                                            jQuery("#redpay-credit-data-container").hide(0);
                                            jQuery("#redpay-oxxo-payment-data-container").hide(0);
                                            jQuery("#redpay-paynet-payment-data-container").show(0);
                                        }

                                        jQuery("#redpay_ccNo").attr("placeholder", placeHolderCard);
                                        jQuery("#redpay_cvv").attr("placeholder", placeHolderCvv);

                                    }
                                    );
                                    </script>';
                                }

        /**
         * Constructor de la clase
         */
        public function __construct()
        {

            $this->id                 = 'redpay'; // ID del Plugin
            $this->icon               = plugins_url('redpay_1.png', __FILE__); // URL del icono que se muestra cerca del PlugIn
            $this->has_fields         = true; // Indica que tiene un formulario este gateway
            $this->method_title       = 'RedPay Gateway';
            $this->method_description = 'Description of RedPay payment gateway'; // Texto que aparece en las opciones del administrador
            
            // Gateways puede soportar subscriptions, refunds, saved payment methods,
            $this->supports = array(
                'products',
                'refunds'
            );
            
            // M�todo con los campos del formulario
            $this->init_form_fields();
            
            // Carga los ajustes (Settings)
            $this->init_settings();
            $this->title       = $this->get_option('title');
            if ($this->get_option('title')){
            }else{
                $this->title = $this->form_fields['title']['default'];
            }
            $this->description = $this->get_option('description');
            $this->enabled     = $this->get_option('enabled');
            $this->production  = $this->get_option('production');
            $this->apiKey      = $this->get_option('ApiKey');
            $this->password    = $this->get_option('Password');
            $this->orderPrefix  = $this->get_option('orderPrefix');
            $this->pagooxxo    = $this->get_option('pagooxxo');
            $this->pagopaynet  = $this->get_option('pagopaynet');

            
            // Acciones soportadas por el PlugIn
            add_action( 'wp_enqueue_scripts', array( $this, 'redpay_scripts' ) );
            add_action( 'wp_enqueue_scripts_inline', array( $this, 'redpay_scripts_inline' ) );
            add_action('woocommerce_api_wc_gateway_' . $this->id, array(
                $this,
                'check_redpay_response'
            ));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'
            ));
            
            // Checa si tiene datos en el CallBack del sistema de pagos
            if ((isset($_POST["TransactionNumber"])) || (isset($_POST["transactionNumber"]))) {
                if (isset($_POST["TransactionNumber"])){
                    $transaction = $_POST["TransactionNumber"];
                }
                else if (isset($_POST["transactionNumber"])){
                    $transaction = $_POST["transactionNumber"];
                }

                $this->check_redpay_response($transaction);
                exit;
            }
            
        }
        //Web hook que es llamado
        function check_redpay_response($transaction)
        {
            $context = array('source' => 'udev');
            $transaction_detail = $this->get_response_code_transaction($transaction);
            if (($transaction_detail->responseCode == "002") || ($transaction_detail->responseCode == "006")){ //002 = Aceptado 006 Expirado
                $referenceNumber = empty($this->orderPrefix) ? $transaction_detail->referenceNumber : str_replace($this->orderPrefix . "_", "", $transaction_detail->referenceNumber);
                $order = wc_get_order($referenceNumber);
                if (false === $order) {
                    return false;
                }
                $note = "Notificación de cambio estatus recibido transactionCode = {$transaction_detail->transactionCode}, ResponseCode= {$transaction_detail->responseCode}";
                $order->add_order_note( $note );
                if ($transaction_detail->responseCode == "002"){
                    $order->payment_complete($transaction_detail->transactionCode);
                    wp_redirect($this->get_return_url( $order ));
                    wc_get_logger()->debug ('Order completed'. print_r($transaction_detail,true) . " redirect to " . $this->get_return_url( $order ), $context);
                    $this->get_return_url( $order );
                    exit();
                }
                if ($transaction_detail->responseCode == "006"){
                    $order->update_status('Cancelled');
                    $this->get_return_url( $order );
                     exit();
                }
            }
            wp_redirect(wc_get_checkout_url());
            exit();
        }
        
        
        /**
         * Método de configuración del plugin desde el administrador (WP Admin)
         * @return void
         */
        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Habilitar/Deshabilitar',
                    'label' => 'Habilitar RedPay Gateway',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'production' => array(
                    'title' => 'Producci&oacute;n',
                    'label' => 'Habilita el ambiente de producci&oacute;n de RedPay',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),     
                //Agregamos  la opción de pago en oxxo
                'pagooxxo' => array(
                    'title' => 'Habilitar/Deshabilitar',
                    'label' => 'Pago en efectivo en tiendas <b>OXXO</b>',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no',
                    'value' => '005'
                ),
                //Agregamod la opción de pago con NetPay
                'pagopaynet' => array(
                    'title' => 'Habilitar/Deshabilitar',
                    'label' => 'Pago en efectivo en comercios afiliados a <b>Paynet</b>',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no',
                    'value' => '011'
                ),
                'title' => array(
                    'title' => 'T&iacute;tulo',
                    'type' => 'text',
                    'description' => 'T&iacute;tulo que se presenta en la pasarela de pagos',
                    'default' => 'Red Pay',
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => 'Descripci&oacute;n',
                    'type' => 'textarea',
                    'description' => 'Descripci&oacute;n que se presenta en la pasarela de pagos',
                    'default' => 'Empleamos los m&aacute;s altos est&aacute;ndares de seguridad (SSL, 3D Secure) para proteger su informaci&oacute;n personal y la de tu tarjeta. Revise al pie de su p&aacute;gina el s&iacute;mbolo del condado (SSL) que garantiza la autenticidad de la p&aacute;gina.'
                ),
                'ApiKey' => array(
                    'title' => 'ApiKey:',
                    'type' => 'text'
                ),
                'Password' => array(
                    'title' => 'Password',
                    'type' => 'password'
                ),
                'orderPrefix' => array(
                    'title' => 'Prefijo de orden:',
                    'type' => 'text'
                )
            );
        }
        
        /**
         * Construye el formulario de pagos
         */
        public function payment_fields()
        {
            // API URL
            if ('no' === $this->production){
                $url = 'https://appredpayapiclientmxdev.azurewebsites.net/api/Pay/CardTypes/' . $this->apiKey;
            }else{
                $url = 'https://api.redpayonline.com/api/Pay/CardTypes/' . $this->apiKey;
            }

            $args = array(
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'sslverify' 	=> false,
                'headers'     => array(
                    "Authorization" => " Basic " . base64_encode($this->apiKey . ':' . $this->password)
                )
            );

            $response = wp_remote_get($url, $args);
            $body = (array)json_decode($response["body"]);

            if ($this->description) {
                // Descripci�n antes del formulario
                echo wpautop(wp_kses_post($this->description));
            }else{
                echo wpautop(wp_kses_post($this->form_fields['description']['default']));
            }

            echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="" style="background:transparent;">';

            // Acci�n que se ejecuta en el submit
            do_action('woocommerce_credit_card_form_start', $this->id);

            $isAmex = FALSE;

            echo '<p class=""><label for="redpay_type">M&#xE9;todo de pago<span class="required">*</span></label>
            <span class="woocommerce-input-wrapper">
            <select id="redpay_type" name="redpay_type" style="width:100%">
            <option value selected>Elige tu opci&#xF3;n</option>';
            foreach($body as $item)//warning generated here
            {
                if($item->text == 'American Express'){
                    $isAmex = true;
                }
                echo '<option value="' . $item->value . '">' . $item->text . '</option>';
            }

            //Si se habilita la opción de pàgo con oxxo se agrega a los métodos de pago
            if ($this->pagooxxo == 'yes') {
                echo '<option value="005">Pago en efectivo en tiendas OXXO</option>';
            }

            //Si se habilita la opción de pàgo con Paynet se agrega a los métodos de pago
            if ($this->pagopaynet == 'yes') {
                echo '<option value="011">Pago en efectivo en comercios Paynet</option>';
            }

            echo '</select>
            </span>
            </p>';

            /*
            Sección de pago con tarjeta
             */
            echo '<div id="redpay-credit-data-container">
            <p class=""><label for="redpay_ccNo">N&#xFA;mero de tarjeta<span class="required">*</span></label>
            <span class="woocommerce-input-wrapper">
            <input style="width:100%" id="redpay_ccNo" name="redpay_ccNo" type="text" autocomplete="off">
            </span>
            </p>

            <p class="">
            <label for="redpay_expdate">Fecha de Vencimiento <span class="required">*</span></label>
            <span class="woocommerce-input-wrapper">
            <input style="width:100%" id="redpay_expdate" name="redpay_expdate" type="text" placeholder="MM / YY">
            </span>
            </p>

            <p class="">
            <label for="redpay_cvv">C&#xF3;digo de Seguridad (CVC) <span class="required">*</span></label>
            <span class="woocommerce-input-wrapper">
            <input style="width:100%" id="redpay_cvv" name="redpay_cvv" type="password" autocomplete="off">
            </span>
            </p> 

            <p class="">
            <img src="'.plugins_url('visa-and-mastercard-logo.png', __FILE__).'" style="height:50px;max-width:auto; margin: 0 .25rem" />';
            if($isAmex == true){
                echo '<img src="'.plugins_url('amex.png', __FILE__).'" style="height:50px;max-width:auto; margin: 0 .25rem" />';
            }
            echo '<img src="'.plugins_url('red-pay-logo.png', __FILE__).'"  style="height:50px;max-width:auto; margin: 0 .25rem"  /></p>';

            echo "</div> <!-- //END redpay-credit-data-container -->";
            
            echo '<div class="clear"></div>';
            /* **********************************************
            Termina Sección de pago con tarjeta
             */
            

            /* 
            Sección de pago OXXO
             */
            echo '<div id="redpay-oxxo-payment-data-container" style="display: none">';

            echo '<img src="'.plugins_url('redpay_logo_oxxo.png', __FILE__).'" style="max-height:150px !important;max-width:auto !important; display: block; margin: auto; float: none !important; margin-top: 20px !important; margin-bottom: 20px !important;" />';

            echo "<h3>¡Estás a un paso de finalizar tu pago con efectivo!</h3>";
            echo "</div> <!-- //END redpay-oxxo-payment-data-container -->";

            echo '<div class="clear"></div>';
            /* **********************************************
            Termina Sección de pago OXXO
             */            
            
            /* 
            Sección de pago con Paynet
             */            
            echo '<div id="redpay-paynet-payment-data-container" style="display: none">';

            echo '<img src="'.plugins_url('redpay_logo_netpay.png', __FILE__).'" style="max-height:150px !important;max-width:auto !important; display: block; margin: auto; float: none !important; margin-top: 20px !important; margin-bottom: 20px !important;" />';

            echo "<h3>¡Estás a un paso de finalizar tu pago con efectivo!</h3>";
            echo "</div> <!-- //END redpay-paynet-payment-data-container -->";
            /* **********************************************
            Termina Sección de pago OXXO
             */            
            
            echo '<div class="clear"></div>';
            
            do_action('woocommerce_credit_card_form_end', $this->id);
            
            echo '<div class="clear"></div></fieldset>';
            
            do_action('wp_enqueue_scripts_inline');
            
        }
        
        /*
         * JS y CSS Personalizados
         */
        public function payment_scripts()
        {

            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                return;
            }
            
            // Checa si esta deshabilitado el PlugIn
            if ('no' === $this->enabled) {
                return;
            }
            
            // Si no esta el apiKey y el password no realiza nada
            if (empty($this->apiKey) || empty($this->password)) {
                return;
            }
            
        }
        
        /*
         * Mensaje de datos de tarjeta requeridos
         */
        public function validate_fields()
        {
            $redPayType    = explode("-",  sanitize_text_field($_POST['redpay_type']));
            
            //Si no se selecciona ningún métdo de pago
            if (empty($_POST['redpay_type'])) {
                wc_add_notice('&#xA1;M&#xE9;todo de pago es requerido!', 'error');
                return false;
            }

            //Si el método de pago es TC / TD / AMEX validamos los campos 
            //adicionales
            else if ($redPayType[0] == '001' || $redPayType[0] == '002' || $redPayType[0] == '003') {
                if (empty($_POST['redpay_ccNo'])) {
                    wc_add_notice('&#xA1;N&#xFA;mero de tarjeta es requerido!', 'error');
                    return false;
                }
                if (empty($_POST['redpay_expdate'])) {
                    wc_add_notice('&#xA1;Fecha de vencimiento es requerido!', 'error');
                    return false;
                }
                if (empty($_POST['redpay_cvv'])) {
                    wc_add_notice('&#xA1;C&#xF3;digo de Seguridad(CVC) es requerido!', 'error');
                    return false;
                }
            }

            //Si el pago es con referencia en efectivo no se valida nada mas 
            else if  ($redPayType[0] == '005' || $redPayType[0] == '011'){
                return true;
            }
        }

        function get_response_code_transaction ($transaction_id){
            if ('no' === $this->production){
                $url = "https://appredpayapiclientmxdev.azurewebsites.net/api/transaction/response/$transaction_id";
            }
            else{
                $url = "https://api.redpayonline.com/api/transaction/response/$transaction_id";
            }
            // Llamada HTTP Api
            $args = array(
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'sslverify' => false,
                'headers'   => array(
                    "Content-Type" => "application/json",
                    "Authorization" => " Basic " . base64_encode($this->apiKey . ':' . $this->password)
                ),
                'cookies' => array()
            );
            $response = wp_remote_get($url, $args );
            $http_response = $response['response']['code'];
            if ($http_response != '200') {
                return false;
            }
            $my_body = json_decode($response["body"]);
			return $my_body;
        }


        /*    
         * Procesa el pago
         */
        public function process_payment($order_id)
        {

            global $woocommerce;
            
            // Obtiene el detalle de la orden
            $order = wc_get_order($order_id);
            
            // API URL
            if ('no' === $this->production){
                $url = 'https://appredpayapiclientmxdev.azurewebsites.net/api/auth/Authenticate';
            }else{
                $url = 'https://api.redpayonline.com/api/auth/Authenticate';
            }
            
            $data = array(
                "UserId" => $this->apiKey,
                "Password" => $this->password
            );
            $payload = json_encode($data);

            // Llamada HTTP Api
            $args = array(
                'body' => $payload,
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'sslverify' 	=> false,
                'headers'     => array(
                    "Content-Type" => "application/json"
                ),
                'cookies' => array()
            );

            $response = wp_remote_post($url, $args );

            $body = (array)json_decode($response["body"]);
            
            if ($body["message"] == 'Success') {
                // API URL
                if ('no' === $this->production){
                    $url = 'https://appredpayapiclientmxdev.azurewebsites.net/api/Pay/Create';
                }
                else{
                    $url = 'https://api.redpayonline.com/api/Pay/Create';
                }

                // Llamada HTTP Api
                $referenceNumber = empty($this->orderPrefix) ? $order_id : $this->orderPrefix . '_' . $order_id;
                $pieces    = explode("/",  sanitize_text_field($_POST['redpay_expdate']));
                $expire_mm = $pieces[0];
                $expire_yy = '20' . $pieces[1];

                $redPayType    = explode("-",  sanitize_text_field($_POST['redpay_type']));

                //Si el método de pago es tarjeta
                if ($redPayType[0] == "001" || $redPayType[0] == "002" || $redPayType[0] == "003") {

                    $data    = array(
                       // "IdPaymentMethod" => (int)$redPayType[1], cuando es tarjeta no va
                        "ReferenceNumber" => $referenceNumber,
                        "CardType" =>  $redPayType[0],
                        "cardNumber" => sanitize_text_field($_POST['redpay_ccNo']),
                        "cardExpirationMonth" => $expire_mm,
                        "cardExpirationYear" => $expire_yy,
                        "cvv" =>sanitize_text_field( $_POST['redpay_cvv']),
                        "Amount" => round($order->total, 2),
                        "Currency" => $order->currency,
                        "FirstName" => $order->billing_first_name,
                        "LastName" => $order->billing_last_name,
                        "Email" => $order->billing_email,
                        "PhoneNumber" => $order->billing_phone,
                        "Street" => $order->billing_address_1,
                        "Street2Col"=> $order->billing_address_2,
                        "Street2Del"=> $order->billing_city,
                        "City"=> $order->billing_city,
                        "State"=> $order->billing_state,
                        "Country"=> $order->billing_country,
                        "PostalCode"=> $order->billing_postcode,
                        "Device" => array(
                            "Id" => $_POST['deviceId'],
                            "Token" => $_POST['token']
                        )
                    );
                }

                else if ($redPayType[0] == "005" || $redPayType[0] == "011") {
                    $data   = array(
                        "IdPaymentMethod" => (int)$redPayType[0],
                        "ReferenceNumber" => $referenceNumber,                        
                        "Amount" => round($order->total, 2),
                        "Currency" => $order->currency,
                        "FirstName" => $order->billing_first_name,
                        "LastName" => $order->billing_last_name,
                        "Email" => $order->billing_email,                                                
                    );
                }

                $payload = json_encode($data);

                $args = array(
                    'body'        => $payload,
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'sslverify' 	=> false,
                    'headers'     => array(
                        "Content-Type" => "application/json",
                        "Authorization" => " Bearer " . $body["token"]
                    )
                );

                $response = wp_remote_post($url, $args );
                $body = (array)json_decode($response["body"]);
                
                wc_get_logger()->debug("DATAW TRANSFER antes: " . print_r($body,true) . " después: " . print_r(json_encode($body),true), array('source' => 'udev'));
                wc_get_logger()->debug("PATH : {$body["responseCode"]} método {$body["paymentMethodId"]}", array('source' => 'udev'));

                if($body["responseCode"] == '002'){
                    // Redirige al hook
                    return array(
                        'result' => 'success',
                        'redirect' => plugins_url('3dsecure.html', __FILE__) . '?redir=' . $body["urlRedirect"] . "&data=" . $response["body"]
                        //$this->get_return_url( $order )
                    );
                }
                else if($body["responseCode"] == '005' && ($body["paymentMethodId"] == "005" || $body["paymentMethodId"] == "011")){                    
                    //Si el pago es con efectivo aunque queda pendiente 
                    //eliminamos el carrito
                    $woocommerce->cart->empty_cart();
                    return array(
                        'result' => 'success', 
                        'redirect' => plugins_url('ape_payment.php', __FILE__). "?data=" . $response["body"]."&payment_method_id=".$body["paymentMethodId"]
                    );
                }
                else if ($body["responseCode"] == '007') {  // Rediret a 3DSecure
                    return array(
                        'result' => 'success',
                        'redirect' => $body["urlRedirect"]
                    );
                } else {
                    wc_add_notice($body["message"], 'error');
                    return;
                }
            } else {
                wc_add_notice($body["message"], 'error');
                return;
            }
        }

        public function process_refund( $order_id, $amount = null, $reason = '' ) {
            // Do your refund here. Refund $amount for the order with ID $order_id
            // API URL
            if ('no' === $this->production) {
                $url = "https://appredpayapiclientmxdev.azurewebsites.net/api/pay/refund";
            } else {
                $url = "https://api.redpayonline.com/api/pay/refund";
            }

            $order = wc_get_order($order_id);
            $transaction_code = $order->get_meta('_transaction_id');

            $data   = array(
                "Amount" => $amount,
                'TransactionCode' => $transaction_code,                
            );
        
            $payload = json_encode($data);


            $args = array(
                'body'          => $payload,
                'timeout'       => 45,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,
                'sslverify'     => false,
                'headers'       => array(
                    "Content-type" => "application/json",
                    "Authorization" => "Basic " . base64_encode($this->apiKey . ':' . $this->password)
                ),
            );

            $response = wp_remote_post($url, $args);
            $body = (array)json_decode($response["body"]);
            
            wc_get_logger()->debug("REFUND DATA SEND: " . print_r($payload,true) , array('source' => 'udev'));
            wc_get_logger()->debug("REFUND DATA ANSWER: " . print_r($response,true) . " order_id=: $order_id, \$amount= $amount", array('source' => 'udev'));
            if ("12" == $body['responseCode'] || "16" == $body['responseCode']){
                $order->add_order_note('Refund request sent to RedPay. ' . $reason);
                wc_add_notice($body["message"], 'error');
                return true;
            }
            wc_add_notice($body["message"], 'error');
            return false;
        }
    }
} 
