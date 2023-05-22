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
    // for some reason when a key ends with a ], it disapears when posted to this script. Check all keys and add when missing
    foreach ($ajax_post as $key => $value) {
        if( str_contains($key,'[') && $key[strlen($key)-1] != "]" ){
            $ajax_post["$key]"] = $value;
            unset($ajax_post[$key]);
        }
    }
    // echo json_encode( $ajax_post );
    // return false;

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
    // ci_log(json_encode($prices));

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
                                $price = $values[0];
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
