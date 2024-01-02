<?php

if (!function_exists('ci_log')) {
    function ci_log($row)
    {
        $row = date("Y-m-d H:i:s") . "\t" . $row;
        $logfile = __DIR__ . "/../log.log";
        if (file_exists($logfile)) {
            $log = file_get_contents($logfile);
            $log = $row . "\n" . $log;
        } else {
            $log = $row;
        }
        // file_put_contents($logfile,$log);
    }
}

if (isset($_GET['getPrices'])) {
    $ajax_post = $_POST;
    if (count($_POST) == 0 || $_POST == '') {
        $data = file_get_contents("php://input");
        $ajax_post = json_decode($data, true);
    }
    getPrices($ajax_post);
}

function getPrices($ajax_post, $json = false){
    global $wpdb, $product;
    //For the call it should be brochure, and not brochures.
    if( $ajax_post['product_type'] == 'brochures' ){
        $ajax_post['product_type'] = 'brochure';
    }
    // for some reason when a key ends with a ], it disapears when posted to this script. Check all keys and add when missing
    foreach ($ajax_post as $key => $value) {
        if( str_contains($key,'[') && $key[strlen($key)-1] != "]" ){
            $ajax_post["$key]"] = $value;
            unset($ajax_post[$key]);
        }
    }

    //Check if the dataset is complete;
    $missing = []; $toCheck = ['printtype','papertype','weight','printtype_cover','papertype_cover','weight_cover'];
    foreach( $toCheck as $checkKey ){
        if( isset( $ajax_post[$checkKey] ) ){
            if( $ajax_post[$checkKey] == "" || $ajax_post[$checkKey] == 0 ){
                $missing[] = $checkKey;
            }
        }
    }
    if( count($missing) > 0 ){
        ?>
            <span missing-fields="<?php echo implode(',',$missing); ?>" class="error">Niet alle verplichte velden zijn ingevuld</span>
        <?php
        return false;
    }
    // ci_log( json_encode( $ajax_post ) );
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://digicalculator.nl/calculator/calculator/calculate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $ajax_post,
        CURLOPT_HTTPHEADER => [
            'Host: www.digicalculator.nl',
            'Authorization: Basic cHJpbnRjYWxjQGdyb290c2dlZHJ1a3Qubmw6WHFDcDJAZXtNeEcmWjhddQ==',
        ],
    ));

    $prices = json_decode(curl_exec($curl), true);
    curl_close($curl);

    //Check if there's is a discount; First check if there is a product id; 
    if( isset($ajax_post['productid']) ){
        $product_id = $ajax_post['productid'];
        $coupons = $wpdb->get_col("SELECT LOWER(post_title) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'shop_coupon'");
        foreach ($coupons as $coupon) {
            $wp_coupon = new WC_Coupon( $coupon );
            if( $wp_coupon->is_valid() ){
                //Check if the ID is allowed
                if( !is_array($wp_coupon->product_ids) || in_array($product_id,$wp_coupon->product_ids) ){
                    if( !is_array($wp_coupon->excluded_product_ids) || !in_array($product_id,$wp_coupon->excluded_product_ids) ){
                        //Discount;
                        foreach ($prices['total_costs']['prices'] as &$single_price) {
                            # code...
                            if( $wp_coupon->discount_type == 'percent' )
                            $single_price[] = $single_price[0]*((100-floatval($wp_coupon->amount))/100);
                        }
                    }
                }
            }
        }
    } //No product ID, no discount

    if ($json) {
        return $prices;
    } else {
        if (is_array($prices)) {
            if (count($prices['error']) > 0) {
        ?>
                <span class="error"><?php echo implode('<br/>', $prices['error']); ?></span>
            <?php
            } else if (!isset($prices['total_costs']) > 0) {
            ?>
                <span class="error">Onbekende fout</span>
            <?php
            } else {
            ?> <table> <?php
                            ?> <tr>
                        <th>Oplage</th>
                        <th>Prijs</th>
                    </tr> <?php
                            $tprices = $prices['total_costs']['prices'];
                            ksort($tprices);
                            foreach ($tprices as $quantity => $values) {
                                if( isset($values[2]) ){
                                    $price = $values[2];
                                } else {
                                    $price = $values[0];
                                }
                            ?> <tr>
                            <td name="dc-quantity"><?php echo $quantity; ?></td>
                            <td name="dc-price"><?php echo wc_price(round($price, 2)); ?></td>
                        </tr> <?php
                            }
                                ?> </table>
                <!-- <p> <?php echo json_encode($prices); ?> </p>
                <p> <?php echo json_encode($ajax_post); ?> </p> -->
            <?php
            }
        } else {
            ?>
            <span class="error">Onbekende fout</span>
        <?php
        }
    }
}

function getOptions(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://digicalculator.nl/calculator/calculator/getOptions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Host: www.digicalculator.nl',
            'Authorization: Basic cHJpbnRjYWxjQGdyb290c2dlZHJ1a3Qubmw6WHFDcDJAZXtNeEcmWjhddQ=='
        ),
    ));

    $options = json_decode(curl_exec($curl), true);
    // var_dump($options);
    curl_close($curl);
    return $options;
}
