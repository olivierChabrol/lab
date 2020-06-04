<?php
/**
 * TAB settings in lab administration backend
 * @author Olivier CHABROL
 */

/**
 * Function for the groups management
 */
function lab_admin_tab_settings() {
  ?>
  <h1>Database Tables</h1>
  <hr/>
    <h4>Create Table :</h4>
<?php
  if (!lab_admin_checkTable("lab_keys")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_key_loans")) {
    echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_key_loans</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_hal")) {
    echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_hal</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_keys")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_groups")) {
    echo "<p id='lab_group_noTableWarning'>La table <em>wp_lab_groups</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_users_groups")) {
    echo "<p id='lab_group_noSubTableWarning'>La table <em>wp_lab_users_groups</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_group_substitutes")) {
    echo "<p id='lab_group_noSubTableWarning'>La table <em>wp_lab_group_substitutes</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_keys")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("lab_key_loans")) {
    echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_key_loans</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
?>
  <p></p>
  <button class="page-title-action" id="lab_hal_create">Create table HAL</button>
  <button class="page-title-action" id="lab_keyring_create_table_keys">Créer la table Keys</button>
  <button class="page-title-action" id="lab_keyring_create_table_loans">Créer la table Loans</button>
    <a href="#" class="page-title-action" id="lab_tab_param_create_table">Create table param</a>
  <hr>
  <!-- Gestion des tables -->
  <?php
  ?>
  <button class="page-title-action" id="lab_createGroup_createTable">Créer la table Groups</button>
  <button class="page-title-action" id="lab_user_group_create_table">Créer la table User Groups</button>
  <button class="page-title-action" id="lab_createGroup_createTable_Sub">Créer la table Substitutes</button>
  <button class="page-title-action" id="lab_createGroup_createRoot">Créer groupe root</button>
  <hr/>
  <button class="page-title-action" id="lab_presence_create_table"><?php echo esc_html__('Créer la table presence','lab'); ?></button><br>
  <hr/>
  <button class="page-title-action" id="lab_invite_create_tables"><?php echo esc_html__('Créer la table Invitations','lab'); ?></button>
  <hr/>
  <button class="page-title-action" id="lab_create_table_historic"><?php echo esc_html__('Créer la table Historic','lab'); ?></button>
  <hr/>
  <button class="page-title-action" id="lab_admin_reset_db"><?php echo esc_html__('Reset DB','lab'); ?></button>
  <div id="lab_admin_setting_delete_dialog" class="modal">
      <p><?php esc_html_e('Reset LAB DB ?','lab');?></p>
      <div id="lab_admin_setting_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Annuler','lab')?></a>
        <a href="#" rel="modal:close" id="lab_admin_setting_delete_dialog_confirm" keyid=""><?php esc_html_e('Confirmer','lab'); ?></a>
      </div>
    </div>
  <button class="page-title-action" id="lab_admin_role_add_keyring"><?php echo esc_html__('Add role keyring','lab'); ?></button>
  <hr>
    <h4>Correct DB :</h4>
  <button class="page-title-action" id="lab_settings_correct_um"><?php echo esc_html__('Corriger usermeta fields UM','lab'); ?></button>
  <button class="page-title-action" id="lab_settings_copy_phone"><?php echo esc_html__('Copier les champs phone','lab'); ?></button>
  <hr>
    <h4>Fill Table :</h4>
    <button class="page-title-action" id="lab_settings_button_fill_hal_name_fields"><?php echo esc_html_e('Fill HAL name','lab'); ?></button>
    <button class="page-title-action" id="lab_settings_button_fill_user_slug_fields"><?php echo esc_html_e('Fill user slug','lab'); ?></button>
    <input type="text" id="lab_hal_user">
    <button class="page-title-action" id="lab_hal_delete_table"><?php echo esc_html_e('Empty HAL Table','lab'); ?></button>
  <hr/>
  <h2>Create usermetadata key</h2>
  <label for="usermetadata_user_search">User</label>
  <input type="text" id="usermetadata_user_search"><input type="hidden" id="usermetadata_user_search_id">
  <label for="usermetadata_key">Key</label>
  <input type="text" id="usermetadata_key">
  <label for="usermetadata_value">Value</label>
  <input type="text" id="usermetadata_value">
  <a href="#" class="page-title-action" id="lab_settings_button_addKey"><?php echo esc_html_e('Add Meta Key','lab'); ?></a>
  <hr/>
  <h2>Create Metadata key for all user</h2>
  <label for="usermetadata_key_all">Key</label>
  <input type="text" id="usermetadata_key_all">
  <label for="usermetadata_value_all">Value</label>
  <input type="text" id="usermetadata_value_all">
  <!-- <a href="#" class="page-title-action" id="lab_settings_button_addKey_all">Add Keys</a> -->
  <button class="lab_keyring_create_table_keys" id="lab_settings_button_addKey_all">Add Keys</button>
  <hr/>
  <h2>Social networks for all user</h2>
  <button class="page-title-action" id="lab_setting_social_delete_button">Delete all social networks keys</button>
  <button class="page-title-action" id="lab_setting_social_create_button">Create all social networks keys</button>
  <hr/>
  <h2>Existing usermeta keys</h2>
  <label for="usermetadata_keys">Existing keys : </label>
  <select id="usermetadata_keys"></select> 
  <a href="#" class="page-title-action" id="lab_settings_button_delete_keys_all">Delete keys for all users</a>
<?php
}