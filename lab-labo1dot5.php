<?php

function lab_labo1dot5_save()
{
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
    //*/
    wp_send_json_success( $data ); 
    /*
    for ($i = 0 ; $i <= count($indexes) ; $i++)
    {
        $wpdb->insert($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$_POST['from'.$indexes[$i]], "means"=>$_POST['lab_transport_to'.$indexes[$i]], "travel_to"=>$_POST['to'.$indexes[$i]]));
    }
    
    wp_send_json_success( $length );
    /*
    $from0 = $_POST['from0'];
    $to0 = $_POST['to0'];
    $transportMeans0 = $_POST['lab_transport_to0'];

   
    
    if ($wpdb->insert($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$from, "means"=>$transportMeans, "travel_to"=>$to)))
    {
        wp_send_json_success( $wpdb->insert_id );
    }
    else
    {
        wp_send_json_error( "Fail to insert ..." );
    }
    //*/
}
