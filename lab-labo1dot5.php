<?php

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 

function lab_labo1dot5_get(){
    global $wpdb;
    $limitM=$_POST["limitM"];
    $limitN=$_POST["limitN"];
    $userId=$_POST["user_id"];
    $orderBy=$_POST["orderBy"];

    $sql = "SELECT * FROM `".$wpdb->prefix."lab_labo1dot5` AS lb
            JOIN `".$wpdb->prefix."lab_labo1dot5_historic` AS lbhis ON lb.`travel_id`=lbhis.`travel_id`";

    if ($userId != "")
    {
        $sql .= " WHERE lbhis.`user_id` = $userId"; 
    };

    if ($orderBy != "")
    {
        $sql .= " ORDER BY $orderBy";
    };
    $sql .= " LIMIT $limitM, $limitN";

    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results ); 
}

function lab_labo1dot5_getRowNum_ajax(){
    wp_send_json_success( lab_labo1dot5_getRowNum() ); 
}

function lab_labo1dot5_getRowNum(){
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_labo1dot5`";
     
    $results = $wpdb->get_results($sql);  
    return $results;
}

function lab_labo1dot5_save(){
    $data = "";
    global $wpdb;

    date_default_timezone_set('Europe/Paris');
    $date_motif = date('Y-m-d H:m:s',time());
    $user_id = get_current_user_id();
    
    $sql = "SELECT max(travel_id) AS maxTravelId FROM `".$wpdb->prefix."lab_labo1dot5_historic`";
    //wp_send_json_success( "SQL : " . $sql );
    //return;
    $travel_id = $wpdb->get_results($sql);
    //var_dump($travel_id);
    if(!isset($travel_id) || $travel_id == NULL)
    {
        $travel_id = 0;
    }
    else {
        $travel_id = $travel_id[0]->maxTravelId;
    }
    $travel_id += 1;

    $wpdb->insert($wpdb->prefix.'lab_labo1dot5_historic',array("travel_id"=>$travel_id,"user_id"=>$user_id));

    foreach ($_POST as $key => $value) {
        if (strlen($key) > 4)
        {
            if (substr( $key, 0, 4 ) === "from")
            {
                $index = substr( $key,4);
                
                $wpdb->insert($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$_POST['from'.$index],  "means"=>$_POST['lab_transport_to'.$index],
                                                                   "travel_to"  =>$_POST['to'.$index]        , "country_from"=>$_POST['country_from'.$index],
                                                                   "country_to" =>$_POST['country_to'.$index], "go_back"     =>$_POST['go_back'.$index],
                                                                   "travel_date"=>$_POST['travel_date'.$index],"travel_id"   =>$travel_id));
            }
        }
    }
    wp_send_json_success( $data ); 
   
}


function lab_labo1dot5_saveadmin(){
    $data = "";
    global $wpdb;

    date_default_timezone_set('Europe/Paris');
    $date_motif = date('Y-m-d H:m:s',time());
    $user_id = get_current_user_id();
    
    $sql = "SELECT max(travel_id) AS maxTravelId FROM `".$wpdb->prefix."lab_labo1dot5_historic`";
    //wp_send_json_success( "SQL : " . $sql );
    //return;
    $travel_id = $wpdb->get_results($sql);
    //var_dump($travel_id);
    if(!isset($travel_id) || $travel_id == NULL)
    {
        $travel_id = 0;
    }
    else {
        $travel_id = $travel_id[0]->maxTravelId;
    }
    $travel_id += 1;

    $wpdb->insert($wpdb->prefix.'lab_labo1dot5_historic',array("travel_id"=>$travel_id,"user_id"=>$user_id));
    $wpdb->insert($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$_POST['travel_from'],  "means"=>$_POST['means'],
                                                       "travel_to"  =>$_POST['travel_to']        , "country_from"=>$_POST['country_from'],
                                                       "country_to" =>$_POST['country_to'], "go_back"     =>$_POST['go_back'],
                                                       "travel_date"=>$_POST['travel_date'],"travel_id"   =>$travel_id,
                                                       "status"=>$_POST['status']));
    
    wp_send_json_success( $data ); 
   
}

function lab_labo1dot5_deleteadmin(){

    $data = "";
    global $wpdb;
    $travel_id = $_POST['travel_id'];
    $travel_idint = intval($travel_id);

    $sql1 = "DELETE FROM `".$wpdb->prefix."lab_labo1dot5` WHERE travel_id=$travel_idint " ;
    $sql2 = "DELETE FROM `".$wpdb->prefix."lab_labo1dot5_historic` WHERE travel_id=$travel_idint " ;
     
    $results1 = $wpdb->get_results($sql1);  
    $results2 = $wpdb->get_results($sql2); 
    wp_send_json_success( $results1 ); 
    wp_send_json_success( $results2 ); 
}

function lab_labo1dot5_updateadmin(){

    $data = "";
    global $wpdb;
    $travel_id = $_POST['travel_id'];
    $travel_idint = intval($travel_id);

    $wpdb->update($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$_POST['travel_from'],  "means"=>$_POST['means'],
                                                       "travel_to"  =>$_POST['travel_to']   , "country_from"=>$_POST['country_from'],
                                                       "country_to" =>$_POST['country_to'],   "go_back"     =>$_POST['go_back'],
                                                       "travel_date"=>$_POST['travel_date'],  "status"=>$_POST['status']),
                                                 array("travel_id"=>$travel_idint)); 
    
    wp_send_json_success($data); 
}

function lab_labo1dot5_exportExcel(){

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Travel_ID');
    $sheet->setCellValue('A2', 'Prénom');
    /*$sheet->setCellValue('B1', 'Country_from');
    $sheet->setCellValue('C1', 'Travel_from');
    $sheet->setCellValue('D1', 'Country_to');
    $sheet->setCellValue('E1', 'Travel_to');
    $sheet->setCellValue('F1', 'Means');
    $sheet->setCellValue('G1', 'Go_back');*/

    global $wpdb;

    //$userId=$_POST["user_id"];
    //$orderBy=$_POST["orderBy"];
    $sql = "SELECT lb.`travel_id` FROM `".$wpdb->prefix."lab_labo1dot5` AS lb
    JOIN `".$wpdb->prefix."lab_labo1dot5_historic` AS lbhis ON lb.`travel_id`=lbhis.`travel_id`";

    /*if ($userId != "")
    {
    $sql .= " WHERE lbhis.`user_id` = $userId"; 
    };

    if ($orderBy != "")
    {
    $sql .= " ORDER BY $orderBy";
    };*/ 

    //$results = $wpdb->get_results($sql);  
    //wp_send_json_success( $results ); 
    $writer = new Xlsx($spreadsheet);
    //$writer->save('hello world.xlsx');
    $writer->save( "php://output" );


}