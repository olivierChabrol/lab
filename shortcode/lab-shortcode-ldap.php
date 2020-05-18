<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti, Astrid Beyer
 * Version: 0.1
 * 
 * TODO : à partir d'un e-mail donné -> ramener les champs pour la fonction d'Ivan : 
 *  nom, prénom, mail, login (uid)
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
    $BASE = "dc=i2m,dc=univ-amu,dc=fr";
    $lc = ldap_connect("localhost","389")
        or die ("Impossible de se connecter au serveur LDAP.");
    ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION,3);
    $lb = ldap_bind($lc, 'cn=admin,'.$BASE,'root');

    /* **** CONNEXION TEST - SUCCESS! [do not erase if you want to test] ****
    if($lb) {
        echo("Connecté avec succès ! ");
    } else {
        $errname = ldap_error($lc);
        $errno   = ldap_errno($lc);
        echo "Problème à la connexion : " . $errno . " - " . $errname . "</br>";
    }
    */

    //TODO : pagination, bouton détail (récupère attributs LDAP)
    $ldapStr = '
    <div>    
        <label for="lab_results_number">'.esc_html__("Nombre de résultats par page","lab").' : </label>
        <select id="lab_results_number">
            <option selected value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
    </div>
    <div class="table-responsive">
        <table id="lab-table-directory" class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>'.esc_html__("Nom", "lab").'</th>
                    <th>'.esc_html__("Action", "lab").'</th>
                <tr>
            </thead>
        <tbody id="lab_ldapListBody">';
    
    //$ldapStr .= lab_ldap_list_update($lc,$BASE);

    $ldapStr .= '           
        </tbody>
    </table>
    </div>';

    $ldapStr .= '<div id="lab_pages">'.lab_ldap_pagination(1,1).'</div><br/><br/>';
        
    return $ldapStr;

}
function lab_ldap_pagination($pages, $currentPage) {
    $out = '<ul id="pagination-digg">';
    $out .= '<li class="page_previous'.($currentPage>1 ? '">' : ' gris">').'« Précédent</li>';
    if($pages <= 10)
    {
        for ($i=1; $i<=$pages; $i++) {
            $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
        }
    }
    else
    {
        for ($i=1; $i<=$pages; $i++) {
            if($i == $currentPage -2 || $i == $currentPage -1 || $i == $currentPage || $i == $currentPage +1 || $i == $currentPage +2){
                $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
            }
            else{
                if($i % 10 == 0 || $i == 1 || $i == $pages) {
                    $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
                }

            }
        }
    }
    $out .= '<li class="page_next'.($pages>1 && $currentPage<$pages ? '">' : ' gris">').'Suivant »</li>';
    $out .= '</ul>';
    return $out;
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

function lab_ldap_addUser($first_name, $last_name,$email,$password,$uid,$organization) {
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
    $info["userpassword"]=$password;
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