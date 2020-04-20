<?php
define( 'BLOCK_LOAD', true );
define( 'SHORTINIT', true );
echo __FILE__."\n";
$_SERVER = array();
$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../../..';
echo $_SERVER['DOCUMENT_ROOT']."\n";
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
define('LAB_DIR_PATH', dirname(__FILE__));
require_once(LAB_DIR_PATH."/lab-admin-core.php");
//
$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
$wpdb->prefix = "wp_";

function do_common_curl_call($url) {
    $ch = curl_init($url);
    // Options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "LAB Plugin Wordpress "
    );
    if(defined('WP_PROXY_HOST') && defined('WP_PROXY_PORT') && defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD')){
        curl_setopt($ch, CURLOPT_PROXY, WP_PROXY_HOST);
        curl_setopt($ch, CURLOPT_PROXYPORT, WP_PROXY_PORT);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME.':'.WP_PROXY_PASSWORD);
    }
    // Bind des options et de l'objet cURL que l'on va utiliser
    curl_setopt_array($ch, $options);
    // Récupération du résultat JSON
    $json = json_decode(curl_exec($ch));
    curl_close($ch);
    return $json;
}

//
//echo LAB_DIR_PATH;
delete_hal_table();
hal_download_all();