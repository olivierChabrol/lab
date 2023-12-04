<?php
use AdminParams as AdminParams;

function lab_get_thematic()
{
    $thematics = lab_admin_thematic_load_all();
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $assoc_array = array();
    foreach($thematics as $thematic) {
        $thematic_id = $thematic->id;
        $label = $thematic->value;
        $sql = "SELECT ut.*, umfn.meta_value as firstName, umln.meta_value as lastName, umsn.meta_value as slug FROM `".$wpdb->prefix."lab_users_thematic` AS ut  
        LEFT JOIN ".$wpdb->prefix."usermeta as umfn ON ut.user_id=umfn.user_id 
        LEFT JOIN ".$wpdb->prefix."usermeta as umln ON ut.user_id=umln.user_id  
        LEFT JOIN ".$wpdb->prefix."usermeta as umsn ON ut.user_id=umsn.user_id 
        WHERE umln.meta_key='last_name' AND umfn.meta_key='first_name' AND umsn.meta_key='lab_user_slug' AND ut.thematic_id=".$thematic_id;
        $results = $wpdb->get_results($sql);
        foreach($results as $r) 
        {
            $assoc_array[$label][] = array("firstname"=>$r->firstName, "lastname"=>strtoupper($r->lastName), "id"=>$r->user_id );
        }
    }
    return $assoc_array;
}

function lab_shortcode_thematic_display()
{
    $thematics = lab_admin_thematic_load_all();
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $html = "";
    foreach($thematics as $thematic) {
        $thematic_id = $thematic->id;
        $label = $thematic->value;
        $sql = "SELECT ut.*, umfn.meta_value as firstName, umln.meta_value as lastName, umsn.meta_value as slug FROM `".$wpdb->prefix."lab_users_thematic` AS ut  
        LEFT JOIN ".$wpdb->prefix."usermeta as umfn ON ut.user_id=umfn.user_id 
        LEFT JOIN ".$wpdb->prefix."usermeta as umln ON ut.user_id=umln.user_id  
        LEFT JOIN ".$wpdb->prefix."usermeta as umsn ON ut.user_id=umsn.user_id 
        WHERE umln.meta_key='last_name' AND umfn.meta_key='first_name' AND umsn.meta_key='lab_user_slug' AND ut.thematic_id=".$thematic_id;
        $results = $wpdb->get_results($sql);
        $html .= "<h3>".$label."</h3>";
        foreach($results as $r) 
        {
            $html .= "- ".$r->firstName." ".strtoupper($r->lastName);//."<br>";
            $html .= " (";
            $html .= $r->main == 1?"P":"S";
            $html .= ")";
            $html .= "<br>";
        }
    }
    return $html;
}

/*****************************************************************************************************************
 * THEMATIC
 *****************************************************************************************************************/
function lab_admin_thematic_add($userId, $thematicId)
{
    if (lab_admin_param_get_by_id($thematicId) == null)
    {
        return ["success"=>false,"data"=>sprintf(__("Thematic id doesn't exist '%d'", "lab"), $thematicId)];
    }
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'lab_users_thematic', array("user_id"=>$userId,"thematic_id"=>$thematicId));
    return ["success"=>true,"data"=>sprintf(__("Thematic id : '%d' add to user %d", "lab"), $thematicId, $userId)];
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
    $wpdb->delete($wpdb->prefix.'lab_users_thematic', array('id' => $thematicId));
    return true;
}

function lab_admin_thematic_set_main($thematicId, $value)
{
    global $wpdb;
    $toggleValue = 0;
    if ($value === "0") {
        $toggleValue = 1;
    }

    $wpdb->update($wpdb->prefix.'lab_users_thematic', array('main' => $toggleValue), array('id' => $thematicId));
    return "\$toggleValue : ".$toggleValue." thematicId : ".$thematicId;
}

function lab_admin_thematic_add_to_user($userId, $thematicId)
{
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix.'lab_users_thematic',
        array(
            'user_id' => $userId,
            'thematic_id' => $thematicId,
        ));
    return true;
}

function lab_admin_thematic_load_all()
{
    return AdminParams::lab_admin_get_params_thematics();
}

function lab_admin_thematic_get_thematics_by_user($userId)
{
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_users_thematic` WHERE `user_id`=$userId";
    $results = $wpdb->get_results($sql);
    //return $results;
    $thematics = array();
    if (count($results) > 0)
    {
        foreach($results as $r) {
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