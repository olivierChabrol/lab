<?php

function list_internship_years() {
    global $wpdb;
    $sql = "SELECT DISTINCT YEAR(uh.end) AS y FROM `wp_lab_params` as p JOIN wp_lab_users_historic AS uh ON uh.function=p.id WHERE p.slug = 'STG'";
    $beginYears = $wpdb->get_results($sql);
    $years = array();
    $currentYear = date('Y');
    $isCurrentYearInside = FALSE;
    foreach($beginYears as $y) {
        $years[] = $y->y;
        if ($y->y == $currentYear) {
            $isCurrentYearInside = TRUE;
        }
    }
    if (!$isCurrentYearInside) {
        $years[] =  $currentYear;
    }
    sort($years);
    return $years;
}

function get_nb_open_days($date_start, $date_stop) {	
	$arr_bank_holidays = array(); // Tableau des jours feriés	
	
	// On boucle dans le cas où l'année de départ serait différente de l'année d'arrivée
	$diff_year = date('Y', $date_stop) - date('Y', $date_start);
	for ($i = 0; $i <= $diff_year; $i++) {			
		$year = (int)date('Y', $date_start) + $i;
		// Liste des jours feriés
		$arr_bank_holidays[] = '1_1_'.$year; // Jour de l'an
		$arr_bank_holidays[] = '1_5_'.$year; // Fete du travail
		$arr_bank_holidays[] = '8_5_'.$year; // Victoire 1945
		$arr_bank_holidays[] = '14_7_'.$year; // Fete nationale
		$arr_bank_holidays[] = '15_8_'.$year; // Assomption
		$arr_bank_holidays[] = '1_11_'.$year; // Toussaint
		$arr_bank_holidays[] = '11_11_'.$year; // Armistice 1918
		$arr_bank_holidays[] = '25_12_'.$year; // Noel
				
		// Récupération de paques. Permet ensuite d'obtenir le jour de l'ascension et celui de la pentecote	
		$easter = easter_date($year);
		$arr_bank_holidays[] = date('j_n_'.$year, $easter + 86400); // Paques
		$arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*39)); // Ascension
		$arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*50)); // Pentecote	
	}
	//print_r($arr_bank_holidays);
	$nb_days_open = 0;
	// Mettre <= si on souhaite prendre en compte le dernier jour dans le décompte	
	while ($date_start < $date_stop) {
		// Si le jour suivant n'est ni un dimanche (0) ou un samedi (6), ni un jour férié, on incrémente les jours ouvrés	
		if (!in_array(date('w', $date_start), array(0, 6)) 
		&& !in_array(date('j_n_'.date('Y', $date_start), $date_start), $arr_bank_holidays)) {
			$nb_days_open++;		
		}
		$date_start = mktime(date('H', $date_start), date('i', $date_start), date('s', $date_start), date('m', $date_start), date('d', $date_start) + 1, date('Y', $date_start));			
	}		
	return $nb_days_open;
}

function lab_internship_delete($histo_id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_users_historic", array("id"=>$histo_id));
    internship_financial_delete_by_histo($histo_id);
    return TRUE;
}

function lab_internship_get($histo_id) {
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."lab_users_historic WHERE id=".$histo_id;
    $histo = $wpdb->get_results($sql);

    $data["id"] = $histo_id;
    $id = $histo[0]->user_id;
    $data["user_id"] = $id;
    $sql = "SELECT user_email FROM ".$wpdb->prefix."users WHERE ID=".$id;
    $res = $wpdb->get_results($sql);
    $data["email"] = $res[0]->user_email;
    $names = lab_admin_usermeta_names($id);
    $data["firstname"] = $names->first_name;
    $data["lastname"]  = $names->last_name;
    $data["training"]  = $histo[0]->training;
    $data["establishment"]  = $histo[0]->establishment;
    $data["begin"]          = $histo[0]->begin;
    $data["end"]            = $histo[0]->end;
    $data["host"]           = lab_admin_usermeta_names($histo[0]->host_id);
    $data["host_id"]        = $histo[0]->host_id;
    $data["convention_state"] = $histo[0]->convention_state;
    $data["financials"] = internship_financial_list($histo_id);
    return $data;
}

