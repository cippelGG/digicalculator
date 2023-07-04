<?php
/**
 * @since             1.4.0
 * @package           Digicalculator
 *
 * Plugin Name:       Digicalculator
 * Description:       Special connection with the Digicalculator server.
 * Version:           1.4.1
 * Author:            Chanan Ippel
 */

 ini_set('log_errors','On'); // enable or disable PHP error logging (use 'On' or 'Off')
 ini_set('display_errors','Off'); // enable or disable public display of errors (use 'On' or 'Off')
 error_reporting(E_ALL);
 ini_set('error_log',plugin_dir_path(__FILE__)."error.log"); // path to server-writable log file

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
require_once plugin_dir_path(__FILE__) . 'includes/dc-orderplaced.php';

