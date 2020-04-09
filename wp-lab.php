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

//Récupère les constantes
require_once("constantes.php");
require_once("lab-shortcode.php");
require_once("lab-admin-ajax.php");
require_once("lab-admin-core.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tabs.php");
//require_once(LAB_DIR_PATH."admin/view/lab-admin-tabs.php");
///locatrequire_once("link-template.php");
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
add_action( 'wp_ajax_search_user_metadata', 'lab_admin_search_user_metadata' );
add_action( 'wp_ajax_update_user_metadata', 'lab_admin_update_user_metadata' );
add_action( 'wp_ajax_update_user_metadata_db', 'lab_admin_update_user_metadata_db' );
add_action( 'wp_ajax_search_event_category', 'lab_admin_get_event_category' );
add_action( 'wp_ajax_save_event_category', 'lab_admin_save_event_actegory');
add_action( 'wp_ajax_search_group', 'lab_admin_group_search');
add_action( 'wp_ajax_test', 'lab_admin_test');
add_action( 'wp_ajax_group_search_ac', 'lab_admin_search_group_acronym' );
add_action( 'wp_ajax_group_create', 'lab_group_createGroup' );
add_action( 'wp_ajax_group_table', 'lab_createGroupTable' );
add_action( 'wp_ajax_group_root', 'lab_group_createRoot');
add_action( 'wp_ajax_delete_group', 'lab_admin_group_delete');
add_action( 'wp_ajax_param_create_table', 'lab_admin_param_create_table');
add_action( 'wp_ajax_save_param', 'lab_admin_param_save');
add_action( 'wp_ajax_load_param_type', 'lab_admin_param_load_type');
add_action( 'wp_ajax_param_delete', 'lab_admin_param_delete');
add_action( 'wp_ajax_param_search_value', 'lab_admin_param_search_value');

