<?php

add_action('woocommerce_thankyou', 'digicalculator_send_order', 10, 1);
function digicalculator_send_order($order_id) {
    if (!$order_id)
        return;


    // Post to Switch webhook for optimalisation
    
    // Allow code execution only once 
    if (!get_post_meta($order_id, '_printcalc_mails_send', true)) {
        digicalculator_send_to_webhook($order_id);

        $order = wc_get_order($order_id);
        $order_data = $order->get_data();
        foreach ($order->get_items(['line_item']) as  $item_key => $item_values) {
            // get data to check if it's a Digicalc product;
            $item_data = $item_values->get_data();
            error_log(json_encode($order->get_data()));
            error_log(json_encode($item_data));
            error_log(json_encode(json_decode($item_values->get_meta('dc_connect-product_keys'), true)));
            if ($item_values->get_meta('dc_connect-product_keys') != null) {
                //Printcalc product;
                //Build orderticket for mail
                $variables = [
                    "order_id" => $order_id,
                    "line_id" => $item_data['id'],
                    "date" => date("d-m-Y H:i:s"),
                    "quantity" => $item_data['quantity'],
                    "extref" =>  $item_data['order_id'],
                    "deliverydate" => calculateDate(4),
                    "option_rows" => "",
                    "file_links" => "",

                    "client" => $order_data['billing']['first_name'] . " " . $order_data['billing']['last_name'],
                    "client_company" => $order_data['billing']['company'],
                    "client_mail" => $order_data['billing']['email'],
                    "del_contact" => $order_data['shipping']['first_name'] . " " . $order_data['shipping']['last_name'],
                    "del_company" => $order_data['shipping']['company'],
                    "del_adress" => $order_data['shipping']['address_1'],
                    "del_city" => $order_data['shipping']['city'],
                    "del_postal" => $order_data['shipping']['postcode'],
                    "del_country" => $order_data['shipping']['country'],
                ];
                foreach (json_decode($item_values->get_meta('dc_connect-product_keys'), false) as $value) {
                    $variables["option_rows"] .= "<tr style='width: 100%;'>
                    <td style='border-collapse: collapse;	border: 1px solid black;	padding: 4px 8px;	min-width: 150px;	font-family: Arial, Helvetica, sans-serif;'>
                        {$value->title}</td>
                    <td style='border-collapse: collapse;	border: 1px solid black;	padding: 4px 8px;	min-width: 150px;	font-family: Arial, Helvetica, sans-serif;'>
                        {$value->value}</td></tr>";
                }
                foreach (json_decode($item_values->get_meta('dc_connect-files'), false) as $value) {
                    $variables["file_links"] .= "<a href='{$value->url}'>{$value->name}</a>";
                }

                $ticket = file_get_contents(__DIR__ . "/../snippets/orderticket-main.html");

                foreach ($variables as $key => $var) {
                    $ticket = str_replace("{{{$key}}}", $var, $ticket);
                }

                file_put_contents(__DIR__ . '/../' . $order_id . '-' . $item_data['id'] . '.html', $ticket);
                $mail = $ticket;

                $content_type = function () {
                    return 'text/html';
                };
                add_filter('wp_mail_content_type', $content_type);
                wp_mail(
                    "orders@zogedrukt.nl",
                    'Digicalulator product in order ' . $order_id . '-' . $item_data['id'],
                    $mail,
                    '',
                    [__DIR__ . '/../' . $order_id . '-' . $item_data['id'] . '.html']
                );
                $order->add_order_note("Mail naar klantenservice gestuurd.");
                remove_filter('wp_mail_content_type', 'wpdocs_set_html_mail_content_type');
                // unlink(__DIR__.'/../'.$order_id.'-'.$item_data['id'].'.html');
            }
        }
        // return false;
        //Update meta to prevent a double mail on page refresh
        $order->update_meta_data('_printcalc_mails_send', true);
        $order->save();
    }
}

function digicalculator_send_to_webhook($order_id){
    $order = wc_get_order($order_id);
    foreach ($order->get_items(['line_item']) as  $item_values) {
        $line_data = $item_values->get_data();
        //Get size;
        $order_variables = ["back_print"=>"FD"]; $files = []; $desc = ["Digicalculator product"];
        foreach($line_data['meta_data'] as $meta_data){
            if( $meta_data->key == 'dc_connect-product_keys' ){
                //Variables 
                $data = json_decode($meta_data->value,false);
                foreach ($data as $value) {
                    $desc[] = $value->value;
                    if( $value->title == "Formaat" ){
                        $order_variables['width'] = explode('x',$value->value)[0];
                        $order_variables['height'] = explode('x',$value->value)[1];
                    } else if( $value->title == "Aantal pagina's" ){
                        $order_variables['pages'] = explode(' ',$value->value)[0];
                        if( $order_variables['pages'] == "1" ){
                            $order_variables['back_print'] = "";
                        }
                    } else if( $value->title == "Materiaal" ){
                        if( strpos($value->value,' mc ') ){
                            $order_variables['mat_type'] = "PS1";
                        } else {
                            $order_variables['mat_type'] = "PS5";
                        }
                    }
                }
            } else if( $meta_data->key == 'dc_connect-files' ){
                $files = json_decode($meta_data->value,true);
            }
        }
        foreach ($files as $file) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://swh.grootsgedrukt.nl:51080/digicalculator',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => "<Xgram><WEBBEST>
                    <LR_LINK Value=\"{$file['url']}\" /> 
                    <VELD_13_VAL Value=\"3\" /><VELD_15_VAL Value=\"3\" /><VELD_17_VAL Value=\"3\" /><VELD_19_VAL Value=\"3\" />
                    <VELD_23_VAL Value=\"".implode(', ',$desc)."\" />
                    <VELD_35_VAL Value=\"FD\" />
                    <VELD_37_VAL Value=\"{$order_variables['back_print']}\" />
                    <VELD_27_VAL Value=\"{$order_variables['mat_type']}\" />
                    <VELD_31_VAL Value=\"{$order_variables['width']}\" />
                    <VELD_33_VAL Value=\"{$order_variables['height']}\" />
                    <ORD_LINK Value=\"ZGDRKT-{$order_id}-{$line_data['id']}-{$file['nth']}\" />
                    <SWITCH_VAL Value=\"digicalc\" />
                </WEBBEST></Xgram>",
                CURLOPT_HTTPHEADER => array( 
                    'Content-Type: application/xml'
                ),
            ));
    
            $response = curl_exec($curl);
            curl_close($curl);
            // echo $response;
        }

    }
}

function calculateDate($productiondays = 4){
    if (date("H") >= 12 && date('D') != 'Sat' && date('D') != 'Sun') {
        $productiondays += 1;
    }
    $date = date('Y-m-d');
    while ($productiondays >= 0) {
        //Add a day
        $date = date('Y-m-d', strtotime($date . ' + 1 days'));
        $day = date('D', strtotime($date));
        // echo "$date > $day </br>";
        if ($day != 'Sat' || $day != 'Sun') {
            $productiondays -= 1;
        }
    }
    return $date;
}
