<?php
add_action('wp_footer', function () {
?>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>


    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>
<?php
});

add_action('wp_ajax_DC-uploadfile', 'dc_uploadfile_ajax');
add_action('wp_ajax_nopriv_DC-uploadfile', 'dc_uploadfile_ajax');

add_action('wp_ajax_DC-deletefile', 'dc_deletefile_ajax');
add_action('wp_ajax_nopriv_DC-deletefile', 'dc_deletefile_ajax');

add_action('wp_ajax_DC-uploadfile-pond', 'dc_uploadfile_ajax_pond');
add_action('wp_ajax_nopriv_DC-uploadfile-pond', 'dc_uploadfile_ajax_pond');
function dc_uploadfile_ajax_pond(){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    $nonce = $_POST['nextNonce'];
    if ( ! wp_verify_nonce( $nonce, 'dc-next-nonce' ) ) {
        die ( 'Busted!' );
    }
    $cart = WC()->cart->cart_contents;
    $cart_item_id = $_POST['cart_item_id'];
    $cart_item = $cart[$cart_item_id];
    // Get file; the way propzone works it's alsways only one file;
    //$_FILES = {"file":{"name":"koor dubbelzinnig.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\phpE575.tmp","error":0,"size":162273}}
    $file = $_FILES['filepond'];
    
    //an order may have more then one file. Check the newest file to get the suffix
    $last_nth = 0;
    $order_files = [];
    if( isset( $cart_item['order_files'] ) ){
        $order_files = $cart_item['order_files'];
        foreach ($cart_item['order_files'] as $orderf) {
            if( $orderf['nth'] >= $last_nth ){
                $last_nth = $orderf['nth'];
            }
        }
        $last_nth++;
    }
    $file_root = ABSPATH . 'wp-content/uploads/dc-uploads';
    $file_name = "$cart_item_id-$last_nth.pdf";
    $file_location = "$file_root/$file_name";
    //Move file;
    if (!file_exists($file_root)) {
        mkdir($file_root, 0777, true);
    }    
    move_uploaded_file($file['tmp_name'], $file_location);

    $order_files[] = [
        "nth" =>$last_nth,
        "name"=>$file['name'],
        "file"=>$file_name,
        "size"=>$file['size'],
        "url"=>get_site_url()."/wp-content/uploads/dc-uploads/{$file_name}",
    ];
   
    $cart_item['order_files'] = $order_files;

    WC()->cart->cart_contents[$cart_item_id] = $cart_item;
    WC()->cart->set_session();
    echo json_encode( [
        "name"=>$file['name'],
        "url"=> get_site_url()."/wp-content/uploads/dc-uploads/{$file_name}",
        "file"=> $file_name,
        "id"=>$cart_item_id,
    ] ); 
    // echo json_encode($_FILES );

    // IMPORTANT: don't forget to "exit"
    exit;
}

function dc_uploadfile_ajax(){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $nonce = $_POST['nextNonce'];
    if ( ! wp_verify_nonce( $nonce, 'dc-next-nonce' ) ) {
        die ( 'Busted!' );
    }
    $cart = WC()->cart->cart_contents;
    $cart_item_id = $_POST['cart_item_id'];
    $cart_item = $cart[$cart_item_id];
    // Get file; the way propzone works it's alsways only one file;
    //$_FILES = {"file":{"name":"koor dubbelzinnig.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\phpE575.tmp","error":0,"size":162273}}
    $file = $_FILES['file'];
    
    //an order may have more then one file. Check the newest file to get the suffix
    $last_nth = 0;
    $order_files = [];
    if( isset( $cart_item['order_files'] ) ){
        $order_files = $cart_item['order_files'];
        foreach ($cart_item['order_files'] as $orderf) {
            if( $orderf['nth'] >= $last_nth ){
                $last_nth = $orderf['nth'];
            }
        }
        $last_nth++;
    }
    $file_root = ABSPATH . 'wp-content/uploads/dc-uploads';
    $file_name = "$cart_item_id-$last_nth.pdf";
    $file_location = "$file_root/$file_name";
    //Move file;
    if (!file_exists($file_root)) {
        mkdir($file_root, 0777, true);
    }    
    move_uploaded_file($file['tmp_name'], $file_location);

    $order_files[] = [
        "nth" =>$last_nth,
        "name"=>$file['name'],
        "file"=>$file_name,
        "size"=>$file['size'],
        "url"=>get_site_url()."/wp-content/uploads/dc-uploads/{$file_name}",
    ];
   
    $cart_item['order_files'] = $order_files;

    WC()->cart->cart_contents[$cart_item_id] = $cart_item;
    WC()->cart->set_session();
    echo json_encode( [
        "name"=>$file['name'],
        "url"=> get_site_url()."/wp-content/uploads/dc-uploads/{$file_name}",
        "file"=> $file_name,
        "id"=>$cart_item_id,
    ] ); 
    // echo json_encode($_FILES );

    // IMPORTANT: don't forget to "exit"
    exit;
}

function dc_deletefile_ajax(){
    //URL = https://localhost/pww/wp-admin/admin-ajax.php?action=DC-deletefile&cart_item_id=5288b5835c1b3d91519004cfb91556de&file=5288b5835c1b3d91519004cfb91556de-0.pdf
    $cart = WC()->cart->cart_contents;
    $cart_item_id = $_GET['cart_item_id'];
    $file = $_GET['file'];
    $file_root = ABSPATH . 'wp-content/uploads/dc-uploads';

    echo "$cart_item_id <br/>";
    echo "$file <br/>";
    
    foreach( $cart[$cart_item_id]['order_files'] as $key => $order_file ){
        echo "$key {$order_file['file']} <br/>";

        if( $order_file['file'] == $file ){
            unset($cart[$cart_item_id]['order_files'][$key]);
            unlink("$file_root/$file");
        }
    }
    WC()->cart->cart_contents[$cart_item_id] = $cart[$cart_item_id];
    WC()->cart->set_session();
}
