<?php
/**
 * Plugin LAB
 *
 * @author : Olivier CHABROL
 * Load action for LAB plugin
 */

/**
 * Ajoute le menu à l'initialisation du menu admin
 */

add_action( 'wp_enqueue_scripts'  , 'wp_lab_fe_enqueues' );

if (is_admin()) {
    add_action( 'wp_ajax_lab_user_info', 'lab_admin_ajax_user_info');

    add_action( 'wp_ajax_lab_user_delGroup', 'lab_admin_ajax_users_group_delete');
    add_action( 'wp_ajax_lab_user_setMainThematic', 'lab_admin_ajax_users_thematic_set_main');
    add_action( 'wp_ajax_lab_admin_group_by_user', 'lab_admin_ajax_group_by_user');
    add_action( 'wp_ajax_lab_user_addGroup', 'lab_admin_ajax_group_add');
    // Action thematic BE
    add_action( 'wp_ajax_lab_user_getThematics', 'lab_admin_thematic_get_thematics_by_user');
    add_action( 'wp_ajax_lab_user_getThematics_by_user', 'ajax_thematic_get_thematics_by_user');
    add_action( 'wp_ajax_lab_user_delThematic', 'lab_user_delThematic');
    add_action( 'wp_ajax_lab_user_addThematic', 'ajax_thematic_add');
    // Action thematic FE
    add_action( 'wp_ajax_lab_fe_thematic_add', 'ajax_thematic_fe_add');
    add_action( 'wp_ajax_lab_fe_thematic_del', 'lab_user_delThematic');
    add_action( 'wp_ajax_lab_fe_thematic_get', 'ajax_thematic_fe_get');
    add_action( 'wp_ajax_lab_fe_thematic_togle_main', 'lab_admin_ajax_users_thematic_set_main');

    add_action( 'plugins_loaded', 'myplugin_load_textdomain' );
    add_action( 'admin_menu'          , 'wp_lab_menu' );
    add_action( 'wp_ajax_search_event', 'lab_admin_search_event' );
    add_action( 'wp_ajax_search_user'      , 'lab_admin_search_user' );
    add_action( 'wp_ajax_search_username', 'lab_admin_search_username' );
    add_action( 'wp_ajax_nopriv_search_username2', 'lab_admin_search_username2' );
    add_action( 'wp_ajax_search_username2', 'lab_admin_search_username2' );
    add_action( 'wp_ajax_search_user_metadata', 'lab_admin_search_user_metadata' );
    add_action( 'wp_ajax_missing_user_metadata', 'lab_admin_check_missing_usermeta_data' );
    add_action( 'wp_ajax_correct_user_metadatas', 'lab_admin_correct_user_metadatas' );
    add_action( 'wp_ajax_update_user_metadata', 'lab_admin_update_user_metadata' );
    add_action( 'wp_ajax_update_user_metadata_db', 'lab_admin_update_user_metadata_db' );
    add_action( 'wp_ajax_search_event_category', 'lab_admin_get_event_category' );
    add_action( 'wp_ajax_save_event_category', 'lab_admin_save_event_category');
    add_action( 'wp_ajax_search_group', 'lab_admin_group_search');
    add_action( 'wp_ajax_test', 'lab_admin_test');
    add_action( 'wp_ajax_lab_admin_get_userLogin','lab_admin_get_userLogin_Req' );
    add_action( 'wp_ajax_lab_admin_loadUserHistory','lab_admin_loadUserHistory_Req' );
    add_action( 'wp_ajax_lab_historic_add','lab_historic_add' );
    add_action( 'wp_ajax_lab_historic_getEntry','lab_historic_getEntry' );
    add_action( 'wp_ajax_lab_historic_delete','lab_historic_delete' );
    add_action( 'wp_ajax_lab_historic_update','lab_historic_update' );
    add_action( 'wp_ajax_lab_user_getRoles','lab_user_getRoles' );
    add_action( 'wp_ajax_lab_user_addRole','lab_user_addRole' );
    add_action( 'wp_ajax_lab_user_delRole','lab_user_delRole' );
    //Actions pour la gestion des groupes
    add_action( 'wp_ajax_group_search_ac', 'lab_admin_group_availableAc' );
    add_action( 'wp_ajax_group_create', 'lab_admin_group_createReq' );
    add_action( 'wp_ajax_group_table', 'lab_admin_createGroupTable' );
    add_action( 'wp_ajax_user_group_table', 'lab_admin_createUserGroupTable' );
    add_action( 'wp_ajax_group_sub_table', 'lab_admin_createSubTable' );
    add_action( 'wp_ajax_group_root', 'lab_admin_group_createRoot');
    add_action( 'wp_ajax_delete_group', 'lab_admin_group_delete');
    add_action( 'wp_ajax_group_subs_add', 'lab_admin_group_subs_addReq');
    add_action( 'wp_ajax_usermeta_names', 'lab_admin_ajax_usermeta_names');
    add_action( 'wp_ajax_usermeta_dateLeft', 'lab_admin_usermeta_dateLeft');
    add_action( 'wp_ajax_usermeta_fill_user_slug', 'lab_ajax_admin_usermeta_fill_user_slug');
    add_action( 'wp_ajax_list_users_groups' , 'lab_admin_list_users_groups');
    add_action( 'wp_ajax_add_users_groups' , 'lab_admin_add_users_groups');
    add_action( 'wp_ajax_lab_group_add_manager', 'lab_admin_ajax_group_add_manager');
    add_action( 'wp_ajax_lab_group_load_managers', 'lab_admin_ajax_group_load_managers');
    add_action( 'wp_ajax_lab_group_delete_manager', 'lab_admin_ajax_group_delete_manager');
    //Actions pour presence
    add_action( 'wp_ajax_lab_presence_create_table', 'lab_admin_createTable_presence');
    add_action( 'wp_ajax_lab_presence_save', 'lab_admin_presence_save_ajax');
    add_action( 'wp_ajax_lab_presence_delete', 'lab_admin_presence_delete_ajax');
    add_action( 'wp_ajax_nopriv_lab_presence_save_ext', 'lab_admin_presence_save_ext_ajax');
    //Actions pour contract
    add_action( 'wp_ajax_lab_admin_contract_save', 'lab_admin_contract_ajax_save');
    add_action( 'wp_ajax_lab_admin_contract_search', 'lab_admin_contract_ajax_search');
    add_action( 'wp_ajax_lab_admin_contract_users_load', 'lab_admin_contract_ajax_users_load');
    add_action( "wp_ajax_lab_admin_contract_delete", "lab_admin_contract_ajax_delete");
    add_action( "wp_ajax_lab_admin_contract_create_table", "lab_admin_contract_ajax_create_table");
    add_action( "wp_ajax_lab_admin_contract_load", "lab_admin_contract_ajax_load");
    add_action( "wp_ajax_lab_admin_contract_get_managers", "lab_admin_contract_ajax_get_managers");
    add_action( "wp_ajax_lab_budget_info_save_order", "lab_budget_info_ajax_save_order");

    //Actions pour la gestion des params
    add_action( 'wp_ajax_param_create_table', 'lab_admin_param_create_table');
    add_action( 'wp_ajax_save_param', 'lab_admin_ajax_param_save');
    add_action( 'wp_ajax_load_param_type', 'lab_admin_param_load_type');
    add_action( 'wp_ajax_param_delete', 'lab_admin_param_delete');
    add_action( 'wp_ajax_param_search_value', 'lab_admin_param_search_value');
    //Actions pour la gestion des clés - KeyRing
    add_action( 'wp_ajax_keyring_table_keys', 'lab_keyring_createTable_keys' );
    add_action( 'wp_ajax_keyring_table_loans', 'lab_keyring_createTable_loans' );
    add_action( 'wp_ajax_keyring_create_key', 'lab_keyring_create_keyReq' );
    add_action( 'wp_ajax_keyring_search_word', 'lab_keyring_search_byWordReq' );
    add_action( 'wp_ajax_keyring_get_key', 'lab_keyring_findKey_Req' );
    add_action( 'wp_ajax_keyring_edit_key', 'lab_keyring_editKey_Req' );
    add_action( 'wp_ajax_keyring_delete_key', 'lab_keyring_deleteKey_Req' );
    add_action( 'wp_ajax_keyring_find_curr_loans', 'lab_keyring_search_current_loans_Req');
    add_action( 'wp_ajax_keyring_add_role', 'lab_keyring_add_role_ajax');

    add_action('wp_ajax_edit_group', 'lab_group_editGroup');
    //Action for settings
    //add_action( 'wp_ajax_add_new_metakeys', 'lab_ajax_userMetaData_complete_keys');
    add_action( 'wp_ajax_add_new_metakeys', 'lab_ajax_userMetaData_complete_keys');
    add_action( 'wp_ajax_complete_new_metakeys', 'lab_ajax_userMetaData_complete_keys');
    add_action( 'wp_ajax_list_metakeys', 'lab_ajax_userMetaData_list_keys');
    add_action( 'wp_ajax_delete_metakey', 'lab_ajax_userMetaData_delete_key');
    add_action( 'wp_ajax_not_exist_metakey', 'lab_ajax_userMeta_key_not_exist');
    add_action( 'wp_ajax_um_correct', 'lab_usermeta_correct_um_fields');
    add_action( 'wp_ajax_copy_phone', 'lab_admin_usermeta_update_phone');
    add_action( 'wp_ajax_create_social', 'lab_admin_createSocial_Req' );
    add_action( 'wp_ajax_delete_social', 'lab_admin_deleteSocial' );
    add_action( 'wp_ajax_reset_lab_db', 'lab_admin_setting_reset_tables');
    add_action( 'wp_ajax_invite_createTablePrefGroup', 'lab_invitations_createPrefGroupTable' );
    add_action( 'wp_ajax_invite_createTables', 'lab_invitations_createTables_Req' );
    add_action( 'wp_ajax_lab_historic_createTable', 'lab_historic_createTable' );
    add_action( 'wp_ajax_correct_all_user_slug', 'lab_admin_usermeta_fill_user_slug');
    //Action for hal
    add_action( 'wp_ajax_hal_create_table', 'lab_ajax_hal_create_table');
    add_action( 'wp_ajax_hal_fill_hal_name', 'lab_ajax_hal_fill_fields');
    add_action( 'wp_ajax_hal_download', 'lab_ajax_hal_download');
    add_action( 'wp_ajax_hal_empty_table', 'lab_ajax_delete_hal_table');

    add_action( 'show_user_profile', 'custom_user_profile_fields', 10, 1 );
    add_action( 'edit_user_profile', 'custom_user_profile_fields', 10, 1 );
    //Actions pour keyring - Prêts
    add_action( 'wp_ajax_keyring_create_loan', 'lab_keyring_create_loanReq' ); 
    add_action( 'wp_ajax_keyring_find_loan_byKey', 'lab_keyring_find_loan_byKey' ); 
    add_action( 'wp_ajax_keyring_edit_loan', 'lab_keyring_edit_loanReq' ); 
    add_action( 'wp_ajax_keyring_end_loan','lab_keyring_end_loanReq' );
    add_action( 'wp_ajax_keyring_find_old_loans','lab_keyring_find_oldLoansReq' );
    add_action( 'wp_ajax_keyring_find_loan_byID','lab_keyring_get_loan_Req' );
    add_action( 'wp_ajax_keyring_search_key_number','lab_keyring_search_key_number' );
    add_action( 'wp_ajax_keyring_find_key','lab_keyring_find_key' );
    add_action( 'wp_ajax_keyring_save_loans','lab_keyring_save_loans' );

    add_action( 'wp_ajax_lab_profile_edit','lab_profile_edit' );
    //Actions pour les invitations
    add_action( 'wp_ajax_lab_invitations_new','lab_invitations_new' );
    add_action( 'wp_ajax_nopriv_lab_invitations_new','lab_invitations_new' );
    add_action( 'wp_ajax_lab_invitations_edit','lab_invitations_edit' );
    add_action( 'wp_ajax_lab_invitations_complete','lab_invitations_complete' );
    add_action( 'wp_ajax_lab_invitations_validate','lab_invitations_validate' );
    add_action( 'wp_ajax_lab_invitations_assume','lab_invitations_assume' );
    add_action( 'wp_ajax_lab_invitation_newComment','lab_invitation_newComment' );
    add_action( 'wp_ajax_lab_prefGroups_add','lab_prefGroups_addReq' );
    add_action( 'wp_ajax_lab_prefGroups_remove','lab_prefGroups_removeReq' );
    add_action( 'wp_ajax_lab_prefGroups_update','lab_prefGroups_update' );
    add_action( 'wp_ajax_lab_invitations_chiefList_update','lab_invitations_chiefList_update' );
    add_action( 'wp_ajax_lab_invitations_adminList_update','lab_invitations_adminList_update' );
    add_action( 'wp_ajax_lab_invitations_hostList_update','lab_invitations_hostList_update' );
    add_action( 'wp_ajax_lab_invitations_summary','lab_invitations_summary' );
    add_action( 'wp_ajax_lab_invitations_comments','lab_invitations_comments' );
    add_action( 'wp_ajax_lab_invitations_realCost','lab_invitations_realCost' );
    add_action( 'wp_ajax_lab_invitations_add_realCost','lab_invitations_add_realCost' );
    add_action( 'wp_ajax_lab_invitations_guestInfo','lab_invitations_guestInfo' );
    add_action( 'wp_ajax_lab_invitations_pagination','lab_invitations_pagination_Req' );
    //Actions pour le LDAP
    add_action( 'wp_ajax_lab_ldap_pagination','lab_ldap_pagination_Req' );
    add_action( 'wp_ajax_lab_ldap_list_update','lab_ldap_list_update' );
    add_action( 'wp_ajax_lab_ldap_add_user','lab_ldap_add_user' );
    add_action( 'wp_ajax_lab_invitations_pagination','lab_ldap_pagination_Req' );
    add_action( 'wp_ajax_lab_ldap_amu_lookup','lab_ldap_amu_lookup' );
    add_action( 'wp_ajax_lab_ldap_user_details','lab_ldap_user_details');
    add_action( 'wp_ajax_lab_ldap_delete_user','lab_ldap_delete_userReq' );
    add_action( 'wp_ajax_lab_ldap_edit_user','lab_ldap_edit_user' );
    add_action( 'wp_ajax_lab_ldap_reconnect', 'lab_ldap_reconnect');
    add_action( 'wp_ajax_lab_admin_ldap_settings', 'lab_admin_ldap_settings');
    // Actions pour le budget
    //add_action( 'wp_ajax_lab_admin_budget_info_create_table', 'lab_admin_createTable_budget_info');
    add_action( 'wp_ajax_lab_budget_info_create_tables', 'lab_ajax_admin_createTable_budget_info');
    add_action( 'wp_ajax_lab_budget_info_load', 'lab_budget_info_ajax_load');
    add_action( 'wp_ajax_lab_budget_info_delete', 'lab_budget_info_ajax_delete');
    add_action( 'wp_ajax_lab_budget_info_set_date', 'budget_info_ajax_set_date');
    // Actions pour les mission
    add_action( 'wp_ajax_lab_mission_load', 'lab_mission_ajax_load');
    add_action( 'wp_ajax_lab_travels_load', 'lab_travels_ajax_load');
    add_action( 'wp_ajax_lab_travel_delete', 'lab_travel_ajax_delete');
    add_action( 'wp_ajax_lab_travel_save', 'lab_travel_ajax_save');
    add_action( 'wp_ajax_lab_mission_delete', 'lab_mission_ajax_delete');
    add_action( 'wp_ajax_lab_mission_set_manager', 'lab_mission_ajax_set_manager');
    //add_action( 'wp_ajax_lab_mission_getNotifs', 'lab_mission_getNotifs');
    //add_action( 'wp_ajax_lab_mission_resetNotifs', 'lab_mission_resetNotifs');
    
    // Actions for guest action on th MISSION
    add_action( 'wp_ajax_nopriv_lab_mission_load', 'lab_mission_ajax_load');
    add_action( 'wp_ajax_nopriv_lab_travels_load', 'lab_travels_ajax_load');
    add_action( 'wp_ajax_nopriv_lab_travel_delete', 'lab_travel_ajax_delete');
    add_action( 'wp_ajax_nopriv_lab_travel_save', 'lab_travel_ajax_save');
    add_action( 'wp_ajax_nopriv_lab_invitations_edit','lab_invitations_edit' );

    // LABO 1.5
    add_action( 'wp_ajax_lab_labo1.5_initial', 'lab_labo1dot5_initial');
    add_action( 'wp_ajax_lab_labo1.5_save_mission', 'lab_labo1dot5_save_mission');
    add_action( 'wp_ajax_lab_labo1.5_transportation_get_mission', 'lab_labo1dot5_get_mission');
    add_action( 'wp_ajax_lab_labo1.5_transportation_get_trajet', 'lab_labo1dot5_get_trajet');
    add_action( 'wp_ajax_lab_labo1.5_transportation_getMissionYear', 'lab_labo1dot5_getMissionYear');
    add_action( 'wp_ajax_lab_admin_modify_mission', 'lab_labo1dot5_admin_modify_mission');
    add_action( 'wp_ajax_lab_admin_modify_travel', 'lab_labo1dot5_admin_modify_travel');
    add_action( 'wp_ajax_lab_admin_add_New_travel', 'lab_labo1dot5_admin_add_New_travel');
    add_action( 'wp_ajax_lab_admin_del_travel', 'lab_labo1dot5_admin_del_travel');
    add_action( 'wp_ajax_lab_admin_del_mission', 'lab_labo1dot5_admin_del_mission');
    add_action( 'wp_ajax_lab_labo1.5_transportation_getRowNum', 'lab_labo1dot5_getRowNum_ajax');

}
// no admin
else{

    add_action( 'wp_ajax_nopriv_lab_presence_save_ext', 'lab_admin_presence_save_ext_ajax');
    add_action( 'wp_ajax_nopriv_lab_save_transportation', 'lab_labo1dot5_save');

}