<?php

add_action('woocommerce_thankyou', 'digicalculator_send_order', 10, 1);
function digicalculator_send_order( $order_id ) {
    if ( ! $order_id )
        return;
    

    // Allow code execution only once 
    if( ! get_post_meta( $order_id, '_printcalc_mails_send', true ) ) {

        $order = wc_get_order( $order_id );
        $order_data = $order->get_data();
        foreach ( $order->get_items(['line_item']) as  $item_key => $item_values ) {
            // get data to check if it's a Digicalc product;
            $item_data = $item_values->get_data();
            error_log(json_encode($order->get_data()));
            error_log(json_encode($item_data));
            error_log(json_encode( json_decode($item_values->get_meta('dc_connect-product_keys'),true) ));
            if( $item_data['name'] == "Printcalc" ){
                //Printcalc product;
                //Build orderticket for mail
                $variables = [
                    "order_id" => $order_id,
                    "line_id" => $item_data['id'],
                    "date" => date("d-m-Y H:i:s"),
                    "quantity" => $item_data['quantity'],
                    "extref" =>  $item_data['order_id'],
                    
                    "option_rows" => "",
                    "file_links" => "",

                    "client" => $order_data['billing']['first_name']." ".$order_data['billing']['last_name'],
                    "client_company" => $order_data['billing']['company'],
                    "client_mail" => $order_data['billing']['email'],
                    "del_contact" => $order_data['shipping']['first_name']." ".$order_data['shipping']['last_name'],
                    "del_company" => $order_data['shipping']['company'],
                    "del_adress" => $order_data['shipping']['address_1'],
                    "del_city" => $order_data['shipping']['city'],
                    "del_postal" => $order_data['shipping']['postcode'],
                    "del_country" => $order_data['shipping']['country'],            
                ];
                foreach (json_decode($item_values->get_meta('dc_connect-product_keys'),false) as $value) {
                    $variables["option_rows"] .= "<tr style='width: 100%;'>
                    <td style='border-collapse: collapse;	border: 1px solid black;	padding: 4px 8px;	min-width: 150px;	font-family: Arial, Helvetica, sans-serif;'>
                        {$value->title}</td>
                    <td style='border-collapse: collapse;	border: 1px solid black;	padding: 4px 8px;	min-width: 150px;	font-family: Arial, Helvetica, sans-serif;'>
                        {$value->value}</td></tr>";
                }
                foreach (json_decode($item_values->get_meta('dc_connect-files'),false) as $value) {
                    $variables["file_links"] .= "<a href='{$value->url}'>{$value->name}</a>";
                }

                $ticket = file_get_contents( __DIR__."/../snippets/orderticket-main.html" );

                foreach ($variables as $key => $var) {
                    $ticket = str_replace("{{{$key}}}",$var,$ticket);
                }

                file_put_contents(__DIR__.'/../'.$order_id.'-'.$item_data['id'].'.html',$ticket);
                $mail = $ticket;

                $content_type = function() { return 'text/html'; };
                add_filter( 'wp_mail_content_type', $content_type );
                wp_mail( "orders@zogedrukt.nl", 
                'Digicalulator product in order' , 
                $mail,
                '',
                [__DIR__.'/../'.$order_id.'-'.$item_data['id'].'.html']);
                remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
                unlink(__DIR__.'/../'.$order_id.'-'.$item_data['id'].'.html');
            }
        }
        // return false;
        //Update meta to prevent a double mail on page refresh
        $order->update_meta_data( '_printcalc_mails_send', true );
        $order->save();
    }
}
