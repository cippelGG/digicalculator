<?php
// --------------------------
// #5 Add javascript, css and ajax
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'digicalculator',
        plugin_dir_url(__FILE__) . '../js/digicalculator.js'
    );
    wp_localize_script(
        'digicalculator',
        'dc_ajax',
        array( 
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nextNonce' => wp_create_nonce('myajax-next-nonce')
        )
    );
    wp_enqueue_style(
        'digicalculator',
        plugin_dir_url(__FILE__) . '../assets/style.min.css'
    );
}, 99);

add_action( 'wp_ajax_DC-getPrices', 'dc_getprices_ajax' );
add_action( 'wp_ajax_nopriv_DC-getPrices', 'dc_getprices_ajax' );

function dc_getprices_ajax(){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);    

    $nonce = $_POST['nextNonce'];
    if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
        die ( 'Busted!' );
    }
    $data = ( $_POST['data']);
 
    // response output
    header( "Content-Type: application/json" );
    echo getPrices($data);
 
    // IMPORTANT: don't forget to "exit"
    exit;
}


// --------------------------
// #6 Add page layout above order button
add_action("woocommerce_digicalculator_add_to_cart", function () {
    global $product;
    if ($product->get_type() != 'digicalculator') {
        return false;
    }

    require_once plugin_dir_path(__FILE__) . '../pages/dc-digicalculator-table.php';
    require_once plugin_dir_path(__FILE__) . 'dc-calls.php';

    update_post_meta($product->get_id(), '_price', 0);

    $root = get_site_url();
    echo "<script>
        var dc_wproot = '$root';
    </script>";
    //Get data;
    $options = getOptions();
    // ci_log(json_encode($options));
    dc_digicalculator_table($options);

    // do_action('woocommerce_simple_add_to_cart');
    // $arr['Color'] = 'Green';
    // WC()->cart->add_to_cart( 24, 1, 28, $arr, null ); 
    // add_to_cart( $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data = array() ) {
});
