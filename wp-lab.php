<?php
/*
Plugin Name: LAB
Plugin URI: https://www.i2m.univ-amu.fr
Description: Pluggin de l'I2M de gestion du labo
Authors: Astrid BEYER, Ivan Ivanov, Lucas Argenti, Olivier CHABROL
Version: 1.0
Author URI: http://www.i2m.univ-amu.fr
*/

// Traduction de la description
__("Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.", "wp-hal");

define('LAB_DIR_PATH', plugin_dir_path(__FILE__));
define('LAB_META_PREFIX', "lab_");
define('LAB_HAL_URL', 'http://api.archives-ouvertes.fr/search/hal/');

//Récupère les constantes
require_once("constantes.php");
require_once("lab-shortcode.php");
require_once("lab-shortcode-directory.php");
require_once("lab-admin-ajax.php");
require_once("lab-admin-core.php");
require_once("lab-admin-groups.php");
require_once("lab-admin-params.php");
require_once("lab-admin-keyring.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tabs.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-groups.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-params.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-users.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-settings.php");
require_once("lab-html-helper.php");

global $wpdb;
$dbTablePrefix = $wpdb->prefix;
define("LAB_TABLE_HAL", $dbTablePrefix."lab_hal");
define("LAB_TABLE_GROUPS", $dbTablePrefix."lab_groups");
define("LAB_TABLE_GROUP_SUBSTITUTE", $dbTablePrefix."lab_group_substitute");
define("LAB_TABLE_KEYS", $dbTablePrefix."lab_keys");
define("LAB_TABLE_KEY_LOAN", $dbTablePrefix."lab_key_loan");
define("LAB_TABLE_PARAMS", $dbTablePrefix."lab_params");

//Admin Files
if (is_admin()) {
}
$active_tab = 'default';
if (isset($_GET['tab'])) {
  $active_tab = $_GET['tab'];
}

if (locale == 'fr_FR') {
  define('lab_lang', 'fr');
} elseif (locale == 'es_ES') {
  define('lab_lang', 'es');
} else {
  define('lab_lang', 'en');
}

add_shortcode('lab-directory', 'lab_directory');
add_shortcode('lab_old-event', 'lab_old_event');
add_shortcode('lab-old-event', 'lab_old_event');
add_shortcode('lab-event', 'lab_event');
add_shortcode('lab-event-of-the-week', 'lab_event_of_the_week');
add_shortcode('lab-incoming-event', 'lab_incoming_event');

add_action('admin_enqueue_scripts', 'admin_enqueue');

/**
 * Ajoute le widget wphal à l'initialisation des widgets
 */
add_action('widgets_init', 'wplab_init');

/**
 * Initialise le nouveau widget
 */
function wplab_init()
{
  register_widget("wplab_widget_week_event");
}

/**
 * Ajoute le menu à l'initialisation du menu admin
 */
add_action( 'admin_menu'          , 'wp_lab_menu' );
add_action( 'wp_enqueue_scripts'  , 'wp_lab_global_enqueues' );
add_action( 'wp_ajax_search_event', 'lab_admin_search_event' );
add_action( 'wp_ajax_search_user'      , 'lab_admin_search_user' );
add_action( 'wp_ajax_search_username', 'lab_admin_search_username' );
add_action( 'wp_ajax_search_username2', 'lab_admin_search_username2' );
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
add_action( 'wp_ajax_group_sub_table', 'lab_admin_createSubTable' );
add_action( 'wp_ajax_group_root', 'lab_admin_group_createRoot');
add_action( 'wp_ajax_delete_group', 'lab_admin_group_delete');
add_action( 'wp_ajax_group_subs_add', 'lab_admin_group_subs_addReq');
add_action( 'wp_ajax_usermeta_names', 'lab_admin_usermeta_names');
add_action( 'wp_ajax_group_load_substitutes', 'group_load_substitutes');
add_action( 'wp_ajax_group_delete_substitutes', 'group_delete_substitutes');
add_action( 'wp_ajax_group_add_substitutes', 'group_add_substitutes');

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
//Action for hal
add_action( 'wp_ajax_hal_create_table', 'lab_ajax_hal_create_table');
add_action( 'wp_ajax_hal_fill_hal_name', 'lab_ajax_hal_fill_fields');
add_action( 'wp_ajax_hal_download', 'lab_ajax_hal_download');
add_action( 'wp_ajax_hal_empty_table', 'lab_ajax_delete_hal_table');

