<?php

use AdminParams as AdminParams;

function lab_get_thematic()
{
    // pour tester
    // curl -X POST http://www.i2m.univ-amu.fr/wp-admin/admin-ajax.php?action=lab_thematics -H "Content-Type: application/json"
    $thematics = lab_admin_thematic_load_all();
    global $wpdb;
    //$results = $wpdb->get_results($sql);
    $assoc_array = array();
    foreach ($thematics as $thematic) {
        $thematic_id = $thematic->id;
        $label = $thematic->value;
        $sql = "SELECT ut.*, umfn.meta_value as firstName, umln.meta_value as lastName, umsn.meta_value as slug FROM `" . $wpdb->prefix . "lab_users_thematic` AS ut  
        LEFT JOIN " . $wpdb->prefix . "usermeta as umfn ON ut.user_id=umfn.user_id 
        LEFT JOIN " . $wpdb->prefix . "usermeta as umln ON ut.user_id=umln.user_id  
        LEFT JOIN " . $wpdb->prefix . "usermeta as umsn ON ut.user_id=umsn.user_id 
        WHERE umln.meta_key='last_name' AND umfn.meta_key='first_name' AND umsn.meta_key='lab_user_slug' AND ut.thematic_id=" . $thematic_id;
        $results = $wpdb->get_results($sql);
        foreach ($results as $r) {
            $assoc_array[$label][] = array("firstname" => $r->firstName, "lastname" => strtoupper($r->lastName), "id" => $r->user_id);
        }
    }
    return $assoc_array;
}

function lab_get_thematic_csv()
{
    $thematics = lab_get_thematic();
    $retour = "";
    foreach ($thematics as $thematic => $users_list) {
        foreach ($users_list as $user) {
            $retour .= $thematic . " ; " . $user["firstname"] . " ; " . $user["lastname"] . " ; " . $user["id"] . "\n";
        }
        //$retour .= $thematic . " ; " . implode(" ; ", $users_list) . "\n";
    }
    return $retour;
}

function lab_shortcode_thematic_display()
{
    $thematics = lab_admin_thematic_load_all();
    global $wpdb;
    $sql = "SELECT DISTINCT thematic_id FROM `" . $wpdb->prefix . "lab_users_thematic`";
    $results = $wpdb->get_results($sql);
    $html = "<div class='lab_thematics' id='lab_thematics'>";

    $html .= "<div class='lab_thematics_list'>";
    foreach ($thematics as $thematic) {
        $html .= "<div class='lab_thematics_list_item' theme_id='" . $thematic->id . "'>";
        $html .= "<a class='lab_thematics_list_item_link' theme_id='" . $thematic->id . "'>";
        $html .= $thematic->value;
        $html .= "</a></div>";
    }
    $html .= "</div>";

    $html .= "<div class='lab_thematics_rows'>";
    foreach ($thematics as $thematic) {
        $html .= "<div class='lab_thematics_row' theme_id='" . $thematic->id . "'>";
        $thematic_id = $thematic->id;
        $label = $thematic->value;
        $sql = "SELECT ut.*, umfn.meta_value as firstName, umln.meta_value as lastName, umsn.meta_value as slug FROM `" . $wpdb->prefix . "lab_users_thematic` AS ut  
        LEFT JOIN " . $wpdb->prefix . "usermeta as umfn ON ut.user_id=umfn.user_id 
        LEFT JOIN " . $wpdb->prefix . "usermeta as umln ON ut.user_id=umln.user_id  
        LEFT JOIN " . $wpdb->prefix . "usermeta as umsn ON ut.user_id=umsn.user_id 
        WHERE umln.meta_key='last_name' AND umfn.meta_key='first_name' AND umsn.meta_key='lab_user_slug' AND ut.thematic_id=" . $thematic_id;
        $results = $wpdb->get_results($sql);
        $html .= "<div class='lab_thematics_row_thematic' id='lab_thematics_row_thematic_" . $thematic->id . "'>";
        $html .= $label;
        $html .= "</div>"; // fin lab_thematics_row_thematic
        $html .= "<div class='lab_thematics_row_users' id='lab_thematics_row_users_" . $thematic->id . "' theme_id='" . $thematic->id . "'>";
        foreach ($results as $r) {
            $html .= "<div class='lab_thematics_row_user lab_clickable_user' user_id='" . esc_html($r->slug) . "'>";
            $html .= "<div class='lab_thematics_row_user_firstname'>";
            $html .= $r->firstName;
            $html .= "</div>"; // fin lab_thematics_row_user_firstname
            $html .= "<div class='lab_thematics_row_user_firstname'>";
            $html .= strtoupper($r->lastName);
            $html .= "</div>"; // fin lab_thematics_row_user_firstname
            $html .= "<div class='lab_thematics_row_user_main'>";
            $html .= $r->main == 1 ? "P" : "S";
            $html .= "</div>"; // fin lab_thematics_row_user
            $html .= "</div>"; // fin lab_thematics_row_user
        }
        $html .= "</div>"; // fin lab_thematics_row_users
        $html .= "</div>"; // fin lab_thematics_row
    }
    $html .= "</div>"; // fin lab_thematics_rows
    $html .= "</div>"; // fin lab_thematics
    return $html;
}

