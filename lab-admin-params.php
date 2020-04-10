<?php
class AdminParams {
    public const PARAMS_ID = 1;
    public const PARAMS_GROUPTYPE_ID = 2;
    public const PARAMS_KEYTYPE_ID = 3;
    public const PARAMS_SITE_ID = 4;
    public function get_params_fromId($id) {
        $sql = "SELECT value,id FROM `wp_lab_params` WHERE type_param=".$id.";";
        global $wpdb;
        return $results = $wpdb->get_results($sql);
    }
    public function get_param($id) {
        $sql = "SELECT value FROM `wp_lab_params` WHERE id=".$id.";";
        global $wpdb;
        $results = $wpdb->get_results($sql);
        return $results[0]->value;
    }
    /* Inutiles, préférer utiliser get_params_fromId(CONSTANTE)
    public function lab_admin_get_params_Types() {
        return $this->lab_admin_get_params_fromId($this->LAB_ADMIN_PARAMS_ID);
    }
    public function lab_admin_get_params_groupTypes() {
        global $LAB_ADMIN_PARAMS_GROUPTYPE_ID;
        return $this->lab_admin_get_params_fromId($this->LAB_ADMIN_PARAMS_GROUPTYPE_ID);
    }*/
}

?>