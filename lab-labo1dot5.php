<?php

//Les functions pour la page utilisateur
function lab_labo1dot5_initial(){
    global $wpdb;
    $user_id = get_current_user_id();

    $sql = "SELECT meta_value FROM `".$wpdb->prefix."usermeta` AS lb WHERE lb.`meta_key`='first_name' AND lb.`user_id`= $user_id 
            UNION 
            SELECT meta_value FROM `".$wpdb->prefix."usermeta` AS lb WHERE lb.`meta_key`='last_name' AND lb.`user_id`= $user_id
            UNION 
            SELECT user_email FROM `".$wpdb->prefix."users` AS lb WHERE lb.`ID` = $user_id
            UNION 
            SELECT group_name FROM `".$wpdb->prefix."lab_groups` AS gr JOIN `".$wpdb->prefix."lab_users_groups` AS ug ON gr.`id`=ug.`group_id` WHERE ug.`user_id`= $user_id";
    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results ); 
}
function lab_labo1dot5_getContrat(){
    global $wpdb;
    
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_contract` AS ct ";
    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results ); 
}
function lab_labo1dot5_save_mission(){
    $data = "";
    global $wpdb;

    date_default_timezone_set('Europe/Paris');
    $date_submit = date('Y-m-d H:m:s',time());
    $user_id = get_current_user_id();
    $sql = "SELECT max(mission_id) AS maxMissionId FROM `".$wpdb->prefix."lab_labo1dot5_mission`";

    $mission_id = $wpdb->get_results($sql);

    if(!isset($mission_id) || $mission_id == NULL)
    {
        $mission_id = 0;
    }
    else {
        $mission_id = $mission_id[0]->maxMissionId;
    }
    $mission_id += 1;

    $wpdb->insert($wpdb->prefix.'lab_labo1dot5_mission',array("mission_id"=>$mission_id,"user_id"=>$user_id,
                                                              "user_name"=>$_POST["user_name"],"mission_motif"=>$_POST["mission_motif"],
                                                              "mission_cost"=>$_POST["mission_cost"],
                                                              "cost_cover"=>$_POST["cost_cover"],
                                                              "mission_credit"=>$_POST["mission_credit"],
                                                              "mission_contract"=>$_POST["mission_contract"],
                                                              "cost_estimate"=>$_POST["cost_estimate"],
                                                              "mission_card"=>$_POST["mission_card"],
                                                              "mission_comment"=>$_POST["mission_comment"],
                                                              "statut"=>"0","closed"=>"0","mission_tutelle"=>"","date_submit"=>$date_submit));
    $travel_id = 0;

    foreach ($_POST as $key => $value) {
        if (strlen($key) > 4)
        {
            if (substr( $key, 0, 4 ) === "from")
           {
               $index = substr( $key,4);
                $travel_id +=1;
                $wpdb->insert($wpdb->prefix.'lab_labo1dot5_trajet', array("mission_id"=>$mission_id, "travel_id"=>$travel_id, "country_from"=>$_POST['country_from'.$index],
                                                                          "travel_from"=>$_POST['from'.$index], "country_to"=>$_POST['country_to'.$index], "travel_to"=>$_POST['to'.$index],
                                                                          "travel_date"=>$_POST['travel_date'.$index],"means"=>$_POST['means'.$index],"go_back"=>$_POST['go_back'.$index],
                                                                          "nb_person"=>$_POST['nb_person'.$index],"travel_datereturn"=>$_POST['travel_datereturn'.$index]
                                                            ));
            }
        }
    }
    wp_send_json_success( $data ); 
   
}

//Les functions pour la page admin
function lab_labo1dot5_getRowNum(){
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_labo1dot5_mission`";
     
    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results );
}

function lab_labo1dot5_getMissionYear(){
    global $wpdb;
    $sql = "SELECT DISTINCT YEAR(date_submit) AS MY FROM `".$wpdb->prefix."lab_labo1dot5_mission`";
    $results = $wpdb->get_results($sql);
    wp_send_json_success( $results );
}

function lab_labo1dot5_get_mission(){
    global $wpdb;
    $limitM = $_POST["limitM"];
    $limitN = $_POST["limitN"];
    $userId = $_POST["user_id"];
    $orderBy = $_POST["orderBy"];
    $missionYear = $_POST["missionYear"];
    $groupBy = $_POST["groupBy"];
    //$closed = $_POST["closed"];


    $sql = "SELECT mission.*, lg.`id` , lg.`group_name`
            FROM (`".$wpdb->prefix."lab_labo1dot5_mission` AS mission JOIN `".$wpdb->prefix."lab_users_groups` AS ug ON mission.`user_id` = ug.`user_id`)
            JOIN `".$wpdb->prefix."lab_groups` AS lg ON ug.`group_id` = lg.`id`";

    if ($userId != "")
    {
        $sql .= " WHERE mission.`user_id` = $userId"; 
    };

    if ($groupBy != "")
    {
        $sql .= " WHERE lg.`id` = $groupBy"; 
    };

    /*if ($closed != "")
    {
        $sql .= " WHERE mission.`closed` = $closed"; 
    };*/

    if ($missionYear != "")
    {
        $sql .= " WHERE YEAR(date_submit) = $missionYear"; 
    };

    if ($orderBy != "")
    {
        $sql .= " ORDER BY $orderBy";
    };
    $sql .= " LIMIT $limitM, $limitN";

    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results ); 
}

