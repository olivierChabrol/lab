<?php
/*
Plugin Name: LAB
Plugin URI: https://www.i2m.univ-amu.fr
Description: Pluggin de l'I2M de gestion du labo
Authors: Astrid BEYER, Ivan Ivanov, Lucas Urgenti, Olivier CHABROL, Hongda MA
Version: 1.0
Author URI: http://www.i2m.univ-amu.fr
Text Domain: lab
Domain Path: /lang
*/

// Traduction de la description
__("Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.", "wp-hal");

define('LAB_DIR_PATH', plugin_dir_path(__FILE__));
define('LAB_META_PREFIX', "lab_");
define('LAB_HAL_URL', 'http://api.archives-ouvertes.fr/search/hal/');

//Récupère les constantes
require_once("constantes.php");
//require_once("lab-shortcode.php");
require_once("lab-admin-ajax.php");
require_once("lab-admin-core.php");
require_once("lab-admin-groups.php");
require_once("lab-admin-params.php");
require_once("lab-admin-keyring.php");
require_once("lab-admin-budget.php");
require_once("lab-event-add.php");
require_once("admin/view/lab-admin-contract.php");
require_once("lab-actions.php");
require_once("lab-hal-widget.php");
require_once("lab-admin-invitations.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-present.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-directory.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-phd-student.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-profile.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-hal.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-event.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-trombinoscope.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-invitation.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-labo1dot5.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-labo1dot5admin.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-labo1dot5resp.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-ldap.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-request.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-internship.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-cirm.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-conditionnal-display.php");
require_once(LAB_DIR_PATH."shortcode/lab-shortcode-reset-password.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tabs.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-mission.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-events.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-groups.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-params.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-users.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-settings.php");
require_once(LAB_DIR_PATH."admin/view/lab-admin-tab-ldap.php");
require_once(LAB_DIR_PATH."lab-admin-ldap.php");
require_once(LAB_DIR_PATH."view/lab-view-user.php");
require_once(LAB_DIR_PATH."core/Lab_Event.php");
require_once(LAB_DIR_PATH."view/lab-view-request.php");
require_once(LAB_DIR_PATH."core/lab-admin-core-request.php");
require_once(LAB_DIR_PATH."core/lab-admin-internship.php");
require_once("lab-html-helper.php");
require_once("lab-utils.php");
require_once("lab-labo1dot5.php");


global $wpdb;
$dbTablePrefix = $wpdb->prefix;
define("LAB_TABLE_HAL", $dbTablePrefix."lab_hal");
define("LAB_TABLE_GROUPS", $dbTablePrefix."lab_groups");
define("LAB_TABLE_GROUP_SUBSTITUTE", $dbTablePrefix."lab_group_substitute");
define("LAB_TABLE_KEYS", $dbTablePrefix."lab_keys");
define("LAB_TABLE_KEY_LOAN", $dbTablePrefix."lab_key_loan");
define("LAB_TABLE_PARAMS", $dbTablePrefix."lab_params");
define("VERSION", '1');

function version_id() {
  if ( WP_DEBUG )
    return time();
  return VERSION;
}

//Admin Files
if (is_admin()) {
}
$active_tab = 'default';
if (isset($_GET['tab'])) {
  $active_tab = $_GET['tab'];
}

if (lab_locale == 'fr_FR') {
  define('lab_lang', 'fr');
} elseif (lab_locale == 'es_ES') {
  define('lab_lang', 'es');
} else {
  define('lab_lang', 'en');
}

function bootstrap_script() {
  wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
}
add_action( 'wp_enqueue_scripts', 'replace_core_jquery_version' );

function replace_core_jquery_version() {
  wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
  wp_enqueue_script('media-upload');
  wp_enqueue_script('thickbox');
  wp_enqueue_style('thickbox');
  wp_enqueue_media();
 }

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */

add_action( 'plugins_loaded', 'myplugin_load_textdomain' );
add_action('admin_enqueue_scripts', 'admin_enqueue');


