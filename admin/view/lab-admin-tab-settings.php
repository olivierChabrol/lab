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
<?php
  if (!lab_admin_checkTable("wp_lab_keys")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
  if (!lab_admin_checkTable("wp_lab_key_loans")) {
    echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_key_loans</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
?>
  <p></p>
  <button class="lab_keyring_create_table_keys" id="lab_keyring_create_table_keys">Créer la table Keys</button>
  <button class="lab_keyring_create_table_loans" id="lab_keyring_create_table_loans">Créer la table Loans</button>
  <hr/>
  <?php
  if (!lab_admin_checkTable("wp_lab_keys")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
  }
?>
    <h4>Create Table :</h4>
    <a href="#" class="page-title-action" id="lab_tab_param_create_table">Create table</a>
  <hr>
  <!-- Gestion des tables -->
  <?php
    if (!lab_admin_checkTable("wp_lab_groups")) {
      echo "<p id='lab_group_noTableWarning'>La table <em>wp_lab_groups</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
    if (!lab_admin_checkTable("wp_lab_group_substitutes")) {
      echo "<p id='lab_group_noSubTableWarning'>La table <em>wp_lab_group_substitutes</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
  ?>
  <button class="page-title-action" id="lab_createGroup_createTable">Créer la table Groups</button>
  <button class="page-title-action" id="lab_createGroup_createTable_Sub">Créer la table Substitutes</button>
  <button class="page-title-action" id="lab_createGroup_createRoot">Créer groupe root</button>
  <hr/>
  <h2>Create usermetadata key</h2>
  <label for="usermetadata_user_search">User</label>
  <input type="text" id="usermetadata_user_search"><input type="hidden" id="usermetadata_user_id">
  <label for="usermetadata_key">Key</label>
  <input type="text" id="usermetadata_key">
  <label for="usermetadata_value">Value</label>
  <input type="text" id="usermetadata_value">
  <a href="#" class="page-title-action" id="lab_settings_button_addKey">Add Key</a>
  <hr/>
  <h2>Create Metadata key for all user</h2>
  <label for="usermetadata_key_all">Key</label>
  <input type="text" id="usermetadata_key_all">
  <label for="usermetadata_value_all">Value</label>
  <input type="text" id="usermetadata_value_all">
  <!-- <a href="#" class="page-title-action" id="lab_settings_button_addKey_all">Add Keys</a> -->
  <button class="lab_keyring_create_table_keys" id="lab_settings_button_addKey_all">Add Keys</button>
  <hr/>
  <h2>Existing usermeta keys</h2>
  <label for="usermetadata_keys">Existing keys : </label>
  <select id="usermetadata_keys"></select> 
  <a href="#" class="page-title-action" id="lab_settings_button_delete_keys_all">Delete keys for all users</a>
<?php
}