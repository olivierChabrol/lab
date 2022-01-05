<?php
/**
 * return a select on group
 * @param 
 * @return array([id:0,value:"groupval"])
 */
function lab_admin_group_select_group($labelField) {
    global $wpdb;
    $sql = "SELECT id,".$labelField." as value FROM `".$wpdb->prefix."lab_groups`";
    
    //return array(id=>$labelField, value=>$sql);
    return $wpdb->get_results($sql);
}