add_shortcode('lab-users-thematic', 'lab_shortcode_thematic_display');
add_shortcode('lab-present', 'lab_present_select');
add_shortcode('lab-phd-student', 'lab_display_phd_student');
add_shortcode('lab-present-choice', 'lab_present_choice');
add_shortcode('lab-directory', 'lab_directory');
add_shortcode('lab-profile', 'lab_profile' );
add_shortcode('lab_old-event', 'lab_old_event');
add_shortcode('lab-old-event', 'lab_old_event');
add_shortcode('lab-event', 'lab_event');
add_shortcode('lab-event-of-the-week', 'lab_event_of_the_week');
add_shortcode('lab-event-of-the-day', 'lab_event_of_the_day');
add_shortcode('lab-event-of-the-year', 'lab_event_of_the_year');
add_shortcode('lab-incoming-event', 'lab_incoming_event');
add_shortcode('lab-trombinoscope', 'lab_trombinoscope');
add_shortcode('lab-user-by-thematic', 'lab_users_by_thematic');
add_shortcode('lab-hal', 'lab_hal');
add_shortcode('lab-cirm', 'lab_cirm');
add_shortcode('lab-invite', 'lab_invitation');
add_shortcode('lab-mission', 'lab_mission');
add_shortcode('lab-labo1dot5', 'lab_labo1_5');
add_shortcode('lab-labo1dot5admin', 'lab_labo1_5admin');
add_shortcode('lab-labo1dot5resp', 'lab_labo1_5resp');
add_shortcode('lab-invite-interface','lab_invitations_interface');
add_shortcode('lab-ldap','lab_ldap');
add_shortcode('lab-hal-tools', 'lab_hal_tools');
add_shortcode('lab-request', 'lab_request');
add_shortcode('lab-internship', 'lab_internship');
add_shortcode('lab-display-if', 'lab_display_if');
add_shortcode('lab-reset-password', 'lab_reset_password');

register_activation_hook( __FILE__, 'lab_activation_hook' );
register_uninstall_hook(__FILE__, 'lab_uninstall_hook');

add_action( 'show_user_profile', 'lab_add_user_profile_fields' );
add_action( 'personal_options_update', 'lab_save_extra_user_profile_fields' );

if (!function_exists('lab_json')) {
    function lab_json() {
        //check if this is a calendar request for all events
        if (preg_match('/events.json(\?.+)?$/', $_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/?json=1') {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: inline; filename="events.json"');
            //send headers
            if (function_exists('em_locate_template')) {
                include 'templates/event-json.php';
            }
            die();
        }
    }
    add_action('init', 'lab_json');
}
  
/*
 * Ajoute le widget wphal à l'initialisation des widgets
 */
add_action('widgets_init', 'wplab_init');

add_filter('get_wp_user_avatar', 'custom_user_avatar', 1, 5);
remove_action('wpua_before_avatar', 'wpua_do_before_avatar');
remove_action('wpua_after_avatar', 'wpua_do_after_avatar');




function custom_user_avatar($avatar, $id_or_email = NULL, $size = NULL, $align = NULL, $alt = NULL) {
  if(is_object($id_or_email) && isset($id_or_email->comment_author_email))
  {
    $comment_user = get_user_by('email',$id_or_email->comment_author_email);
    if(is_object($comment_user))
    {
      $user_pic_base_url = 'url';
      $imgId = get_user_meta($comment_user->ID, 'lab_user_picture_display', true);
      if($imgId)
      {
        //$avatar = '<img src="' . $user_pic_base_url . $user_pic . '" width="54" height="54" alt="admin.kh" class="avatar avatar-54 wp-user-avatar wp-user-avatar-54 alignnone photo">';
        $avatar = wp_get_attachment_image($imgId, array('54', '54'));
      }
    }
  }
  return $avatar;
}

