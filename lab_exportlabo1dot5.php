<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

define( 'BLOCK_LOAD', true );
define( 'SHORTINIT', true );

$_SERVER = array();
$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../../..';

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
define('LAB_DIR_PATH', dirname(__FILE__));
require_once(LAB_DIR_PATH."/lab-admin-core.php");
//
$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
$wpdb->prefix = "wp_";

require __DIR__."/lib/vendor/autoload.php";
require __DIR__."/lab-utils.php";

$filename = 'test.xlsx';
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Disposition: attachment; filename="'.$filename.'"');




$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hello World !');

$writer = new Xlsx($spreadsheet);
$writer->save('hello.xlsx');