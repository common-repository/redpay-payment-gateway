<?php 
header('Content-Type: application/json; charset=utf-8');
require_once('../../../wp-load.php');

//si recibimos el payload de transacción
if (
    isset($_GET['id']) && 
    isset($_POST['ReferenceNumber']) && 
    isset($_POST['ResponseCode']) && 
    isset($_POST['PaymentMethodId'])
) {
    $referenceNumber = $_POST['ReferenceNumber'];    
    $responseCode    = $_POST['ResponseCode'];    
    $paymentMethodId = $_POST['PaymentMethodId'];

    //Preparamos la respuesta 
    $response = array();

    //tomamos la orden a partir del número de referencia
    $order = wc_get_order((int) $referenceNumber);

    //Validamos que exista una orden con la referencia en estatus de pendiente
    if ($order && $order->status == "pending") {
        //Revisamos que los datos del payload coincidan 
        //con los datos de la orden
        if ((int)$order->id == (int)$referenceNumber) {
            //Si el método de pago fue efectivo (5 u 11)
            if ((int) $paymentMethodId == 5 || (int) $paymentMethodId == 11) {
                //Si el pago fue aceptado
                if ((int)$responseCode == 2) {
                    // Completar la orden
                    $order->payment_complete();
                    wc_reduce_stock_levels((int) $referenceNumber);

                    $response = array(
                        "response_code" => 200, 
                        "message" => "payment_complete"
                    );
                }

                //Si el pago no se aceptó
                else {
                    $response = array(
                        "response_code" => 404, 
                        "message" => "payment_error"
                    );
                }
            }

            //Si el método de pago no es efectivo
            else {
                $response = array(
                    "response_code" => 404, 
                    "message" => "payment_method_not_allowed"
                );
            }
        }

        //Si la referencia no coincide
        else {
            $response = array(
                "response_code" => 404, 
                "message" => "payload_not_found"
            );
        }
    }

    //Si no encontramos la orden
    else {
        $response = array(
            "response_code" => 404, 
            "message" => "order_gone"
        );
    }
}

//si no recibimos el payload
else {
    $reponse = array(
        "response_code" => 400, 
        "message" => "Bad Request"
    );
}

if (file_exists("log.txt"))  {
    $file = "log.txt";
    //$current = file_get_contents($file);
    $post_data = "POST:".json_encode($_POST)."\n";
    $get_data  = "GET:".json_encode($_GET)."\n";
    $json_data  = "RESPONSE:".json_encode($response)."\n";
    file_put_contents($file, $post_data."\n\n".$get_data."\n\n".$json_data);
}

echo json_encode($response);