function lab_add_user_profile_fields($user) {
?>
  <h3><?php _e("Extra profile information", "lab"); ?></h3>

  <table class="form-table">
    <tr>
      <td>
        <table class="form-table">
        <tr>
            <th><label for="address1"><?php _e("Address line 1"); ?></label></th>
            <td>
                <input type="text" name="address1" id="address1" value="<?php echo esc_attr( get_the_author_meta( 'lab_address1', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your address.", "lab"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="address2"><?php _e("Address line 2"); ?></label></th>
            <td>
                <input type="text" name="address2" id="address2" value="<?php echo esc_attr( get_the_author_meta( 'lab_address2', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your address.", "lab"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="address3"><?php _e("Address line 3"); ?></label></th>
            <td>
                <input type="text" name="address3" id="address3" value="<?php echo esc_attr( get_the_author_meta( 'lab_address3', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your address.", "lab"); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="city"><?php _e("City"); ?></label></th>
            <td>
                <input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'lab_city', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your city.", "lab"); ?></span>
            </td>
        </tr>
        <tr>
        <th><label for="postalcode"><?php _e("Postal Code"); ?></label></th>
            <td>
                <input type="text" name="postalcode" id="postalcode" value="<?php echo esc_attr( get_the_author_meta( 'lab_postalcode', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your postal code.", "lab"); ?></span>
            </td>
        </tr>
        <tr>
        <th><label for="birthdate"><?php _e("Birth date"); ?></label></th>
            <td>
                <input type="date" name="birthdate" id="birthdate" value="<?php echo esc_attr( get_the_author_meta( 'lab_birthdate', $user->ID ) ); ?>" class="regular-text" /><br />
                <span class="description"><?php _e("Please enter your birthdate.", "lab"); ?></span>
            </td>
        </tr>
        </table>
      </td>
      <td>
        <label for="lab_upload_image"><?php _e("Picture of you"); ?></label>
        <input type="button" value="Upload Image" class="btn btn-info" id="lab_upload_image" userId="<?php echo get_current_user_id(); ?>"/>
        <input type="hidden" name="attachment_id" class="wp_attachment_id" id="attachment_id" value="" /> </br>
        
        <?php
          $imgId = get_the_author_meta( 'lab_user_picture_display', $user->ID );
          if ($imgId != NULL && !empty($imgId)) {
            echo wp_get_attachment_image($imgId, array('300', '300'), false, array("id"=>"lab_user_picture_display", "class"=>"lab_user_picture_img"));
          }
          else {
        ?>
            <img src="" class="image lab_user_picture_img" id="lab_user_picture_display" name="lab_user_picture_display" width="300" height="300"/>
        <?php
          }
        ?>
        
        <br>

        <input type="button " value="Delete Image" class="btn btn-danger" id="lab_user_picture_btn_delete_image"/>
      </td>
    </tr>
    
  </table>
<?php 
}

function lab_save_extra_user_profile_fields( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) ) { 
      return false; 
  }
  update_user_meta( $user_id, 'lab_birthdate' , $_POST['birthdate'] );
  update_user_meta( $user_id, 'lab_address1'  , $_POST['address1'] );
  update_user_meta( $user_id, 'lab_address2'  , $_POST['address2'] );
  update_user_meta( $user_id, 'lab_address3'  , $_POST['address3'] );
  update_user_meta( $user_id, 'lab_city'      , $_POST['city'] );
  update_user_meta( $user_id, 'lab_postalcode', $_POST['postalcode'] );
  update_user_meta( $user_id, 'lab_user_picture_display', $_POST['attachment_id'] );
}


function myplugin_load_textdomain() {
  load_plugin_textdomain( 'lab', false, '/lab/lang' ); 
}

add_action( 'user_register', 'myplugin_registration_save', 10, 1 );

function myplugin_registration_save( $user_id ) {
  lab_admin_add_new_user_metadata($user_id);
}

/**
 * Initialise le nouveau widget
 */
function wplab_init()
{
  $RewriteRules = new LabRewriteRules();
  register_widget("wplab_widget_week_event");
  register_widget("lab_hal_widget");
  add_filter('admin_init', array($RewriteRules, 'flush_rewrite_rules'));
  add_filter('rewrite_rules_array', array($RewriteRules, 'create_rewrite_rules'));
}
class LabRewriteRules {
  function create_rewrite_rules($rules) {
      global $wp_rewrite;
      $newRule = array('user/(.+)$' => 'index.php?pagename=user');
      $newRules = $newRule + $rules;
      $newRules = $newRule + $newRules;
      $newRule = array('invitation/(.+)$' => 'index.php?pagename=invitation');
      $newRules = $newRule + $newRules;
      $newRule = array('invite/(.+)$' => 'index.php?pagename=invite');
      $newRules = $newRule + $newRules;
      $newRule = array('mission/(.+)$' => 'index.php?pagename=mission');
      $newRules = $newRule + $newRules;
      return $newRules;
  }
  function flush_rewrite_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }
}

