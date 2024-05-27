<?php

add_filter('init', function ($template) {
    global $wpdb;
    // require_once(DIGICALCULATOR_PATH . '/plugins/fpdf186/fpdf.php');
    require_once(DIGICALCULATOR_PATH . '/plugins/dompdf/autoload.inc.php');
    //Get URL;
    $p4 = array_reverse(array_diff(explode('/', home_url(add_query_arg(null, null))), ['']));
    if ($p4[1] == 'pdf') {
        $table_name = $wpdb->prefix . 'digicalculator_saved';
        $user_id = wp_get_current_user()->data->ID;
        // include DIGICALCULATOR_PATH."/pages/dc-digicalculator-quotation.php";
        ob_start();
        $id = $p4[0];
        $quotedata = $wpdb->get_results("SELECT * FROM `$table_name`
            WHERE `id` = $id AND `user_ID` = $user_id");
        if( count($quotedata) == 0 ){
            die;
        }
        $quotedata = $quotedata[0];
        $quotedata->product_obj = json_decode($quotedata->product_obj);
        $quotedata->quotation_obj = json_decode($quotedata->quotation_obj);

        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $quotedata->img = wp_get_attachment_image_src( $custom_logo_id, 'full', false, $custom_logo_attr )[0];
        } else {
            $quotedata->img = get_template_directory_uri() . '/assets/images/logo.png';
        }

        include(DIGICALCULATOR_PATH."/pages/dc-digicalculator-quotation.php");
        $page = ob_get_contents();
        ob_end_clean();
        
        echo $page;
        die;

        // instantiate and use the dompdf class
        $options = new Dompdf\Options();
        $options->setIsRemoteEnabled(true);
        $dompdf = new Dompdf\Dompdf($options);
        $dompdf->loadHtml($page);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("offerte-PC$id.pdf");
        die;
    }
    // echo home_url( add_query_arg( null, null ));
});
