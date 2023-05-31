<?php
/**
 * @since             1.1.6
 * @package           Digicalculator
 *
 * Plugin Name:       Digicalculator
 * Description:       Special connection with the Digicalculator server.
 * Version:           1.2.0
 * Author:            Chanan Ippel
 */

if (!defined('ABSPATH')) {
    return;
}

function ci_log($row){
    $row = date("Y-m-d H:i:s")."\t".$row;
    $logfile = __DIR__."/log.log";
    if( file_exists($logfile) ){
        $log = file_get_contents($logfile);
        $log = $row."\n".$log;
    } else {
        $log = $row;
    }
    file_put_contents($logfile,$log);
}

require_once plugin_dir_path(__FILE__) . 'includes/dc-calls.php';
require_once plugin_dir_path(__FILE__) . 'includes/dc-producttype.php';
require_once plugin_dir_path(__FILE__) . 'includes/dc-productpage.php';
require_once plugin_dir_path(__FILE__) . 'includes/dc-adminpage.php';
require_once plugin_dir_path(__FILE__) . 'includes/dc-handlecart.php';
require_once plugin_dir_path(__FILE__) . 'includes/dc-uploadfile.php';

