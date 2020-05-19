<?php
/**************************************************************************************************************
 * LDAP
 * 
 * lab_ldap_list_update($lc,$BASE) => function which displays all the "cn"s from LDAP.
 * 
 * get_ldap_data_from_mail($mail) => function which return name, surname, login and mail of
 *  a requested person from LDAP. It needs the mail of the person to work.
 * 
 **************************************************************************************************************/
function lab_ldap_pagination_Req() {
  wp_send_json_success(lab_ldap_pagination($_POST['pages'],$_POST['currentPage']));
}
function lab_ldap_list_update($lc,$BASE) {
  $value = isset($_POST['value']) ? $_POST['value'] : '5' ;
  $page = isset($_POST['page']) ? $_POST['page'] : '1' ;
  $pageVar = $page - 1;
  $result = ldap_search($lc,'ou=accounts,'.$BASE,"uid=*");
  ldap_sort($lc,$result,'cn');
  for($i = $pageVar; $i < $value; ++$i)
  {
    $ldapResult .= '<tr><td>'. ldap_get_entries($lc,$result)[$i]["cn"][0].'</td>
                    <td>*</td>
                </tr>';
  }
  return($ldapResult);
}

function get_ldap_data_from_mail($mail) {
  $lc        = ldap_connect("localhost","389");
  $base      = "ou=accounts,dc=i2m,dc=univ-amu,dc=fr";
  $filter    = "(mail=" . $mail . ")";
  $attrRead  = array("givenname", "sn", "mail", "uid");
  $result    = ldap_search($lc, $base, $filter, $attrRead) 
      or die ("Error in query");
  $entry     = ldap_get_entries($lc,$result);

  echo $entry[0]["sn"][0]        . " est le nom de famille recherché via mail.</br>" .
       $entry[0]["givenname"][0] . " est le prénom recherché via mail.</br>" .
       $entry[0]["uid"][0]       . " est le login recherché via mail.";
  ldap_close($lc);
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

class LAB_LDAP {
    /**
    * @var LAB_LDAP
    * @access private
    * @static
    */
    private static $_instance = null;
    private $ldap_link;
    private $ldap_admin_password = "aze";
    private $ldap_url = "localhost";
    public const LDAP_BASE = "dc=i2m,dc=univ-amu,dc=fr";

    /**
    * Constructeur de la classe
    *
    * @param void
    * @return void
    */
    private function __construct() {
        $this->ldap_url;
        $this->ldap_link = ldap_connect(self::$ldap_url)
            or die ("URL du serveur LDAP incorrecte.");
        ldap_set_option(self::$ldap_link, LDAP_OPT_PROTOCOL_VERSION, 3);
        self::$ldap_url = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_URL);
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
  if ($wpdb->query($sql)===false) {
      return $wpdb->last_error;
  }
  return true;
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
    $info["homedirectory"] = "/home/".usermeta_format_name_to_slug($first_name,$last_name);
    $info["gidnumber"] = "5000";
    $info["o"] = $organization;
    $ldap_obj::bindAdmin();
    $res1 = ldap_add($ldap_obj::getLink(),"uid=$uid,ou=accounts,".$ldap_obj::LDAP_BASE,$info);
    if ($res1 === true) {
        $info2['objectclass']='automount';
        $info2['cn'] = usermeta_format_name_to_slug($first_name,$last_name);
        $info2['automountinformation']='olympe:/mnt/newpool/COMPTES/'.usermeta_format_name_to_slug($first_name,$last_name);
        ldap_add($ldap_obj::getLink(),"cn=".usermeta_format_name_to_slug($first_name,$last_name).",ou=auto.home,".$ldap_obj::LDAP_BASE,$info2);
        return ldap_errno($ldap_obj::getLink());
    }
    return ldap_errno($ldap_obj::getLink());
}