function save_intern($data) {
    global $wpdb;

    $financials = $data["financials"];
    if (isset($data["id"]) && !empty($data["id"])) {
        $histo = array();
        $histo["user_id"] = $data["user_id"];
        $histo["begin"] = $data["begin"];
        $histo["end"] = $data["end"];
        $histo["host_id"] = $data["host_id"];
        $histo["training"] = $data["training"];
        $histo["establishment"] = $data["establishment"];
        $histo["convention_state"] = $data["convention_state"];

        $i = 1;
        foreach($financials as $financial) {
            $financial["histo_id"] = $data["id"];
            $financial["cardinality"] = $i++;
            internship_financial_save($financial);
        }
        
        $wpdb->update($wpdb->prefix.'lab_users_historic', $histo, array('id' => $data["id"]));
        $wpdb->update($wpdb->prefix.'users', array('user_email'=>$data["email"]), array("ID"=>$data["user_id"]));
        update_user_meta($data["user_id"], "last_name", $data["lastname"]);
        update_user_meta($data["user_id"], "first_name", $data["firstname"]);
        return $data["id"];
    }
    else {
        if(isset($data["user_id"]))
        {
            $user_id = $data["user_id"];
        }
        else {
            $user_id = addNewUser(generateLogin($data["lastname"], $data["firstname"]),null,$data["email"],$data["firstname"],$data["lastname"]);
        }
        $histo = array();
        $histo["user_id"] = $user_id;
        $histo["ext"] = 0;
        $histo["begin"] = $data["begin"];
        $histo["end"] = $data["end"];
        $histo["mobility"] = 0;
        $histo["mobility_status"] = 0;
        $histo["host_id"] = $data["host_id"];
        $histo["training"] = $data["training"];
        $histo["establishment"] = $data["establishment"];
        $histo["convention_state"] = $data["convention_state"];
        $fctStgs = AdminParams::get_param_by_slug("STG");
        $fctStg = 0;
        if (count($fctStgs) > 1) {
            $fctStg = $fctStgs[0]->id;
        }
        else {
            $fctStg = $fctStgs->id;
        }
        $histo["function"] = $fctStg;
        $wpdb->insert($wpdb->prefix.'lab_users_historic', $histo);
        $host_id = $wpdb->insert_id;
        $i = 1;
        foreach($financials as $financial) {
            $financial["histo_id"] = $host_id;
            $financial["cardinality"] = $i++;
            internship_financial_save($financial);
        }
        return $user_id;
    }
}
function internship_financial_save($data) {
    global $wpdb;
    
    if(!isset($data["id"]) || empty($data["id"])) {
        $wpdb->insert($wpdb->prefix."lab_internship_financial", $data);
        return $wpdb->insert_id;
    } 
    else {
        $wpdb->update($wpdb->prefix."lab_internship_financial", $data, array("id"=>$data["id"]));
        return $data["id"];
    }
}

function internship_financial_save_old($id, $histo_id, $cardinality, $team_id, $tutelage_id, $months, $amount) {
    global $wpdb;
    if(isset($id) && !empty($id)) {
        $wpdb->update($wpdb->prefix."lab_internship_financial", array("histo_id"=>$histo_id, "cardinality"=>$cardinality, "team_id"=>$team_id, "tutelage_id"=>$tutelage_id, "nb_month"=>$months, "amount"=>$amount));
        return $wpdb->insert_id;
    } 
    else {
        $wpdb->insert($wpdb->prefix."lab_internship_financial", array("histo_id"=>$histo_id, "cardinality"=>$cardinality, "team_id"=>$team_id, "tutelage_id"=>$tutelage_id, "nb_month"=>$months, "amount"=>$amount), array("id"=>$id));
        return $id;
    }
}

function internship_financial_list($histo_id) {
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."lab_internship_financial WHERE histo_id=".$histo_id;
    $histo_financials = $wpdb->get_results($sql);
    foreach($histo_financials as $hf) {
        $hf->financials = internship_financial_list($hf->id);
    }
    return $histo_financials;
}

function internship_financial_delete_by_histo($histo_id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_internship_financial", array("histo_id"=>$histo_id));
}

function internship_financial_delete($id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_internship_financial", array("id"=>$id));
}

function generateLogin($lastname, $firstname) {
    $baseLogin = strtoupper($lastname).".".substr($firstname,-1,1);
    $login = $baseLogin."";
    global $wpdb;
    $sql = "SELECT user_login FROM ".$wpdb->prefix."users WHERE user_login = '".$login."'";
    $res = $wpdb->get_results($sql);
    $i=0;
    while(count($res) > 0) {
        $i += 1;
        $login = $baseLogin.".".$i;
        $sql = "SELECT user_login FROM ".$wpdb->prefix."users WHERE user_login = '".$login."'";
        $res = $wpdb->get_results($sql);
    }
    return $login;
}

function addNewUser($login, $password, $email, $firstname, $lastname) {
    $passwd = "definedIt";
    if (isset($passwd)) {
        $passwd = substr($password,0,7)=='{CRYPT}' ? 'hahaha' : substr($password,0,7);
    }
    $userData = array(
        'user_login'=>$login,
        'user_pass'=>$passwd,
        'user_email'=>$email,
        'user_registered'=>date("Y-m-d H:i:s",time()),
        'first_name'=>$firstname,
        'last_name'=>$lastname,
        'display_name'=>$firstname." ".$lastname,
        'role'=>'subscriber');
      $user_id = wp_insert_user($userData);
      lab_admin_add_new_user_metadata($user_id);
      return $user_id;
}

function list_intern($year)
{
    global $wpdb;
    $sql = "SELECT uh.* FROM `wp_lab_params` as p JOIN wp_lab_users_historic AS uh ON uh.function=p.id WHERE p.slug = 'STG' AND ((YEAR(uh.end) = ".$year." OR YEAR(uh.begin) = ".$year."))";
    $historics = $wpdb->get_results($sql);
    

    $data    = array();
    $data["results"] = $historics;
    $data["sql"]     = $sql;
    $userIds = array();
    foreach($historics as $historic) {
        if(!isset($userIds[$historic->user_id]) && $historic->user_id != 0) {
            $userIds[$historic->user_id] = lab_admin_usermeta_names($historic->user_id);
        }
        if(!isset($userIds[$historic->host_id]) && $historic->host_id != 0) {
            $userIds[$historic->host_id] = lab_admin_usermeta_names($historic->host_id);
        }
        $historic->financials = internship_financial_list($historic->id);
    }
    $data["users"] = $userIds;
    $data["teams"] = array();
    $teams = lab_admin_group_load();
    foreach($teams as $team) {
        $data["teams"][$team->id] = $team->acronym;
    }
    $data["financers"] = array();
    $budgets = AdminParams::lab_admin_get_params_budgetFunds();
    foreach($budgets as $budget) {
        $data["tutelage"][$budget->id] = $budget->value;
    }

    return $data;
}

?>