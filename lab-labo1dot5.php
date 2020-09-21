<?php

function lab_labo1dot5_save()
{
    $data = "";
    global $wpdb;

    date_default_timezone_set('Europe/Paris');
    $travel_id = get_current_user_id().date('YmdHis', time());
    
    foreach ($_POST as $key => $value) {
        if (strlen($key) > 4)
        {
            if (substr( $key, 0, 4 ) === "from")
            {
                $index = substr( $key,4);
                //$data .= 
                $wpdb->insert($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$_POST['from'.$index],  "means"=>$_POST['lab_transport_to'.$index],
                                                                   "travel_to"=>$_POST['to'.$index],  "country_from"=>$_POST['country_from'.$index],
                                                                   "country_to"=>$_POST['country_to'.$index], "go_back"=>$_POST['go_back'.$index],
                                                                   "travel_date"=>$_POST['travel_date'.$index],"travel_id"=>$travel_id));
            }
        }

        /*
        if (strlen($key) > 4 && substr( $key, 0, 4 ) === "from")
        {
            
            $data .= "key commence par from <br>";
            $index = intval(substr( $key,4))
            //$wpdb->insert($wpdb->prefix.'lab_labo1dot5', array("travel_from"=>$_POST['from'.$index, "means"=>$_POST['lab_transport_to'.$index, "travel_to"=>$_POST['to'.$index]));
        }
        //*/
    }
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
