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
//require __DIR__."/lab-admin-core.php";

$filename = htmlspecialchars($_GET["filename"]);
$do       = htmlspecialchars($_GET["do"]);
$param    = htmlspecialchars($_GET["param"]);

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Disposition: attachment; filename="'.$filename.'"');

if ($do == "presentOfTheWeek")
{
    $firstDayOfTheWeek = $param;
    $firstDayDateTime = strtotime($firstDayOfTheWeek);

    $startDay = getFirstMondayOfTheWeek($firstDayDateTime);
    $endDay = strtotime('+5 days', $startDay);

    $usersPresent = lab_admin_list_present_user($startDay, $endDay);

    $users = array();
    $userId = 0;
    foreach($usersPresent as $user) {
        // convert dateString to timeStamp obj
        $user->hour_start = strtotime($user->hour_start);
        $user->hour_end   = strtotime($user->hour_end);

        if ($userId == 0 || $userId != $user->user_id) {
            $userId = $user->user_id;
            $users[$user->first_name." ".$user->last_name][date('d', $user->hour_start)][] = $user;
        }
        else if ($userId == $user->user_id) {
            $users[$user->first_name." ".$user->last_name][date('d', $user->hour_start)][] = $user;
        }
    }
    $dayBuff = $startDay;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Prénom');
    $sheet->setCellValue('B1', 'Nom');
    $sheet->setCellValue('C1', 'Employeur');
    $sheet->setCellValue('D1', 'Site');
    $sheet->setCellValue('E1', 'Etage');
    $sheet->setCellValue('F1', 'Bureau');
    $sheet->setCellValue('G1', 'Date');
    $sheet->setCellValue('H1', 'Arrivé');
    $sheet->setCellValue('I1', 'Départ');
    $sheet->setCellValue('J1', 'Motif');
    $line = 1;
    $listSite = lab_admin_list_site();
    
    foreach($users as $k=>$v) 
    {        
        for ($i = 0 ; $i < 5 ; $i++) 
        {
            $currentDay   = strtotime('+'.$i.' days', $startDay);
            $currentDayDT = date('d', $currentDay);
            
            if(array_key_exists($currentDayDT, $v))
            {                
                foreach($v[$currentDayDT] as $user) 
                {
                    $line++;
                    $sheet->setCellValue('A'.$line, $user->first_name);
                    $sheet->setCellValue('B'.$line, $user->last_name);
                    $sheet->setCellValue('C'.$line, lab_admin_user_get_employer($user->user_id));    
                    $sheet->setCellValue('D'.$line, $user->site);
                    if(isset($user->office)) {
                        $sheet->setCellValue('E'.$line, $user->office);
                        $sheet->setCellValue('F'.$line, $user->floor);
                    }
                    $sheet->setCellValue('G'.$line, date("d-m-Y",$currentDay));
                    $sheet->setCellValue('H'.$line, date("H:i",$user->hour_start));
                    $sheet->setCellValue('I'.$line, date("H:i",$user->hour_end));
                    $sheet->setCellValue('J'.$line, $user->comment);
                }
                //*/
            }
            //*/
        }

    }
    
    
    $writer = new Xlsx($spreadsheet);
    //$writer->save('hello world.xlsx');
    $writer->save( "php://output" );

}