add_action('wp_ajax_edit_group', 'lab_group_editGroup');

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
  wp_enqueue_script('lab', plugins_url('js/lab_global.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), "1.3", false);
  wp_enqueue_style('jqueryToastCSS',plugins_url('css/jquery.toast.css',__FILE__));
  wp_enqueue_script('jqueryToastJS',plugins_url('js/jquery.toast.js',__FILE__),array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'),"1.3.2",false);
}
function wp_lab_global_enqueues()
{
  wp_enqueue_script(
    'global',
    dirname(plugin_basename(__FILE__)) . "/js/lab_global.js",
    array('jquery'),
    '1.3',
    true
  );
  wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
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

function lab_admin_tab_user()
{
?>
  <table class="form-table" role="presentation">
    <tr class="user-rich-editing-wrap">
      <th scope="row">
        <label for="lab_user_name">Nom de l'utilisateur</label>
      </th>
      <td>
        <input type="text" name="lab_user_email" id="lab_user_email" value="" size="80" /><span id="lab_user_id"></span><br>
        <input type="hidden" id="lab_searched_user_id" name="lab_searched_user_id" value="" /><br>
      </td>
    </tr>
    <tr class="user-rich-editing-wrap">
      <th>
        <label for="lab_user_firstname">Prenom</label>
      </th>
      <td>
        <input type="text" disabled="disabled" id="lab_user_firstname">
      </td>
    </tr>
    <tr class="user-rich-editing-wrap">
      <th>
        <label for="lab_user_lastname">Nom</label>
      </th>
      <td>
        <input type="text" disabled="disabled" id="lab_user_lastname">
      </td>
    </tr>
    <tr>
      <td>
        <label for="lab_user_left">Parti</label>
      </td>
      <td>
        <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date">Date de départ</label><input type="text" id="lab_user_left_date">
        <input type="hidden" id="lab_usermeta_id">
      </td>
    </tr>
  </table>
  <a href="#" class="page-title-action" id="lab_user_button_save_left">Modifier le statut de l'utilisateur</a>

<?php
}

/**
 * Function for the parameter lab management
 */
function lab_admin_tab_params() {
  global $wpdb;
?>
  <div id="lab_createGroup_form">
    <h3>Manage Parameters :</h3>
    <table>
      <tr>
        <!-- NEW PARAM -->
        <td>
    <h4>New Parameters</h4>
    <label for="wp_lab_param_type">Type param</label>
    <select id="wp_lab_param_type">
<?php
  $results = lab_admin_param_load_param_type();
  foreach ( $results as $r ) {
    echo("<option value=\"" . $r->id . "\">" . $r->value . "</option>");
  }
?>
    </select><a href="#" class="page-title-action" id="lab_tab_param_delete">delete</a>
    <br>
    <label for="wp_lab_param_value">Param value</label>
    <input type="text" id="wp_lab_param_value">
    <a href="#" class="page-title-action" id="lab_tab_param_save">Save param</a>
        </td>
        <!-- EDIT PARAM -->
        <td>
          <h4>Edit parameters</h4>
          <label for="lab_param_param_title">Param title</label>
          <input type="text" id="lab_param_value_search" placeholder="type param first letter">
          <input type="hidden" id="wp_lab_param_id">
          <label for="wp_lab_param_type_edit">Param type</label>
          <select id="wp_lab_param_type_edit"></select>
          <a href="#" class="page-title-action" id="lab_tab_param_save_edit">Save param</a>
          <a href="#" class="page-title-action" id="lab_tab_param_delete_edit">Delete param</a>

        </td>
      </tr>
    </table>
    <hr>
    <h4>Create Table :</h4>
    <a href="#" class="page-title-action" id="lab_tab_param_create_table">Create table</a>
  </div>
<?php
}

/**
 * Function for the groups management
 */
function lab_admin_tab_groups() {
  ?>
  <div>
    <label for="wp_lab_group_name">Nom du groupe</label>
    <input type="text" name="wp_lab_group_name" id="wp_lab_group_name" value="" size="80"/>
    <button class="page-title-action" id="delete_button">Supprimer le groupe</button><br>
    <input type="hidden" id="lab_searched_group_id" name="lab_searched_group_id" value=""/>
    
  </div>
  <div id="suppr_result"></div>
<hr>
 <!-- Modifier un groupe -->
  <div class="wp_lab_editGroup_form">
    <h3>Modifier un groupe</h3>
    <label for="wp_lab_group_to_edit">Je souhaite modifier le groupe :</label>
    <select name="wp_lab_group" id ="wp_lab_group_to_edit">
    <?php
      // les options de la balise select viennent de la base de donnée
      $sql = "SELECT id, group_name FROM `wp_lab_groups`";
      global $wpdb;
      $results = $wpdb->get_results($sql);
      foreach ( $results as $r ) {
        echo("<option value=\"" . $r->id . "\">" . $r->group_name . "</option>");
      }
    ?>
    </select>
    <label for="wp_lab_group_acronym_edit">Modifier l'acronyme :</label>
    <input type="text" name="wp_lab_acronym" id="wp_lab_group_acronym_edit" value="" size=10 placeholder="AA"/><br/><br/>
    <label for="wp_lab_group_name_edit">Nouveau nom du groupe :</label>
    <input type="text" name="wp_lab_group_name" id="wp_lab_group_name_edit" value="" size=50 placeholder="Nouveau nom"/>
    <label for="wp_lab_group_chief_edit">Définir un autre chef de groupe :</label>
    <select name="wp_lab_group_chief" id="wp_lab_group_chief_edit">
    <?php
      $sql ="SELECT ID, display_name FROM `wp_users`";
      global $wpdb;
      $results = $wpdb->get_results($sql);
      foreach ( $results as $r ) {
        echo("<option value=\"" . $r->ID . "\">" . $r->display_name . "</option>");
      }
    ?>
    </select><br /><br />
    <label for="wp_lab_group_parent_edit">Modifier le groupe parent :</label>
    <select name="wp_lab_group_parent" id="wp_lab_group_parent_edit">
      <option value="0">Aucun</option>
    <?php
      $sql = "SELECT id, group_name FROM `wp_lab_groups`";
      global $wpdb;
      $results = $wpdb->get_results($sql);
      foreach ( $results as $r ) {
        echo("<option value=\"" . $r->id . "\">" . $r->group_name . "</option>");
      }
    ?>
    </select>
    <label for="wp_lab_group_type_edit">Modifier le type :</label>
    <select name="wp_lab_group_type" id="wp_lab_group_type_edit">
      <option value="1">Groupe</option>
      <option value="2">Equipe</option>
    </select>
    <input type="hidden" id="lab_searched_chief_id" name="lab_searched_chief_id" value="" />
    <br /><br />
    
    <br /><a href="#" class="page-title-action" id="lab_editGroup">Modifier le groupe</a>

  <hr>
  <?php
    if (!lab_admin_checkTable("wp_lab_groups")) {
      echo "<p id='lab_group_noTableWarning'>La table <em>wp_lab_groups</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
  ?>
  <button class="page-title-action" id="lab_createGroup_createTable">Créer la table Groups</button>
  <button class="page-title-action" id="lab_createGroup_createRoot">Créer groupe root</button>
  <hr/>
  <table class="form-table" role="presentation">
  <h3>Créer un groupe : </h3>
  <form action="javascript:void(0);">
	<tbody>
    <tr class="form-field form-required">
      <th scope="row"><label for="lab_createGroup_name">Nom du groupe* : </label></th>
      <td><input type="text" id="lab_createGroup_name" name="lab_createGroup_name" placeholder="Analyse Appliquée"/></td>
    </tr class="form-field form-required">
      <th scope="row"><label for="lab_createGroup_acronym">Acronyme* <span class="description">(unique)</span> : </label></th>
      <td>
        <input type="text" id="lab_createGroup_acronym" name="lab_createGroup_acronym" placeholder="AA"/>
        <label style="padding-left:2em;" id="lab_createGroupe_acronym_hint"></span>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_parentGroup">Groupe parent :</label></th>
      <td>
        <select id="lab_createGroup_parent" name="lab_createGroup_parent">
          <option value="0">None</option>
          <?php //Récupère la liste des groupes en affichant leur acronymes dans la liste déroulante.
            $sql = "SELECT id,acronym FROM `wp_lab_groups`";
            global $wpdb;
            $results = $wpdb->get_results($sql);
            $output="";
            foreach ( $results as $r )
            {
              $output .= "<option value =".$r->id.">".$r->acronym."</option>";
            }
            echo $output;
          ?>
        </select>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_type">Type :</label></th>
      <td><select id="lab_createGroup_type" name="lab_createGroup_Type">
        <option value="1">Groupe</option>
        <option value="2">Équipe</option>
      </select></td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_chief">Chef du groupe :</label></th>
      <td>
        <input id="lab_createGroup_chief" required type="text" name="lab_createGroup_chief" placeholder="Pascal HUBERT"/>
        <input type="text" hidden disabled id="lab_createGroup_chiefID"></span>
      </td>
    </tr>
    <tr class="form-field">
      <td><input class="page-title-action" type="submit" id="lab_createGroup_create" value="Créer le groupe"/></td>
    </tr>
	</tbody></form></table>
  <br />
  <hr />
  </div>

<?php
}

function lab_group_editGroup() {
  $id = $_POST['groupId'];
  $acronym = $_POST['acronym'];
  $groupName = $_POST['groupName'];
  $chiefId = $_POST['chiefId'];
  $parent = $_POST['parent'];
  $type = $_POST['group_type'];

  $sql = "UPDATE `wp_lab_groups` SET `group_name` = '$groupName', `acronym` = '$acronym',
   `chief_id` = '$chiefId', `groupe_type` = '$type', `parent_group_id` = '$parent'
    WHERE id= '$id';";
  global $wpdb;
  echo $sql;
  $results = $wpdb->get_results($sql);
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