function lab_labo1dot5_get_trajet(){
    global $wpdb;
    $mission_id = $_POST["mission_id"];

    $sql = "SELECT *
            FROM `".$wpdb->prefix."lab_labo1dot5_trajet` WHERE mission_id=$mission_id";
    
    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results ); 

}

function lab_labo1dot5_getRowNum_ajax(){
    wp_send_json_success( lab_labo1dot5_getRowNum() ); 
}

function lab_labo1dot5_admin_modify_mission(){

    $data = "";
    global $wpdb;

    $wpdb->update($wpdb->prefix.'lab_labo1dot5_mission',array("mission_motif"=>$_POST["mission_motif"],
                                                              "mission_cost"=>$_POST["mission_cost"],"cost_cover"=>$_POST["cost_cover"],"cost_estimate"=>$_POST["cost_estimate"],
                                                              "mission_credit"=>$_POST["mission_credit"],
                                                              "mission_contract"=>$_POST["mission_contract"],
                                                              "mission_comment"=>$_POST["mission_comment"],"mission_cost_max"=>$_POST["mission_cost_max"],
                                                              "mission_card"=>$_POST["mission_card"],
                                                              "mission_tutelle"=>$_POST["mission_tutelle"],
                                                              "statut"=>$_POST["mission_statut"],"closed"=>$_POST["mission_closed"]),
                                                        array("mission_id"=>$_POST["mission_id"]));
    wp_send_json_success($data);                                                 
}
function lab_labo1dot5_admin_del_travel(){

    $data = "";
    global $wpdb;

    $travel_id = $_POST["travel_id"];
    $mission_id = $_POST["mission_id"];

    $sql = "DELETE FROM `".$wpdb->prefix."lab_labo1dot5_trajet` WHERE travel_id = $travel_id AND mission_id = $mission_id" ;
     
    $results = $wpdb->get_results($sql);  
    wp_send_json_success( $results ); 
}

function lab_labo1dot5_admin_del_mission(){

    $data = "";
    global $wpdb;

    $mission_id = $_POST["mission_id"];

    $sql = "DELETE FROM `".$wpdb->prefix."lab_labo1dot5_trajet` WHERE mission_id = $mission_id" ;
    $sql2 = "DELETE FROM `".$wpdb->prefix."lab_labo1dot5_mission` WHERE mission_id = $mission_id" ;
    $results = $wpdb->get_results($sql);  
    $results2 = $wpdb->get_results($sql2);  
    wp_send_json_success( $results ); 
    wp_send_json_success( $results2 ); 
}

function lab_labo1dot5_admin_modify_travel(){

    $data = "";
    global $wpdb;

    $wpdb->update($wpdb->prefix.'lab_labo1dot5_trajet', array("travel_from"=>$_POST['travel_from'],  "means"=>$_POST['means'],
                                                       "travel_to"  =>$_POST['travel_to']   , "country_from"=>$_POST['country_from'],
                                                       "country_to" =>$_POST['country_to'],   "go_back"     =>$_POST['go_back'],
                                                       "travel_date"=>$_POST['travel_date'],"travel_datereturn"=>$_POST['travel_datereturn'],"nb_person"=>$_POST['nb_person']),
                                                 array("mission_id"=>$_POST['mission_id'],"travel_id"=>$_POST['travel_id'])); 
    
    wp_send_json_success($data); 
}

function lab_labo1dot5_admin_add_New_travel(){

    $data = "";
    global $wpdb;
    $mission_id = $_POST['mission_id'];
    $sql = "SELECT max(travel_id) AS maxTravelId FROM `".$wpdb->prefix."lab_labo1dot5_trajet` WHERE mission_id = $mission_id";

    $travel_id = $wpdb->get_results($sql);

    if(!isset($travel_id) || $travel_id == NULL)
    {
        $travel_id = 0;
    }
    else {
        $travel_id = $travel_id[0]->maxTravelId;
    }
    $travel_id += 1;

    $wpdb->insert($wpdb->prefix.'lab_labo1dot5_trajet', array("travel_from"=>$_POST['travel_from'],  "means"=>$_POST['means'],
                                                       "travel_to"  =>$_POST['travel_to']   , "country_from"=>$_POST['country_from'],
                                                       "country_to" =>$_POST['country_to'],   "go_back"     =>$_POST['go_back'],
                                                       "travel_date"=>$_POST['travel_date'],"travel_datereturn"=>$_POST['travel_datereturn'],"nb_person"=>$_POST['nb_person'],
                                                       "mission_id"=>$mission_id,"travel_id"=>$travel_id)); 
    
    wp_send_json_success($data); 
}

/*function sendMail($to,$title,$content){
    require_once("phpmailer/class.phpmailer.php"); 
    require_once("phpmailer/class.smtp.php");

    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPAuth=true;

    $mail->Host = 'smtp.gmail.com';
    echo "<script>alert('发送邮件成功！')</script>";
    /*$mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->CharSet = 'UTF-8';
    $mail->FromName = 'Testlabo';
    $mail->Username ='m471884624@gmail.com';
    $mail->Password = 'Md94625.+';
    $mail->From = 'm471884624@gmail.com';
    $mail->isHTML(true);
    $mail->addAddress($to,'utilisatuer du labo');
    $mail->Subject = $title;
    $mail->Body = $content;
    $status = $mail->send();

    if($status) {
        return true;
    }else{
        return false;
    }

}

$flage = sendMail ('m471884624@gmail.com','test','test');
if($flag){
    echo "<script>alert('发送邮件成功！')</script>";
}else{
    echo "<script>alert('发送邮件失败！')</script>";
}*/