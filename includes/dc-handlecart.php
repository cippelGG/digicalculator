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
        foreach ($_POST as $key => $value) {
            if( $key == 'option' ){
                foreach ($value as $i => $option) {
                    $cart_item_data["{$key}[$i]"] = $option;
                }
            } else {
                $cart_item_data[$key] = $value;
            }
            # code...
        }
        $cart_item_data['addresses'] = 1;
        $cart_item_data['width'] = 1;
        $cart_item_data['height'] = 1;
        $cart_item_data['digicalculator_product'] = true;
        $cart_item_data['make_unique'] = date("YmdHis");
        // ci_log(json_encode($cart_item_data));
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'digicalculator_add_cart_item_data', 10, 3);



$dc_gl_options = getOptions();
//This adds the variables to the shoppingcart page visualy
function digicalculator_add_variables_to_cart($item_data, $cart_item_data){
    if( isset($cart_item_data['digicalculator_product']) ){
        if( $cart_item_data['digicalculator_product'] ){
            // $item_data = array_merge($item_data,cart_item_data_to_obj($cart_item_data));
            // $item_data[] = [
            //     'key' => __( "dc_connect-product_keys", 'digicalculator' ),
            //     'value'=> json_encode( cart_item_data_to_obj($cart_item_data) )
            // ];


            $item_data[] = [
                'key' => __( "Samenstelling", 'digicalculator' ),
                'value'=> cart_obj_to_string( cart_item_data_to_obj($cart_item_data) )
            ];
        }
    }
    // ci_log(json_encode($options)); 
    return $item_data;
}
add_action( 'woocommerce_get_item_data', 'digicalculator_add_variables_to_cart', 10, 4 );

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
            ci_log(json_encode($values));
            ci_log("item_data: ".$item_data['value']);
        
            $obj = [];
            foreach ($item_data as $it_da) {
                $obj[] = ['title'=>$it_da["key"],'value'=>$it_da['value']]; 
                // $item->add_meta_data( __( $it_da["key"], 'digicalculator' ), $it_da['value']);
            }
            $item->add_meta_data( 'dc_connect-product_keys', json_encode($obj));
        }
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'digicalculator_add_variable_to_order_items', 10, 4 );

//This adds the variables to the backend in a styled manner
function digicalculator_connect_wc_order_item_get_formatted_meta_data( $formatted_meta, $item ) {
    foreach($formatted_meta as $key => $meta) {
        ci_log(json_encode($meta));
        ci_log("Meta");
        if($meta->key == 'dc_connect-product_keys') {
            $meta->display_key = 'Samenstelling';
            
            $values = json_decode($meta->value, true);

            $html = '<br>';
            foreach($values as $value) {
                $html .= '<b>' . $value['title'] . '</b>' . ' - ' . $value['value'] . '<br/>';
            }
            $meta->display_value = $html;
        }
    }
    return $formatted_meta;
}
add_filter( 'woocommerce_order_item_get_formatted_meta_data','digicalculator_connect_wc_order_item_get_formatted_meta_data', 10, 2);



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

    if( isset( $cart_item_data["papertype_cover"] ) ){ // Look up in options
        $value = getOptionsFromId($cart_item_data['papertype_cover'],$options,'materials_cover');
        $item_data[] = [
            'key' => __( "Materiaal omslag", 'digicalculator' ),
            'value'=> $cart_item_data["weight"].' grams '.$value
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
        if( $cart_item_data['product_type'] == 'brochures' ){$keys=['materials_center','Materiaal binnenwerk','weight_cover'];}
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
                if( $i == 3 ){ $d = 'Luxe afwerking binnenwwerk'; }
            }
            if( $value != false ){
                $item_data[] = [
                    'key' => __( $d, 'digicalculator' ),
                    'value'=> $value
                ]; unset($value); 
            }
        }
    }
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

