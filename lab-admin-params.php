<?php
class AdminParams {
    public const PARAMS_ID = 1;
    public const PARAMS_GROUPTYPE_ID = 2;
    public const PARAMS_KEYTYPE_ID = 3;
    public const PARAMS_SITE_ID = 4;
    public const PARAMS_USER_FUNCTION_ID = 5;
    public const PARAMS_MISSION_ID = 6;
    public const PARAMS_FUNDING_ID = 7;
    public const PARAMS_EMPLOYER = 8;
    public const PARAMS_LDAP_TOKEN = 9;
    public const PARAMS_LDAP_HOST = 10;
    public const PARAMS_LDAP_BASE = 11;
    public const PARAMS_LDAP_LOGIN = 12;
    public const PARAMS_LDAP_PASSWORD = 13;
    public const PARAMS_LDAP_TLS = 14;
    public const PARAMS_LDAP_ENABLE = 15;
    public const PARAMS_USER_SECTION_CN = 16;
    public const PARAMS_USER_SECTION_CNU = 17;
    public const PARAMS_OUTGOING_MOBILITY = 18;
    public const PARAMS_KEY_STATE = 19;
    public const PARAMS_USER_ECOLE_DOCTORALE = 20;
    

    public static function get_params_fromId($id) {
        $sql = "SELECT value,id FROM `wp_lab_params` WHERE type_param=".$id." ORDER BY value;";
        global $wpdb;
        return $results = $wpdb->get_results($sql);
    }

    public static function lab_admin_get_params_groupTypes()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_GROUPTYPE_ID);
    }
    public static function lab_admin_get_params_userLocation()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_SITE_ID);
    }
    public static function lab_admin_get_params_UserFunctions()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_USER_FUNCTION_ID);
    }
    public static function lab_admin_get_params_userEmployer()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_EMPLOYER);
    }
    public static function lab_admin_get_params_userFunding()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_FUNDING_ID);
    }
    public static function lab_admin_get_params_ldap_enable()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_ENABLE);
    }
    public static function lab_admin_get_params_userSectionCn()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_USER_SECTION_CN);
    }
    public static function lab_admin_get_params_userSectionCnu()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_USER_SECTION_CNU);
    }
    public static function lab_admin_get_params_userPhdSchool()
    {
        return AdminParams::get_params_fromId(AdminParams::PARAMS_USER_ECOLE_DOCTORALE);
    }

    public static function get_param($id) {
        global $wpdb;
        $sql = "SELECT value FROM `".$wpdb->prefix."lab_params` WHERE id=".$id." ORDER BY value;";
        $results = $wpdb->get_results($sql);
        return $results[0]->value;
    }
    public static function get_paramWithColor($id) {
        global $wpdb;
        $sql = "SELECT * FROM `".$wpdb->prefix."lab_params` WHERE id=".$id." ORDER BY value;";
        $results = $wpdb->get_results($sql);
        return $results[0];
    }
    /* Inutiles, préférer utiliser get_params_fromId(CONSTANTE)
    public function lab_admin_get_params_Types() {
        return $this->lab_admin_get_params_fromId($this->LAB_ADMIN_PARAMS_ID);
    }
    public function lab_admin_get_params_groupTypes() {
        global $LAB_ADMIN_PARAMS_GROUPTYPE_ID;
        return $this->lab_admin_get_params_fromId($this->LAB_ADMIN_PARAMS_GROUPTYPE_ID);
    }*/
};

function lab_admin_get_params_groupTypes() {
    return AdminParams::lab_admin_get_params_groupTypes();
}

function lab_admin_get_params_userFunction() {
    return AdminParams::lab_admin_get_params_UserFunctions();
}
function lab_admin_get_params_userLocation() {
    return AdminParams::lab_admin_get_params_userLocation();
}
function lab_admin_get_params_userEmployer() {
    return AdminParams::lab_admin_get_params_userEmployer();
}

function lab_admin_get_params_userFunding() {
    return AdminParams::lab_admin_get_params_userFunding();
}

function lab_admin_get_params_userSectionCn() {
    return AdminParams::lab_admin_get_params_userSectionCn();
}

function lab_admin_get_params_userSectionCnu() {
    return AdminParams::lab_admin_get_params_userSectionCnu();
}

function lab_admin_param_is_ldap_enable() {
    return AdminParams::lab_admin_get_params_ldap_enable() == 'true';
}

function lab_admin_get_params_outgoingMobility() {
    return AdminParams::get_params_fromId(AdminParams::PARAMS_OUTGOING_MOBILITY);
}
function lab_admin_get_params_userPhdSchool() {
    return AdminParams::lab_admin_get_params_userPhdSchool();
}
?>