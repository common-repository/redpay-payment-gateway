<?php 
require_once('../../../wp-load.php');
wc_get_logger()->debug("ape_payment, _GET\n" . print_r($_GET,true), array('source' => 'udev'));
if (isset($_GET['data']) && isset($_GET['payment_method_id'])) {
    if ($_GET['payment_method_id'] != '5' && $_GET['payment_method_id'] != '11') {
        exit("<h1>PAYLOAD ERROR</h1>");
    }

    global $woocommerce;
    $data = $_GET['data'];
    $payment_method_id = $_GET['payment_method_id'];
    $payment_store = $payment_method_id == '5' ? 'OXXO' : 'Netpay';

    

    $img_url = 'redpay_logo_'.($payment_method_id == '5' ? 'oxxo' : 'netpay').'.png';

    $data_obj = json_decode(str_replace("\\", "", $_GET['data']));
    wc_get_logger()->debug("DATA\n" . print_r(json_decode(str_replace("\\", "", $_GET['data'])),true), array('source' => 'udev'));
    $order = wc_get_order((int) $data_obj->referenceNumber);
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">        
        <link rel="stylesheet" type="text/css" href="<?=get_template_directory_uri()?>/style.css">
    </head>
    <body>

        <div class="container" style="margin: auto; width: 100%; text-align: center;">
            <?=get_custom_logo()?>

            <h2 style="text-align: center;">
                Gracias por tu pedido
            </h2>

            <p style="font-size: 1.6em; padding: 0 2rem">Hemos enviado un correo electrónico con la información necesaria para que realices el pago en efectivo con la forma de pago que has seleccionado, a continuación, te mostramos un resumen de tu pedido:</p>

            <img src="./<?=$img_url?>" style="height:150px;max-width:auto; margin: 0 .25rem" />

            <br>
            <p style="font-size: 1.4em">
                <b>
                    Pago en <?=($payment_store == 'OXXO' ?  'tiendas '.$payment_store : ' comercios afiliados '.$payment_store)?>.
                </b>
            </p>

            <table style="width: auto; margin: auto; font-size: 1.2em">
                <tbody>
                    <tr>
                        <td style="text-align: right; padding-right: 10px;">
                            Monto:
                        </td>
                        <th>
                            $<?=number_format($data_obj->amount, 2, '.', ',')?> MXN
                        </th>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <em style="font-size: 1.1em">
                                <br>
                                Hemos enviado un correo electrónico a <b><a href="mailto:<?=$data_obj->email?>"><?=$data_obj->email?></a></b> con la línea de captura y las instrucciones 
                                de pago.
                            </em>       
                            <br>
                            <br>
                            <a href="<?=home_url()?>">
                                Regresar a la tienda
                            </a>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </dl>





    </div>

    <?=get_footer()?>

</body>
</html>

<?php
}
