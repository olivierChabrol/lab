<?php
$LAB_ADMIN_PARAMS_ID = 1;
$LAB_ADMIN_PARAMS_GROUPTYPE_ID = 2;
function lab_admin_get_params_fromId($id) {
    $sql = "SELECT value,id FROM `wp_lab_params` WHERE type_param=".$id.";";
    global $wpdb;
    return $results = $wpdb->get_results($sql);
}
function lab_admin_get_params_Types() {
    global $LAB_ADMIN_PARAMS_ID;
    return lab_admin_get_params_fromId($LAB_ADMIN_PARAMS_ID);
}
function lab_admin_get_params_groupTypes() {
    global $LAB_ADMIN_PARAMS_GROUPTYPE_ID;
    return lab_admin_get_params_fromId($LAB_ADMIN_PARAMS_GROUPTYPE_ID);
}

?>