<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.1
 */
function lab_ldap($args) {
    $active_tab = 'new';
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">LDAP</h1>
        <hr class="wp-header-end">
        <h2 class="nav-tab-wrapper">
        <a id="lab_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'new' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'new'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Nouvel utilisateur','lab'); ?></a>
        <a id="laib_users_settings_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'edit' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'edit'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Modifier utilisateur','lab'); ?></a>
        </h2>
        <table style="width:100%;">
        <tr>
            <td style="width:65%;vertical-align:top;" id="configurationForm">
            <?php
            if ($active_tab == 'user_settings') {
                lab_admin_tab_user();
            } else if ($active_tab == 'user_general_settings') { 

            }
            ?>
            </td>
        </tr>
    </table>
    </div>
    <?php
    $BASE = "dc=i2m,dc=univ-amu,dc=fr";
    $ldap_link = ldap_connect("localhost");
    $ldap_bind = ldap_bind($ldap_link, 'cn=admin,'.$BASE,'aze');
    // var_dump($ldap_bind);
    // $result = ldap_search($ldap_link,'ou=accounts,'.$BASE,"uid=chabrol");
    // echo "<xmp>";
    // var_dump(ldap_get_entries($ldap_link,$result)[0]);
    // echo "</xmp>";
}

function lab_ldap_getName($cn) {
    $list = explode(" ",$cn);
    $i = 0;
    foreach($list as $elem) {
        if (preg_match("/[a-z]/",substr($elem,-1,1)) ) {
            $last_name=implode(" ",array_slice($list,0,$i));
            $first_name=implode(" ",array_slice($list,$i,(count($list)-$i)));
        } else {
            $i++;
        }
    }
    return array('first_name'=>$first_name,'last_name'=>$last_name);
}

function lab_ldap_new_WPUser($name,$email,$password,$uid) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix."users",
    array(
        'user_login'=>$uid,
        'user_pass'=>$password,
        'user_nicename'=>$uid,
        'user_email'=>$email,
        'user_registered'=>date("Y-m-d H:i:s",time()),
        'user_status'=>0,
        'display_name'=>$name
    ));
    $user_id = $wpdb->insert_id;
    $names = lab_ldap_getName($name);
    $sql = "INSERT INTO ".$wpdb->prefix."usermeta 
        ('user_id', 'meta_key', 'meta_value') VALUES
        ($user_id, 'first_name', '".$names['first_name']."'),
        ($user_id, 'last_name', '".$names['last_name']."'),
        ($user_id, 'nickname', '".$names['first_name']."'),
        ($user_id, 'mo_ldap_user_dn', 'uid=$uid,ou=accounts,dc=i2m,dc=univ-amu,dc=fr'),
        ($user_id, 'wp_user_level', '0'),
        ($user_id, 'wp_capabilities', 'a:1:{s:10:\"subscriber\";b:1;}'),
        ($user_id, 'use_ssl', '0'),
        ($user_id, 'admin_color', 'fresh'),
        ($user_id, 'comment_shortcuts', 'false'),
        ($user_id, 'syntax_highlighting', 'true'),
        ($user_id, 'rich_editing', 'true'),
        ($user_id, 'show_admin_bar_front', 'true'),
        ($user_id, 'description', ''),
        ($user_id, 'locale', ''),
        ($user_id, 'dismissed_wp_pointers', ''),
        ($user_id, 'dbem_phone', ''),
        ($user_id, 'lab_profile_bg_color', '#67afe6'),
        ($user_id, 'lab_hal_name', '".hal_format_name($name)."'),
        ($user_id, 'lab_facebook', ''),
        ($user_id, 'lab_linkedin', ''),
        ($user_id, 'lab_pinterest', ''),
        ($user_id, 'lab_tumblr', ''),
        ($user_id, 'lab_twitter', ''),
        ($user_id, 'lab_instagram', ''),
        ($user_id, 'lab_youtube', ''),
        ($user_id, 'lab_user_left', NULL),
        ($user_id, 'lab_user_phone', ''),
        ($user_id, 'lab_user_office_floor', ''),
        ($user_id, 'lab_user_office_number', ''),
        ($user_id, 'lab_user_position', ''),
        ($user_id, 'lab_user_slug', '".usermeta_format_name_to_slug($names['first_name'], $names['last_name'])."')";
    $wpdb->query($sql);
}
?>