/*****************************************************************************************************************
 * THEMATIC
 *****************************************************************************************************************/
function lab_admin_thematic_add($userId, $thematicId)
{
    if (lab_admin_param_get_by_id($thematicId) == null) {
        return ["success" => false, "data" => sprintf(__("Thematic id doesn't exist '%d'", "lab"), $thematicId)];
    }
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'lab_users_thematic', array("user_id" => $userId, "thematic_id" => $thematicId));
    return ["success" => true, "data" => sprintf(__("Thematic id : '%d' add to user %d", "lab"), $thematicId, $userId)];
}

/**
 * Return thematic Label, in reality param label
 *
 * @param [type] $thematicId
 * @return void
 */
function lab_admin_thematic_get_label($thematicId)
{
    return lab_admin_param_get_by_id($thematicId)->value;
}
function lab_admin_thematic_delete($thematicId)
{
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'lab_users_thematic', array('id' => $thematicId));
    return true;
}

function lab_admin_thematic_set_main($thematicId, $value)
{
    global $wpdb;
    $toggleValue = 0;
    if ($value === "0") {
        $toggleValue = 1;
    }

    $wpdb->update($wpdb->prefix . 'lab_users_thematic', array('main' => $toggleValue), array('id' => $thematicId));
    return "\$toggleValue : " . $toggleValue . " thematicId : " . $thematicId;
}

function lab_admin_thematic_add_to_user($userId, $thematicId)
{
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'lab_users_thematic',
        array(
            'user_id' => $userId,
            'thematic_id' => $thematicId,
        )
    );
    return true;
}

function lab_admin_thematic_load_all()
{
    return AdminParams::lab_admin_get_params_thematics();
}

function lab_admin_thematic_load_all_cut()
{
    $initial_result = AdminParams::lab_admin_get_params_thematics();
    $copy = array();
    $max_size = 10;
    foreach ($results as $r) {
        $a_array = new stdClass();

        $a_array->id = $r->id;
        $a_array->value = $r->value;
        $a_array->slug = $r->slug;
        /*
	foreach($r as $k=>$v) {
		if ($k != "slug") {
			$a_array[$k]=$v;
		}
		else {
			if(strlen($v) > $max_size) {
				$a_array[$k]=substr($v,0,$max_size);
			}
			else {
				$a_array[$k]=$v;
			}
		}
	}
//*/
        $copy[] = $a_array;
    }
    return $copy;
}

function lab_admin_thematic_get_thematics_by_user($userId)
{
    global $wpdb;
    $sql = "SELECT * FROM `" . $wpdb->prefix . "lab_users_thematic` WHERE `user_id`=$userId";
    $results = $wpdb->get_results($sql);
    //return $results;
    $thematics = array();
    if (count($results) > 0) {
        foreach ($results as $r) {
            $thematic = new \stdClass();
            $thematic->id = $r->id;
            $thematic->thematic_id = $r->thematic_id;
            $thematic->main = $r->main;
            $thematic->name = lab_admin_thematic_get_label($r->thematic_id);
            $thematics[] = $thematic;
        }
    }
    /*
    else
    {
        return null;
    }
    //*/
    return $thematics;
}
