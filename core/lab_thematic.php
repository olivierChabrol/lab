<?php
use AdminParams as AdminParams;

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
            $thematic->name = lab_admin_thematic_get_label($r->thematic_id);
            $thematics[] = $thematic;
        }
    }
    else
    {
        return null;
    }
    return $thematics;
}