/**
 * Fonction de création du menu
 */
function wp_lab_menu()
{
  $bookings_num = '<span class="update-plugins count-'.lab_admin_mission_getNotifs(get_current_user_id(), null)[0]->notifs_number.'"><span class="plugin-count">'.lab_admin_mission_getNotifs(get_current_user_id(), null)[0]->notifs_number.'</span></span>';
  add_menu_page('Options'    , 'LAB'       , 'edit_plugins'      , 'wp-lab.php'          , 'wp_lab_option'       , ''                      , 21);
  add_menu_page("KeyRing"    ,"KeyRing"    ,'keyring'            , 'lab_keyring'         ,'lab_keyring'          ,'dashicons-admin-network',22);
  add_menu_page("Request"    ,esc_html__("Requests","lab"),'subscriber' , 'lab_request_view'         ,'lab_request_view','dashicons-format-chat',23);
  add_menu_page("Budget Info","Budget Info","budget_info_manager","lab_admin_budget_info","lab_admin_budget_info",'dashicons-money-alt'    ,24);
  add_menu_page("Mission"    ,"Manage Mission".$bookings_num, "subscriber","lab_admin_mission_manager","lab_admin_mission_manager",'dashicons-admin-site-alt'    ,23);
  add_submenu_page("lab_admin_budget_info", esc_html('Contract','lab'), esc_html('Contract','lab'),'edit_plugins', 'lab_admin_contract', 'lab_admin_new_contract', 25 );
  add_submenu_page("lab_admin_budget_info", esc_html('Contract funder','lab'), esc_html('Contract funder','lab'),'edit_plugins', 'lab_admin_contract_funder', 'lab_admin_contract_funder', 26 );
  //add_menu_page("LDAP Admin","LDAP Admin",'edit_plugins','lab_ldap','lab_ldap','dashicons-id-alt',23);
  add_submenu_page( 'wp-lab.php', "LDAP Admin", "LDAP Admin",'edit_plugins', 'lab_ldap', 'lab_ldap_test', 26 );
  add_submenu_page("wp-lab.php", "User Admin", "User Admin", "lab_user_manager", "lab_user", "lab_user_echo", 27);
  //add_submenu_page("wp-lab.php","Budget",'keyring','lab_admin_budget_info','lab_admin_budget_info','dashicons-money-alt',22);
  if ( ! current_user_can('edit_plugins') ) {
    remove_menu_page('wpfastestcacheoptions');
    remove_menu_page('options-general.php');
  }
}

/***********************************************************************************************
 * ADMINISTRATION
 **********************************************************************************************/

/**
 * Fonction qui permet de charger ce que l'on veut comme JS ou CSS dans l'administration
 **/
