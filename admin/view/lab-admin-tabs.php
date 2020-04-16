<?php
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-params.php");

/**
 * Crée le menu d'option du plugin
 */
function wp_lab_option()
{
  global $EM_Event;
  $active_tab = 'default';
  if (isset($_GET['tab'])) {
    $active_tab = $_GET['tab'];
  }
  if (!is_object($EM_Event)) {
    $EM_Event = new EM_Event();
  }
?>
  <div class="wrap">
    <h1 class="wp-heading-inline">Lab <?php echo (dirname(plugin_basename("__FILE__" . "js/lab_global.js"))); ?></h1>
    <!--    <a href="https://www.i2m.univ-amu.fr/wp-admin/post-new.php?post_type=event" class="page-title-action">Ajouter un évènement</a> -->
    <hr class="wp-header-end">
    <h2 class="nav-tab-wrapper">
      <a id="lab_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'default' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'default'), $_SERVER['REQUEST_URI']); ?>">Séminaires</a>
      <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'user_settings' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'user_settings'), $_SERVER['REQUEST_URI']); ?>">Users Settings</a>
      <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'user_genetal_settings' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'user_general_settings'), $_SERVER['REQUEST_URI']); ?>">Users General Settings</a>
      <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'groups' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'groups'), $_SERVER['REQUEST_URI']); ?>">Groups</a>
      <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'params' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'params'), $_SERVER['REQUEST_URI']); ?>">Parameters</a>
      <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'keyring' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'keyring'), $_SERVER['REQUEST_URI']); ?>">KeyRing</a>
      <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'settings'), $_SERVER['REQUEST_URI']); ?>">General settings</a>
    </h2>
    <table style="width:100%;">
      <tr>
        <td style="width:65%;vertical-align:top;" id="configurationForm">
          <?php
          if ($active_tab == 'user_settings') {
            lab_admin_tab_user();
          } else if ($active_tab == 'user_general_settings') {
            lab_admin_tab_general_user();
          } else if ($active_tab == 'groups') {
            lab_admin_tab_groups();
          } else if ($active_tab == 'params') {
            lab_admin_tab_params();
          } else if ($active_tab == 'keyring') {
            lab_admin_tab_keyring();
          } else if ($active_tab == 'loan-contract') {
            lab_admin_keyring_contract();
          } else {
            lab_admin_tab_seminaire();
          }
          ?>
        </td>
      </tr>
    </table>
  </div>
  <script>
  </script>
<?php
}