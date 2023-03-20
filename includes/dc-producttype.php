<?php

add_filter('product_type_selector', 'add_digicalculator_product_type');
function add_digicalculator_product_type($types)
{
    $types['digicalculator'] = 'Digicalculator';
    return $types;
}

// --------------------------
// #2 Add New Product Type Class

add_action('init', 'create_digicalculator_product_type');

function create_digicalculator_product_type()
{
    class WC_Product_Custom extends WC_Product
    {
        public function get_type()
        {
            return 'digicalculator';
        }
    }
}

// --------------------------
// #3 Load New Product Type Class

add_filter('woocommerce_product_class', 'woocommerce_product_class', 10, 2);

function woocommerce_product_class($classname, $product_type)
{
    if ($product_type == 'digicalculator') {
        $classname = 'WC_Product_Custom';
    }
    return $classname;
}

// --------------------------
// #4 Show Product Data General Tab Prices

// add_action('woocommerce_product_options_general_product_data', 'digicalculator_product_type_show_price');

function digicalculator_product_type_show_price(){
    global $product_object;
    if ($product_object && 'digicalculator' === $product_object->get_type()) {
        wc_enqueue_js("
         jQuery('.product_data_tabs .general_tab').addClass('show_if_digicalculator').show();
         jQuery('.pricing').addClass('show_if_digicalculator').show();
      ");
    }
}