function admin_enqueue()
{
  wp_enqueue_script('media-upload');
  wp_enqueue_script('thickbox');
  wp_enqueue_style('thickbox');
  wp_enqueue_media();

  wp_enqueue_style('labCSS',plugins_url('css/lab.css',__FILE__), version_id(), true);
  wp_enqueue_script('fontAwesome',"https://kit.fontawesome.com/341f99cb81.js",array(),"3.2",true);
  wp_enqueue_script('SpectrumJS', plugins_url('js/spectrum.js',__FILE__), array('jquery','wp-i18n'), '1.8.0', true);
  wp_enqueue_style('SpectrumCSS',plugins_url('css/spectrum.css',__FILE__));
  wp_enqueue_style('bootstrap',plugins_url('css/bootstrap.css',__FILE__), version_id(), true);
  //Plugin permettant d'afficher les toasts :
  wp_enqueue_style('jqueryToastCSS',plugins_url('css/jquery.toast.css',__FILE__));
  wp_enqueue_style('InvitationCSS',plugins_url('css/lab-invitation.css',__FILE__), version_id(), true);
  wp_enqueue_script('jqueryToastJS',plugins_url('js/jquery.toast.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'),"1.3.2",false);

  wp_enqueue_script('lab-global', plugins_url('js/lab_global.js',__FILE__), array('jqueryToastJS', 'jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), version_id(), false);
  wp_enqueue_script('lab-admin', plugins_url('js/lab_admin.js',__FILE__), array('lab-global','jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), version_id(), false);
  wp_enqueue_script('lab-keyring',plugins_url('js/lab_keyring.js',__FILE__), array('lab-global','jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), version_id(), true);
  wp_enqueue_script('lab-budget',plugins_url('js/lab_budget.js',__FILE__), array('lab-global','jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), version_id(), true);
  wp_enqueue_script('lab-mission',plugins_url('js/lab_mission.js',__FILE__), array('lab-global','jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog', 'lab-budget'), version_id(), true);
  wp_enqueue_script('lab-contract',plugins_url('js/lab_contract.js',__FILE__), array('lab-global','jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog','lab-budget'), version_id(), true);
  //Feuille de style des interfaces d'administration WordPress :
  wp_enqueue_style('lab-admin',plugins_url('css/lab-admin.css',__FILE__), version_id(), true);
  wp_enqueue_style('LdapCSS',plugins_url('css/lab-ldap.css',__FILE__));
  //Feuille de style de l'onglet keyring :
  wp_enqueue_style('KeyRingCSS',plugins_url('css/keyring.css',__FILE__));
  //wp_enqueue_style('AdminCSS',plugins_url('css/lab-admin.css',__FILE__));
  //Plugin permettant d'afficher des fenêtres modales :
  wp_enqueue_style('jqueryModalCSS',plugins_url('css/jquery.modal.min.css',__FILE__));
  wp_enqueue_script('jqueryModalJS',plugins_url('js/jquery.modal.min.js',__FILE__),array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'),"0.9.1",false);
  wp_enqueue_script('jquery-ui-1.12.1-js', plugins_url('js/jquery-ui.min.js',__FILE__), array('jquery'), version_id(), false);
  wp_enqueue_script('lab-ldap', plugins_url('js/lab_ldap.js',__FILE__), array('jquery', 'wp-i18n'), version_id(), true);
  wp_enqueue_style('CountrySelectCSS',plugins_url('css/countrySelect.min.css',__FILE__));
  wp_enqueue_script('CountrySelectJS',plugins_url('js/countrySelect.min.js',__FILE__),array('jquery'),"3.5",false);
  wp_enqueue_style('TelInputCSS',plugins_url('css/intlTelInput.min.css',__FILE__));
  wp_enqueue_script('TelInputUtils',plugins_url('js/utils.js',__FILE__),array(),"3.4",false);
  wp_enqueue_script('TelInputJS',plugins_url('js/intlTelInput.min.js',__FILE__),array('TelInputUtils'),"3.4",false);
  wp_enqueue_script('lab-fe', plugins_url('js/lab_fe.js',__FILE__), array('jquery', 'wp-i18n', 'lab-global', 'TelInputJS'), version_id(), true);
  wp_enqueue_script('lab-mission', plugins_url('js/lab_mission.js',__FILE__), array('jquery', 'wp-i18n', 'lab-global', 'TelInputJS'), version_id(), true);
  localize_script('lab-global');
  wp_set_script_translations( 'lab-global' , 'lab', dirname(__FILE__).'/lang' );
  wp_set_script_translations( 'lab-admin'  , 'lab', dirname(__FILE__).'/lang' );
  wp_set_script_translations( 'lab-keyring', 'lab', dirname(__FILE__).'/lang' );
  wp_set_script_translations( 'lab-mission', 'lab', dirname(__FILE__).'/lang' );
}

/**
 * enqueues script for the frontend
 *
 * @return void
 */
function wp_lab_fe_enqueues()
{
  //wp_deregister_script('jquery');
	//wp_enqueue_script('jquery', plugins_url('js/jquery-3.5.1.min.js',__FILE__), array(), version_id(), false);
  wp_enqueue_style('labCSS',plugins_url('css/lab.css',__FILE__), version_id(), false);
  wp_enqueue_style('jqueryToastCSS',plugins_url('css/jquery.toast.css',__FILE__), version_id(), false);
  wp_enqueue_script('jqueryToastJS',plugins_url('js/jquery.toast.js',__FILE__), array('jquery'),version_id(),false);
  wp_enqueue_script('jquery-ui-1.12.1-js', plugins_url('js/jquery-ui.min.js',__FILE__), array('jquery'), version_id(), false);
  //wp_enqueue_script('lab-global', plugins_url('js/lab_global.js',__FILE__), array('jqueryToastJS', 'jquery'), version_id(), false);
  wp_enqueue_style('profileCSS',plugins_url('css/lab-profile.css',__FILE__));
  wp_enqueue_script('SpectrumJS', plugins_url('js/spectrum.js',__FILE__), array('jquery','wp-i18n'), '1.8.0', true);
  wp_enqueue_style('SpectrumCSS',plugins_url('css/spectrum.css',__FILE__));
  
  wp_enqueue_script('fontAwesome',"https://kit.fontawesome.com/341f99cb81.js",array(),"3.2",true);
  wp_enqueue_style('InvitationCSS',plugins_url('css/lab-invitation.css',__FILE__), version_id(), true);
  wp_enqueue_style('LdapCSS',plugins_url('css/lab-ldap.css',__FILE__));
  wp_enqueue_style('CountrySelectCSS',plugins_url('css/countrySelect.min.css',__FILE__));
  wp_enqueue_script('CountrySelectJS',plugins_url('js/countrySelect.min.js',__FILE__),array('jquery'),"3.5",false);
  wp_enqueue_style('TelInputCSS',plugins_url('css/intlTelInput.min.css',__FILE__));
  wp_enqueue_script('TelInputUtils',plugins_url('js/utils.js',__FILE__),array(),"3.4",false);
  wp_enqueue_script('TelInputJS',plugins_url('js/intlTelInput.min.js',__FILE__),array('TelInputUtils'),"3.4",false);

  wp_enqueue_script('lab-fe', plugins_url('js/lab_fe.js',__FILE__), array('jquery', 'wp-i18n', 'lab-global', 'TelInputJS'), version_id(), true);
  wp_enqueue_script('lab-mission', plugins_url('js/lab_mission.js',__FILE__), array('jquery', 'wp-i18n', 'lab-global'), version_id(), true);
  wp_enqueue_script('lab-global', plugins_url('js/lab_global.js',__FILE__), array('jqueryToastJS', 'jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog'), version_id(), false);
  wp_enqueue_script('lab-bootstrap', plugins_url('js/bootstrap.min.js',__FILE__), array('jquery'), version_id(), true);
  wp_enqueue_script('lab-shortcode-present',plugins_url('js/lab_shortcode_present.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog', 'lab-global', 'lab-bootstrap'), version_id(), false);

  localize_script('lab-fe');
  localize_script('lab-global');
  localize_script('lab-mission');
  
  wp_set_script_translations( 'lab-global' , 'lab', dirname(__FILE__).'/lang' );
  wp_set_script_translations( 'lab-fe', 'lab', dirname(__FILE__).'/lang' );
  wp_set_script_translations( 'lab-mission', 'lab', dirname(__FILE__).'/lang' );
  wp_set_script_translations( 'lab-fe', 'lab', dirname(__FILE__).'/lang' );

  //wp_enqueue_script('lab-shortcode-labo1dot5',plugins_url('js/lab_shortcode_labo1dot5.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog', 'lab-global', 'lab-bootstrap'), version_id(), false);
  //wp_enqueue_script('lab-shortcode-labo1dot5admin',plugins_url('js/lab_shortcode_labo1dot5admin.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog', 'lab-global', 'lab-bootstrap'), version_id(), false);
  //wp_enqueue_script('lab-shortcode-labo1dot5resp',plugins_url('js/lab_shortcode_labo1dot5resp.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog', 'lab-global', 'lab-bootstrap'), version_id(), false);
}

function localize_script($domain) {
  $js_vars = array();
  $schema = is_ssl() ? 'https':'http';
  $js_vars['ajaxurl'] = admin_url('admin-ajax.php', $schema);
  wp_localize_script($domain, 'LAB', apply_filters('lab_js_vars', $js_vars));
}

function lab_admin_tab_general_user()
{
?>
  <p>
    <a class="button button-primary button-large" href="?page=lab-export.php&post_type=epl_event&type=all">
        Export All
    </a>
  </p>
  <br>
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
