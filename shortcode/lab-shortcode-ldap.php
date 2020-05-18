<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.1
 */
class LAB_LDAP {
    /**
    * @var LAB_LDAP
    * @access private
    * @static
    */
    private static $_instance = null;
    private static $ldap_link;
    private static $ldap_admin_password = "aze";
    private static $ldap_url = "localhost";
    public const LDAP_BASE = "dc=i2m,dc=univ-amu,dc=fr";

    /**
     * Constructeur de la classe
    *
    * @param void
    * @return void
    */
    private function __construct() {
        $this->ldap_url;
        self::$ldap_link = ldap_connect(self::$ldap_url)
            or die ("URL du serveur LDAP incorrecte.");
        ldap_set_option(self::$ldap_link, LDAP_OPT_PROTOCOL_VERSION, 3);
    }
    /**
     * Méthode qui crée l'unique instance de la classe
    * si elle n'existe pas encore puis la retourne.
    *
    * @param void
    * @return Singleton
    */
    public static function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new LAB_LDAP();  
        }
    return self::$_instance;
    }
    public static function bindAdmin() {
        ldap_bind(self::$ldap_link,"cn=admin,".self::LDAP_BASE,self::$ldap_admin_password);
    }
    public static function getLink() {
        return self::$ldap_link;
    }
    public static function setURL($url) {
        self::$ldap_url=$url;
    }
    public static function setPassword($password) {
        self::$ldap_admin_password=$password;
    }
    public static function countAccountEntries() {
        $result = ldap_search(self::$ldap_link,'ou=accounts,'.self::LDAP_BASE,"uid=*");
        return ldap_count_entries(self::$ldap_link,$result);
    }
}
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
    //echo lab_ldap_addUser("JEAN EUDE", "Michel-Pierre","jemp@univ-amu.fr",'$P$B0v6kIJqQ.AN.VF.QxLmRyqAhvLOEt1',"i12345678",random_int(10000,11000),"TestOrg");
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
    $names = lab_ldap_getName($name);
    global $wpdb;
    $userData = array(
        'user_login'=>$uid,
        'user_pass'=>$password,
        'user_email'=>$email,
        'user_registered'=>date("Y-m-d H:i:s",time()),
        'first_name'=>$names['first_name'],
        'last_name'=>$names['last_name'],
        'display_name'=>$name,
        'role'=>'subscriber');
    $user_id = wp_insert_user($userData);
    $sql = "INSERT INTO ".$wpdb->prefix."usermeta 
        (`user_id`, `meta_key`, `meta_value`) VALUES
        ($user_id, 'mo_ldap_user_dn', 'uid=$uid,ou=accounts,dc=i2m,dc=univ-amu,dc=fr');";
    $wpdb->query($sql);
    //lab_admin_add_new_user_metadata($user_id);
}

function lab_ldap_addUser($first_name, $last_name,$email,$password,$uid,$organization="I2M") {
    $ldap_obj = LAB_LDAP::getInstance();
    $info["objectclass"][0] = "top";
    $info["objectclass"][1] = "person";
    $info["objectclass"][2] = "inetOrgPerson";
    $info["objectclass"][3] = "posixAccount";
    $info["objectclass"][4] = "shadowAccount";
    $info["objectclass"][5] = "organizationalPerson";
    $info["objectclass"][6] = "labeledURIObject";
    $info["cn"] = "$first_name $last_name";
    $info["loginshell"]="/bin/bash";
    $info["uid"]=$uid;
    $info["displayname"] = "$first_name $last_name";
    $info["sn"] = $last_name;
    $info["mail"]=$email;
    $info["uidnumber"]=3000+$ldap_obj::countAccountEntries();
    $info["userpassword"]="{CRYPT}".crypt($password,'$6$rounds=4000$NajlL3dRidV8SxW2$');
    $info["homedirectory"] = "/home/$uid";
    $info["gidnumber"] = "5000";
    $info["o"] = $organization;
    $ldap_obj::bindAdmin();
    ldap_add($ldap_obj::getLink(),"uid=$uid,ou=accounts,".$ldap_obj::LDAP_BASE,$info);
    if (ldap_errno($ldap_obj::getLink())==0) {
        return;
    } else {
        return ldap_errno($ldap_obj::getLink());
    }
}
?>