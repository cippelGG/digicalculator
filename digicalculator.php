<?php
/**
 * @since             1.6.4.1
 * @package           Digicalculator
 *
 * Plugin Name:       Digicalculator
 * Description:       Special connection with the Digicalculator server.
 * Version:           1.6.4.2
 * Author:            Chanan Ippel
 */

 ini_set('log_errors','On'); // enable or disable PHP error logging (use 'On' or 'Off')
 ini_set('display_errors','Off'); // enable or disable public display of errors (use 'On' or 'Off')
 error_reporting(E_ALL);
 ini_set('error_log',plugin_dir_path(__FILE__)."error.log"); // path to server-writable log file

 define('DIGICALCULATOR_BASE', __FILE__);
 define('DIGICALCULATOR_PATH', plugin_dir_path(__FILE__));
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
require_once plugin_dir_path(__FILE__) . 'includes/dc-saved.php';
require_once plugin_dir_path(__FILE__) . 'includes/dc-saved-pdf.php';

 if ( ! function_exists( 'wp_create_nonce' ) ) {
    /**
    * Creates a cryptographic token tied to a specific action, user, user session,
    * and window of time.
    *
    * @since 2.0.3
    * @since 4.0.0 Session tokens were integrated with nonce creation
    *
    * @param string|int $action Scalar value to add context to the nonce.
    * @return string The token.
    */
    function wp_create_nonce( $action = -1 ) {
        $user = wp_get_current_user();
        $uid  = (int) $user->ID;
        $logged_in = '1-';

        $token = wp_get_session_token();
        $i     = wp_nonce_tick();

        if ( ! $uid ) {
            // Prefix when logged-out nonce
            $logged_in = '0-';

            /** This filter is documented in wp-includes/pluggable.php */
            $uid = apply_filters( 'nonce_user_logged_out', $uid, $action );

            // Use IP instead of user_id
            $uid = $_SERVER['REMOTE_ADDR'];
            $token = $_SERVER['REMOTE_ADDR'];
        }

        return $logged_in . substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
    }
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
    /**
    * Verify that correct nonce was used with time limit.
    *
    * The user is given an amount of time to use the token, so therefore, since the
    * UID and $action remain the same, the independent variable is the time.
    *
    * @since 2.0.3
    *
    * @param string     $nonce  Nonce that was used in the form to verify
    * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
    * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
    *                   0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
    */
    function wp_verify_nonce( $nonce, $action = -1 ) {
        $nonce = (string) $nonce;
        $user  = wp_get_current_user();
        $uid   = (int) $user->ID;
        if ( ! $uid ) {
            /**
            * Filters whether the user who generated the nonce is logged out.
            *
            * @since 3.5.0
            *
            * @param int    $uid    ID of the nonce-owning user.
            * @param string $action The nonce action.
            */
            $uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
        }

        if ( empty( $nonce ) ) {
            return false;
        }

        $token = wp_get_session_token();
        $i     = wp_nonce_tick();

        // Check if nonce is for logged_in or logged_out ('1-' and '0-' respectively)
        if ( substr( $nonce, 0, 2 ) == '0-' ) {
            // Use IP instead of user_id and session token
            $uid = $_SERVER[ 'REMOTE_ADDR' ];
            $token = $_SERVER['REMOTE_ADDR'];
        }

        // Remove nonce prefix
        $nonce = substr( $nonce, 2 );

        // Nonce generated 0-12 hours ago
        $expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );

        if ( hash_equals( $expected, $nonce ) ) {
            return 1;
        }

        // Nonce generated 12-24 hours ago
        $expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
        if ( hash_equals( $expected, $nonce ) ) {
            return 2;
        }

        /**
        * Fires when nonce verification fails.
        *
        * @since 4.4.0
        *
        * @param string     $nonce  The invalid nonce.
        * @param string|int $action The nonce action.
        * @param WP_User    $user   The current user object.
        * @param string     $token  The user's session token.
        */
        do_action( 'wp_verify_nonce_failed', $nonce, $action, $user, $token );

        // Invalid nonce
        return false;
    }
}
