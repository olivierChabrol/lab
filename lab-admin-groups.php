<?php
/**
 * return a select on group
 * @param 
 * @return array([id:0,value:"groupval"])
 */
function lab_admin_group_select_group($labelField) {
    $sql = "SELECT id,".$labelField." as value FROM `wp_lab_groups`";
    //return array(id=>$labelField, value=>$sql);
    global $wpdb;
    return $wpdb->get_results($sql);
}