add_action( 'show_user_profile', 'custom_user_profile_fields', 10, 1 );
add_action( 'edit_user_profile', 'custom_user_profile_fields', 10, 1 );

/**
 * Show custom user profile fields
 * 
 * @param  object $profileuser A WP_User object
 * @return void
 */
function custom_user_profile_fields( $profileuser ) {
  ?>
    <table class="form-table">
      <tr>
        <th>
          <label for="user_hal_id">Hal ID</label>
        </th>
        <td>
          <input type="text" name="user_hal_id" id="user_hal_id" value="<?php echo esc_attr( get_user_meta($profileuser->ID, 'lab_hal_id', true) ); ?>" class="regular-text" />
          <br><span class="description"><?php esc_html_e( 'Your HAL id', 'text-domain' ); ?></span>
        </td>
      </tr>
      <tr>
        <th>
          <label for="user_hal_name">Hal Name</label>
        </th>
        <td>
          <input type="text" name="user_hal_name" id="user_hal_name" value="<?php echo esc_attr( get_user_meta($profileuser->ID, 'user_hal_name', true) ); ?>" class="regular-text" />
          <br><span class="description"><?php esc_html_e( 'Your HAL name', 'text-domain' ); ?></span>
        </td>
      </tr>
    </table>
  <?php
  }

/**
 * Fonction de création du menu
 */
function wp_lab_menu()
{
  add_menu_page('Options', 'LAB', 'manage_options', 'wp-lab.php', 'wp_lab_option', '', 21);
}

/***********************************************************************************************
 * ADMINISTRATION
 **********************************************************************************************/

/**
 * Fonction qui permet de charger ce que l'on veut comme JS ou CSS dans l'administration
 **/
