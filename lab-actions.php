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
    add_action( 'plugins_loaded', 'myplugin_load_textdomain' );
    add_action( 'admin_menu'          , 'wp_lab_menu' );
    add_action( 'wp_ajax_search_event', 'lab_admin_search_event' );
    add_action( 'wp_ajax_search_user'      , 'lab_admin_search_user' );
    add_action( 'wp_ajax_search_username', 'lab_admin_search_username' );
    add_action( 'wp_ajax_nopriv_search_username2', 'lab_admin_search_username2' );
    add_action( 'wp_ajax_search_user_metadata', 'lab_admin_search_user_metadata' );
    add_action( 'wp_ajax_update_user_metadata', 'lab_admin_update_user_metadata' );
    add_action( 'wp_ajax_update_user_metadata_db', 'lab_admin_update_user_metadata_db' );
    add_action( 'wp_ajax_search_event_category', 'lab_admin_get_event_category' );
    add_action( 'wp_ajax_save_event_category', 'lab_admin_save_event_category');
    add_action( 'wp_ajax_search_group', 'lab_admin_group_search');
    add_action( 'wp_ajax_test', 'lab_admin_test');
    //Actions pour la gestion des groupes
    add_action( 'wp_ajax_group_search_ac', 'lab_admin_group_availableAc' );
    add_action( 'wp_ajax_group_create', 'lab_admin_group_createReq' );
    add_action( 'wp_ajax_group_table', 'lab_admin_createGroupTable' );
    add_action( 'wp_ajax_user_group_table', 'lab_admin_createUserGroupTable' );
    add_action( 'wp_ajax_group_sub_table', 'lab_admin_createSubTable' );
    add_action( 'wp_ajax_group_root', 'lab_admin_group_createRoot');
    add_action( 'wp_ajax_delete_group', 'lab_admin_group_delete');
    add_action( 'wp_ajax_group_subs_add', 'lab_admin_group_subs_addReq');
    add_action( 'wp_ajax_usermeta_names', 'lab_admin_usermeta_names');
    add_action( 'wp_ajax_usermeta_dateLeft', 'lab_admin_usermeta_dateLeft');
    add_action( 'wp_ajax_group_load_substitutes', 'group_load_substitutes');
    add_action( 'wp_ajax_group_delete_substitutes', 'group_delete_substitutes');
    add_action( 'wp_ajax_group_add_substitutes', 'group_add_substitutes');
    add_action( 'wp_ajax_list_users_groups' , 'lab_admin_list_users_groups');
    add_action( 'wp_ajax_add_users_groups' , 'lab_admin_add_users_groups');

    //Actions pour la gestion des params
    add_action( 'wp_ajax_param_create_table', 'lab_admin_param_create_table');
    add_action( 'wp_ajax_save_param', 'lab_admin_param_save');
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

    add_action('wp_ajax_edit_group', 'lab_group_editGroup');
    //Action for settings
    add_action( 'wp_ajax_add_new_metakey', 'lab_ajax_userMetaData_new_key');
    add_action( 'wp_ajax_add_new_metakeys', 'lab_ajax_userMetaData_create_keys');
    add_action( 'wp_ajax_list_metakeys', 'lab_ajax_userMetaData_list_keys');
    add_action( 'wp_ajax_delete_metakey', 'lab_ajax_userMetaData_delete_key');
    add_action( 'wp_ajax_not_exist_metakey', 'lab_ajax_userMeta_key_not_exist');
    add_action( 'wp_ajax_um_correct', 'lab_usermeta_correct_um_fields');
    //Action for hal
    add_action( 'wp_ajax_hal_create_table', 'lab_ajax_hal_create_table');
    add_action( 'wp_ajax_hal_fill_hal_name', 'lab_ajax_hal_fill_fields');
    add_action( 'wp_ajax_hal_download', 'lab_ajax_hal_download');
    add_action( 'wp_ajax_hal_empty_table', 'lab_ajax_delete_hal_table');

    add_action( 'show_user_profile', 'custom_user_profile_fields', 10, 1 );
    add_action( 'edit_user_profile', 'custom_user_profile_fields', 10, 1 );

    add_action( 'wp_ajax_keyring_create_loan', 'lab_keyring_create_loanReq' ); 
    add_action( 'wp_ajax_keyring_find_loan_byKey', 'lab_keyring_find_loan_byKey' ); 
    add_action( 'wp_ajax_keyring_edit_loan', 'lab_keyring_edit_loanReq' ); 
    add_action( 'wp_ajax_keyring_end_loan','lab_keyring_end_loanReq' );
    add_action( 'wp_ajax_keyring_find_old_loans','lab_keyring_find_oldLoansReq' );
}