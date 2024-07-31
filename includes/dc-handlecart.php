<?php

function digicalculator_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = null){
    if (1 == 2) {
        $passed = false;
        wc_add_notice(__('Your name is a required field.', 'digicalculator'), 'error');
    }
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'digicalculator_add_to_cart_validation', 10, 4);

//This sets the digicalculator variables to the product and adds some defaults;
function digicalculator_add_cart_item_data($cart_item_data, $product_id, $variation_id){
    $product = wc_get_product( $product_id );
    if( $product->get_type() == 'digicalculator' ){
        // error_log(json_encode($_POST));
        foreach ($_POST as $key => $value) {
            if( $key == 'option' ){
                foreach ($value as $i => $option) {
                    $cart_item_data["{$key}[$i]"] = $option;
                }
            } else {
                $cart_item_data[$key] = $value;
            }
        }
        $cart_item_data['addresses'] = 1;
        $cart_item_data['width'] = 1;
        $cart_item_data['height'] = 1;
        $cart_item_data['digicalculator_product'] = true;
        $cart_item_data['make_unique'] = date("YmdHis");
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'digicalculator_add_cart_item_data', 10, 3);

add_filter('woocommerce_cart_item_price', 'display_cart_items_custom_price_details', 20, 3 );
function display_cart_items_custom_price_details( $product_price, $cart_item, $cart_item_key ){
    if( isset($cart_item['digicalculator_product']) ){
        if( $cart_item['digicalculator_product'] ){
            $price = calculatePrice($cart_item, true);
            return wc_price(round($price, 2));
        }
    }
}

function digicalculator_woocommerce_cart_item_name($item_name, $cart_item, $cart_item_key) {
    if( !is_cart() || (!array_key_exists( 'digicalculator_product', $cart_item )))
        return $item_name;

    if (isset($cart_item['digicalculator_product'])) {
        $cart_item_set = cart_item_data_to_obj($cart_item);
        $count_keys = count($cart_item_set);

        $item_name .= '<span class="custom-field-short"><br>';
        foreach (array_slice($cart_item_set, 0, 2) as $product_key) {
            $item_name .= $product_key['key'] . ' - ' . $product_key['value'] . '<br/>';
        }
        if ($count_keys > 2) {
            $item_name .= '<a href="#" class="show-or-hide">Alle opties weergeven</a>';
        }
        $item_name .= '</span>';

        if ($count_keys > 2) {
            $item_name .= '<br><span class="custom-fields hide">';
            foreach ($cart_item_set as $product_key) {
                $item_name .= $product_key['key'] . ' - ' . $product_key['value'] . '<br/>';
            }
            $item_name .= '<a href="#" class="show-or-hide">Opties verbergen</a><br>';
            $item_name .= '</span>';
        }

        if (isset($cart_item['order_files']) && count($cart_item['order_files']) > 0) {
            $root = get_site_url();
            $item_name .= '<div class="pww-product-files-wrap"><br><span>Drukbestanden:<span><br>';
            $item_name .= '<div class="pww-product-files-list">';
            foreach ($cart_item['order_files'] as $order_file) {
                $item_name .= "<a
                    href=\"{$root}/wp-content/uploads/dc-uploads/{$order_file['file']}\"
                    target=\"_blank\">{$order_file['name']}
                </a> - 
                <a href=\"#\"
                    class=\"pww-file-delete-link dc-delete_upload\" 
                    data-key='{$cart_item['key']}' data-file='{$order_file['file']}'>verwijderen
                </a>
                <br>";
            }
            $item_name .= '</div></span></span></div>';
            $item_name .= "<a href='#' class='upload-new-file' data-id='$cart_item_key'>Nog een bestand uploaden</a>"; 
        } else {
            $item_name .= "<a href='#' class='upload-new-file' data-id='$cart_item_key'>Bestand uploaden</a>"; 
        }

        if( 1==0 ){
            if (isset($cart_item['order_files']) && count($cart_item['order_files']) > 0) {
                $root = get_site_url();
                $table = "";
                foreach ($cart_item['order_files'] as $order_file) {
                    $table .= "<tr>
                        <td> {$order_file['name']}</td>
                        <td><a class='fas fa-eye dc-view_upload' href='{$root}/wp-content/uploads/dc-uploads/{$order_file['file']}' target='_blank'></i></a></td>
                        <td><i class='fas fa-trash-alt dc-delete_upload' data-key='{$cart_item['key']}' data-file='{$order_file['file']}' ></i></td>                    
                        </tr>";
                }
                $item_name .= "<table class='dc-shoppingcart_table'>$table</table>";
                $item_name .= "<a href='#' class='upload-new-file' data-id='$cart_item_key'>Nog een bestand uploaden</a>"; 
            } else {
                $item_name .= "<table class='dc-shoppingcart_table'></table><a href='#' class='upload-new-file' data-id='$cart_item_key'>Bestand uploaden</a>"; 
            }
        }
    }
    //Add an upload field

    return $item_name;
}
add_filter( 'woocommerce_cart_item_name', 'digicalculator_woocommerce_cart_item_name', 10, 3 );

$dc_gl_options = getOptions();

//This sets the price in the shoppingcart
function digicalculator_set_price($cart_object){
    $cart_items = $cart_object->cart_contents;
    if (!empty($cart_items)) {
        foreach ($cart_items as $key => &$cart_item) {
            if( isset( $cart_item['digicalculator_product'] ) ){ //digicalculator product;
                $price = calculatePrice($cart_item, true);
                $price = $price / intval($cart_item["quantity"]);
                // ci_log(json_encode($price));
                $cart_item['data']->set_price( $price );
            }
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'digicalculator_set_price');

//This adds the variables to the metadata to be used bij \digicalculator_connect_wc_order_item_get_formatted_meta_data\
function digicalculator_add_variable_to_order_items( $item, $cart_item_key, $values, $order ) {
	if( isset($values['digicalculator_product']) ){
        if( ($values['digicalculator_product']) ){
            $item_data = cart_item_data_to_obj($values);
        
            $obj = [];
            foreach ($item_data as $it_da) {
                $obj[] = ['title'=>$it_da["key"],'value'=>$it_da['value']]; 
                // $item->add_meta_data( __( $it_da["key"], 'digicalculator' ), $it_da['value']);
            }
            $item->add_meta_data( 'dc_connect-product_keys', json_encode($obj));
            
            //Add the upload files to the digicalc prodcuts
            if( isset( $values['order_files'] ) ){
                $item->add_meta_data( 'dc_connect-files', json_encode($values['order_files']));
            }
        }
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'digicalculator_add_variable_to_order_items', 10, 4 );

//This adds the variables to the backend in a styled manner
function digicalculator_connect_wc_order_item_get_formatted_meta_data( $formatted_meta, $item ) {
    foreach($formatted_meta as $key => $meta) {
        if($meta->key == 'dc_connect-product_keys') {
            $meta->display_key = 'Samenstelling';
            
            $values = json_decode($meta->value, true);

            $html = '<br>';
            foreach($values as $value) {
                $html .= '<b>' . $value['title'] . '</b>' . ' - ' . $value['value'] . '<br/>';
            }
            $meta->display_value = $html;
        } else if($meta->key == 'dc_connect-files') {
            $meta->display_key = 'Bestanden';
            $html = "<br/>";
            $values = json_decode($meta->value, true);
            $root = get_site_url();
            foreach($values as $value) {
                $html .= "<a href='{$root}/wp-content/uploads/dc-uploads/{$value['file']}' target='_blank'>{$value['name']}</a><br/>";
            }
            $meta->display_value = $html;
        }
    }

    //When the order is added to the shoppingcart, mail the customersupport 
    

    // $formatted_meta['dc_connect-files'] = (object)[
    //     "key" => "dc_connect-files",
    //     "display_key" => "test",
    //     "display_value"=> "<pre>".json_encode($item->get_data(),JSON_PRETTY_PRINT)."</pre>"
    // ];
    return $formatted_meta;
}
add_filter( 'woocommerce_order_item_get_formatted_meta_data','digicalculator_connect_wc_order_item_get_formatted_meta_data', 10, 2);

$alerts = [];
function check_and_limit_cart_items (  ){
    global $alerts;
    // HERE set your product category (can be term IDs, slugs or names)
    $notice = false;

    // We exit if the cart is empty
    if( WC()->cart->is_empty() ){
        return false;
    }

    // CHECK CART ITEMS: search for items from product category
    foreach ( WC()->cart->get_cart() as $cart_item ){
        // echo '<pre>'; print_r($cart_item); echo '</pre>';
        if( $cart_item['digicalculator_product'] == true ){
            if( !isset($cart_item['order_files']) || count( $cart_item['order_files'] ) == 0 ){
                if( !$notice ){
                    wc_add_notice( sprintf( '<strong>Voor Printcalc producten moeten alle bestanden vooraf geupload worden.</strong>' ), 'error' );
                    $notice = true;
                }
                $alerts[] = $cart_item['key'];
            }
        }
        // echo "<pre>".json_encode($cart_item, JSON_PRETTY_PRINT)."</pre>";
    }
    return !$notice;
}
add_filter( 'woocommerce_check_cart_items', 'check_and_limit_cart_items');
function digicalculator_cart_item_class($class, $cart_item, $cart_item_key ){
    global $alerts;
    if( in_array($cart_item_key,$alerts) == true ){ 
        $class.= " dc-shoppingcart_alert";
    }
    return $class;
}
add_filter( 'woocommerce_cart_item_class','digicalculator_cart_item_class', 10, 3 );


function cart_item_data_to_obj($cart_item_data){
    global $dc_gl_options;
    $item_data = [];
    
    foreach ($dc_gl_options as $option) {
        if( $option['id'] == $cart_item_data['product_type'] ){
            $options = $option['options'];
        }
    }
    // ci_log(json_encode($cart_item_data));

    if( isset( $cart_item_data["product_size"] ) ){
        str_replace('x',' x ',$cart_item_data["product_size"]);
        if($cart_item_data["product_size"] == "unique"){
            $cart_item_data["product_size"] =  $cart_item_data["custom_width"].' x '. $cart_item_data["custom_height"];
        }
        $item_data[] = [
            'key' => __( "Formaat", 'digicalculator' ),
            'value'=> ($cart_item_data['product_size'])
        ];
    }


    if( isset( $cart_item_data["pages"] ) ){ // Look up in options
        if( $cart_item_data["product_type"] == 'brochures' ){
            $item_data[] = [
                'key' => __( "Aantal pagina's incl. omslag", 'digicalculator' ),
                'value'=> $cart_item_data['pages']." pagina's"
            ];
        } else {
            $item_data[] = [
                'key' => __( "Aantal pagina's", 'digicalculator' ),
                'value'=> $cart_item_data['pages']." pagina's"
            ];
        }
    }

    if( isset( $cart_item_data["papertype_cover"] ) ){ // Look up in options
        $value = getOptionsFromId($cart_item_data['papertype_cover'],$options,'materials_cover');
        $item_data[] = [
            'key' => __( "Materiaal omslag", 'digicalculator' ),
            'value'=> $cart_item_data["weight_cover"].' grams '.$value
        ]; unset($value);
    }
    
    if( isset( $cart_item_data["printtype_cover"] ) ){ // Look up in options
        $value = getOptionsFromId($cart_item_data['printtype_cover'],$options,'printtypes_cover');
        $item_data[] = [
            'key' => __( "Bedrukking omslag", 'digicalculator' ),
            'value'=> $value
        ]; unset($value);
    }

    if( isset( $cart_item_data["papertype"] ) ){ // Look up in options
        $keys = ['materials','Materiaal','weight']; 
        if( $cart_item_data['product_type'] == 'brochures' ){$keys=['materials_center','Materiaal binnenwerk','weight'];}
        $value = getOptionsFromId($cart_item_data['papertype'],$options,$keys[0]);
        $item_data[] = [
            'key' => __( $keys[1], 'digicalculator' ),
            'value'=> $cart_item_data[$keys[2]].' grams '.$value
        ]; unset($value); unset($keys);
    }

    if( isset( $cart_item_data["printtype"] ) ){ // Look up in options
        $keys = ['printtypes','Bedrukking']; 
        if( $cart_item_data['product_type'] == 'brochures' ){$keys=['printtypes_center','Bedrukking binnenwerk'];}
        $value = getOptionsFromId($cart_item_data['printtype'],$options,$keys[0]);
        $item_data[] = [
            'key' => __( $keys[1], 'digicalculator' ),
            'value'=> $value
        ]; unset($value); unset($keys);
    }

    for ($i=1; $i <= 4; $i++) { 
        if( isset( $cart_item_data["option[$i]"] ) ){ // Look up in options
            $value = getOptionsFromId($cart_item_data["option[$i]"],$options,'finish');
            if( !$value ){ $value = getOptionsFromId($cart_item_data["option[$i]"],$options,'generalfinish'); }
            if( !$value ){ $value = getOptionsFromId($cart_item_data["option[$i]"],$options,'bounds'); }
            if( !$value ){ $value = getOptionsFromId($cart_item_data["option[$i]"],$options,'brochdouble'); }
            if( !$value ){ $value = getOptionsFromId($cart_item_data["option[$i]"],$options,'finish_cover'); }
            if( !$value ){ $value = getOptionsFromId($cart_item_data["option[$i]"],$options,'finish_center'); }
            
            $d = "Afwerking $i";
            if( $cart_item_data['product_type'] == 'brochures' ){
                if( $i == 1 ){ $d = 'Afwerking brochure'; }
                if( $i == 2 ){ $d = 'Luxe afwerking omslag'; }
                if( $i == 3 ){ $d = 'Luxe afwerking binnenwerk'; }
            }
            if( $value != false ){
                $item_data[] = [
                    'key' => __( $d, 'digicalculator' ),
                    'value'=> $value
                ]; unset($value); 
            }
        }
    }
    if( isset( $cart_item_data["versions"] ) ){ // Look up in options
        $item_data[] = [
            'key' => __( "Versies", 'digicalculator' ),
            'value'=> $cart_item_data['versions']
        ];
    } 
    
    $item_data[] = [
        'key' => __( 'Verwachte leverdatum', 'digicalculator' ),
        'value'=> implode('-',array_reverse(explode('-',calculateDate(4))))
    ]; unset($value); 

    return $item_data;
}
function cart_obj_to_string($cart_item_data){
    $html = '<br>';
    foreach($cart_item_data as $value) {
        if( !isset($value['title']) ){
            $value['title'] = $value['key'];
        }
        $html .= '<b>' . $value['title'] . '</b>' . ' - ' . $value['value'] . '<br/>';
    }
    return $html;
}

function calculatePrice($pricedata){
    unset($pricedata['variation']);
    unset($pricedata['line_tax_data']);
    unset($pricedata['order_files']);
    if( $pricedata['product_size'] == 'unique' ){
        $pricedata['product_size'] = $pricedata['custom_width'].'x'. $pricedata['custom_height'];
    }
    // ci_log(json_encode($cart_item));
    $prices = getPrices($pricedata, true);
    // ci_log(json_encode($prices));
    if( isset($prices['total_costs']) ){
        $price = $prices['total_costs']['prices'][$pricedata["quantity"]][0];
    } else {
        $price = 0;
    }
    return $price;
}
function getOptionsFromId($id,$options,$key){
    if( isset($options[$key]) ){
        foreach ($options[$key] as $option) {
            if( $option['id'] == $id){
                return $option['name'];
            }
        }
    }
    return false;
}