function admin_enqueue()
{
  //wp_enqueue_script('lab', plugins_url('js/lab_global.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), filemtime(dirname(plugin_basename("__FILE__"))."/js/lab_global.js"), false);
  wp_enqueue_script('wp-lab', plugins_url('js/lab_global.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), "1.3", false);
  localize_script();
  //Plugin permettant d'afficher les toasts :
  wp_enqueue_style('jqueryToastCSS',plugins_url('css/jquery.toast.css',__FILE__));
  wp_enqueue_script('jqueryToastJS',plugins_url('js/jquery.toast.js',__FILE__),array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'),"1.3.2",false);
  //Feuille de style de l'onglet keyring :
  wp_enqueue_style('KeyRingTableCSS',plugins_url('css/keyring.css',__FILE__));
  //Plugin permettant d'afficher des fenêtres modales :
  wp_enqueue_style('jqueryModalCSS',plugins_url('css/jquery.modal.min.css',__FILE__));
  wp_enqueue_script('jqueryModalJS',plugins_url('js/jquery.modal.min.js',__FILE__),array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'),"0.9.1",false);
}

function wp_lab_global_enqueues()
{
  wp_enqueue_script(
    'wp-lab',
    plugins_url('js/lab_global.js',__FILE__),
    array('jquery'),
    '1.3',
    true
  );
  localize_script();
  //wp_localize_script('wp-lab', 'lab', array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
}

function localize_script() {
  $js_vars = array();
  $schema = is_ssl() ? 'https':'http';
  $js_vars['ajaxurl'] = admin_url('admin-ajax.php', $schema);
  wp_localize_script('wp-lab', 'LAB', apply_filters('lab_js_vars', $js_vars));
}

function lab_admin_tab_general_user()
{
?>
  <br>
  <a href="#" class="page-title-action" id="lab_user_button_update_db">ajouter lab_left_user à tous les utilisateurs</a>
  <br>
  <a href="#" class="page-title-action" id="lab_user_button_test">test</a>
<?php
}


function lab_admin_tab_seminaire()
{
?>
  <label for="wp_lab_event_title">Nom du seminaire</label>
  <input type="text" name="lab_eventTitle" id="wp_lab_event_title" value="" size="80" /><span id="lab_event_id"></span><br>
  <label id="wp_lab_event_label"></label><span id="wp_lab_event_date"></span>
  <input type="hidden" id="lab_searched_event_id" name="lab_searched_event_id" value="" />
  <br>
  <?php lab_locate_template('forms/event/categories-public.php', true); ?>
  <br><a href="#" class="page-title-action" id="lab-button-change-category">Modifier la categorie d'un evenement</a>
<?php
}


function old_event1($param)
{
  extract(shortcode_atts(
    array(
      'event' => get_option('option_event')
    ),
    $param
  ));
  $eventCaterory = get_option('option_event');
  //$sql = "SELECT p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_posts` as p ON p.`ID`=tr.`object_id` WHERE t.name='".$event."' AND `p`.`post_date` < NOW() ORDER BY `p`.`post_date` DESC ";
  $sql = "SELECT p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` WHERE t.slug='" . $event . "' AND `p`.`event_end_date` < NOW() ORDER BY `p`.`event_end_date` DESC ";
  global $wpdb;
  // SELECT * FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` WHERE t.name='HYPERBO' 
  $results = $wpdb->get_results($sql);
  $listEventStr = "<table>";
  $url = esc_url(home_url('/'));
  foreach ($results as $r) {
    $listEventStr .= "<tr>";
    $listEventStr .= "<td>" . esc_html($r->event_start_date) . "</td><td><a href=\"" . $url . "event/" . $r->event_slug . "\">" . $r->event_name . "</a></td>";
    $listEventStr .= "</tr>";
  }
  $listEventStr .= "</table>";
  //return "category de l'evenement : ".$event."<br>".$sql."<br>".$listEventStr;
  return $listEventStr;
}


/***********************************************************************************************************************
 * PLUGIN WIDGET
 **********************************************************************************************************************/

/**
 * Classe du widget wplab week event
 */
class wplab_widget_week_event extends WP_widget
{

  /**
   * Défini les propriétés du widget
   */
  function __construct()
  {
    $options = array(
      "classname" => "wplab-week-event",
      "description" => __("Affiche les evenements de la semaine en cour.", 'wp-hal')
    );

    parent::__construct(
      'lab-week-event',
      __("Semaine du laboratoire", 'wp-hal'),
      $options
    );
  }

  /**
   * Crée le widget
   * @param $args
   * @param $instance
   */
  function widget($args, $instance)
  {
    extract($args);

    $day = date('w');
    $week_start = date('Y-m-d', strtotime('-' . ($day - 1) . ' days'));
    $week_end = date('Y-m-d', strtotime('+' . (7 - $day) . ' days'));

    $sql = "SELECT * FROM `wp_em_events` WHERE `event_start_date` >= '" . $week_start . "' AND `event_end_date` <= '" . $week_end . "'";
    // SELECT t.name, p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` WHERE p.`event_start_date` >= '2020-01-06' AND p.`event_end_date` <= '2020-01-12' ORDER BY `p`.`event_end_date` DESC 
    global $wpdb;
    $results = $wpdb->get_results($sql);

    echo $before_widget;
    echo $before_title . "Semaine du laboratoire" . $after_title . "<br>";
    echo "Du " . $week_start . " au " . $week_end . "<br>";
    echo "<ul>";
    foreach ($results as $r) {
      echo "<li>" . esc_html($r->event_start_date) . "</td><td><a href=\"" . $url . "event/" . $r->event_slug . "\">" . $r->event_name . "</a></li>";
    }
    echo "</ul>";
    echo $after_widget;
  }

  /**
   * Sauvegarde des données
   * @param $new
   * @param $old
   */
  function update($new, $old)
  {
    return $new;
  }

  /**
   * Formulaire du widget
   * @param $instance
   */
  function form($instance)
  {
    $defaut = array(
      'titre' => __("Publications récentes", 'wp-hal'),
      'select' => "authIdHal_s",
      'typetext' => "title_s",
      'nbdoc' => 5
    );
    $instance = wp_parse_args($instance, $defaut);
  }
}
