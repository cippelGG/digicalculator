<?php

//Add page for client in their own environment
add_action('init', 'my_account_digicalculator_endpoint');
function my_account_digicalculator_endpoint(){
    add_rewrite_endpoint('digicalculator-saved', EP_ROOT | EP_PAGES);
}

add_filter('woocommerce_account_menu_items', 'digicalculator_menu_items');
function digicalculator_menu_items($items){
    $beforekey = "customer-logout";
    $items = array_slice($items, 0, array_search($beforekey, array_keys($items)), true) +
        array("digicalculator-saved" => __('Opgeslagen offertes', 'digicalculator')) +
        array_slice($items, array_search($beforekey, array_keys($items)), count($items) - array_search($beforekey, array_keys($items)), true);

    return $items;
}

add_action('woocommerce_account_digicalculator-saved_endpoint', 'digicalculator_endpoint_content');
function digicalculator_endpoint_content(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'digicalculator_saved';
    $user_id = wp_get_current_user()->data->ID;
    $p4 = array_reverse(array_diff(explode('/', home_url( add_query_arg( null, null ))),['']));
    $paged = intval($p4[0]) ? intval($p4[0]) : 1;
    $basepage = str_replace(home_url(),'',str_replace("/$paged","",home_url( add_query_arg( null, null ))));

    if( $p4[1] == "offer" ){
        $order_detail = $wpdb->get_results ( "SELECT * FROM $table_name WHERE `user_ID` = '$user_id'");
        return false;
    } else if($p4[0] == "test"){
        echo json_encode(digicalculator_add_cart_item_data(json_decode('{"product_size":"74x105","custom_width":"210","custom_height":"297","undefined":"3","pages":"2","printtype":"20","papertype":"33","weight":"90","option[1":"67","option[4":"19","product_type":"default","versions":"1","dc-picked-quantity":"98","quantity":"98","addresses":"1","width":"1","height":"1"}',true),11,1));
        return false;
    }

    $user_quotes = $wpdb->get_results ( "SELECT * FROM $table_name WHERE `user_ID` = '$user_id'");
    if( count($user_quotes) == 0 ){
        return false;
    }
    $user_quotes = array_reverse($user_quotes);
    $next = true; $prev = true;
    if( $paged == 1 ){ $prev = false; }
    
    ?>
    <table class="shop_table my_account_orders">
        <thead><tr>
            <th class="woocommerce-orders-table__header">Offerte</th>
            <th>Datum</th>
            <th>Referentie</th>
            <th>Totaal</th>
            <th>Acties</th>
        </tr></thead>
        <tbody>
            <?php
                $max_per_page = 10;
                for ($i=(($paged*$max_per_page) - ($max_per_page)); $i < ($paged*$max_per_page); $i++) { 
                    if( $i > count($user_quotes)-1 ){
                        $next = false;
                        break;
                    }
                    $row = $user_quotes[$i];
                    ?>
                        <tr>
                            <td>PC<?php echo $row->id; ?></td>
                            <td><?php echo wp_date('d F Y',strtotime($row->date_created)); ?></td>
                            <td><?php echo $row->ext_ref; ?></td>
                            <td><?php echo wc_price(round($row->price, 2)); ?></td>
                            <td>
                                <a href="pdf/<?php echo $row->id; ?>" target="_blank" class="woocommerce-button wp-element-button button view">Bekijken</a>
                                <a onclick='$dc.postDcIdToCart(<?php echo $row->id; ?>)' class="woocommerce-button wp-element-button button">In winkelwagen</a>
                            </td>
                        </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
    <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
    
    <?php if($prev){
        ?> <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo $basepage;echo $paged-1; ?>">Vorige</a> <?php
    } ?>
    <?php if($next){
        ?> <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo $basepage;echo $paged+1; ?>">Volgende</a> <?php
    } ?>
					</div>
    <?php
}
  
//Create the database for the orders;
function create_digicalculator_saved_table(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'digicalculator_saved';

    $sql = "CREATE TABLE " . $table_name . " (
        id INT NOT NULL AUTO_INCREMENT,
        date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        date_changed DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        user_ID INT NOT NULL,
        ext_ref TINYTEXT NOT NULL,
        product_obj TEXT NOT NULL,
        quotation_obj TEXT NOT NULL,
        status TINYTEXT NOT NULL,
        price FLOAT NOT NULL,
        product_description TINYTEXT NOT NULL,
        shop_order INT NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(DIGICALCULATOR_BASE, 'create_digicalculator_saved_table');

add_action('wp_ajax_DC-saveQuotation', 'dc_savequotation_ajax');
add_action('wp_ajax_nopriv_DC-saveQuotation', 'dc_savequotation_ajax');
function dc_savequotation_ajax(){
    global $wpdb;
    $user_id = wp_get_current_user()->data->ID;
    $user = get_user_meta( $user_id ); 

    $nonce = $_POST['nextNonce'];
    if (!wp_verify_nonce($nonce, 'dc-next-nonce')) {
        die('Busted!');
    }
    $data = ($_POST['data']);
    $data['prices'] = getPrices($data['product_obj'], true);

    $table_name = $wpdb->prefix . 'digicalculator_saved';
    $extref = $data['extref'];
    $product_description = $data['product_description'];
    $product_obj = json_encode($data['product_obj']);

    $price = 0;
    foreach ($data['prices']['total_costs']['prices'] as $quan => $pricey) {
        if ($quan == $data['product_obj']['quantity']) {
            if( isset($pricey[2]) ){
                $price = $pricey[2];
            } else {
                $price = $pricey[0];
            }
        }
    }
    // Enrich quotation_obj 
    ksort($data['prices']['total_costs']['prices']);
    $data['quotation_obj'] = ['options'=>$data['quotation_obj']];
    $data['quotation_obj']['prices'] = $data['prices']['total_costs']['prices'];
    $data['quotation_obj']['billing_addres'] = [
        "billing_first_name" => $user['billing_first_name'][0],
        "billing_company" => $user['billing_company'][0],
        "billing_name" => $user['billing_first_name'][0]." ".$user['billing_last_name'][0],
        "billing_address" => $user['billing_address_1'][0],
        "billing_postCity" => $user['billing_postcode'][0]." ".$user['billing_city'][0],
        "billing_country" => $user['billing_country'][0],
    ];

    $quotation_obj = json_encode($data['quotation_obj']);

    $wpdb->query("INSERT INTO `$table_name`
        (`user_ID`, `ext_ref`,  `product_obj`, `status`, `price`, `product_description`, `quotation_obj`) VALUES 
        ('$user_id','{$extref}','$product_obj','new',    '$price','$product_description','$quotation_obj')
    ");

    header("Content-Type: application/json");
    wc_add_notice("{$data['extref']} is toegevoegd aan uw offertes.", 'success');
    echo json_encode($data);

    // response output
    // echo getPrices($data);

    // IMPORTANT: don't forget to "exit"
    exit;
}

add_action('wp_ajax_DC-getQuotation', 'dc_getquotation_ajax');
add_action('wp_ajax_nopriv_DC-getQuotation', 'dc_getquotation_ajax');
function dc_getquotation_ajax(){
    global $wpdb;
    $user_id = wp_get_current_user()->data->ID;
    $nonce = $_POST['nextNonce'];
    if (!wp_verify_nonce($nonce, 'dc-next-nonce')) {
        die('Busted!');
    }

    $table_name = $wpdb->prefix . 'digicalculator_saved';
    $data = ($_POST['data']);
    $resp = $wpdb->get_results("SELECT `product_obj` FROM `$table_name`
    WHERE `id` = {$data['id']} AND `user_ID` = $user_id");
    
    if( count($resp) > 0 ){
        $resp[0]->product_obj = str_replace('[1','[1]',$resp[0]->product_obj);
        $resp[0]->product_obj = str_replace('[2','[2]',$resp[0]->product_obj);
        $resp[0]->product_obj = str_replace('[3','[3]',$resp[0]->product_obj);
        $resp[0]->product_obj = str_replace('[4','[4]',$resp[0]->product_obj);
        wc_add_notice("{$data['extref']} is toegevoegd aan uw winkelwagen.", 'success');
        echo ($resp[0]->product_obj);
    } else {
        die('Not your order!');
    }
    exit;
}
 