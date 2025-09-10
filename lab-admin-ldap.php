<?php

/**************************************************************************************************************
 * LDAP
 * 
 * lab_ldap_list_update($lc,$BASE) => function which displays all the "cn"s from LDAP.
 * 
 * get_ldap_data_from_mail($mail) => function which return in an array the surname, name, login and mail of
 *  a requested person from LDAP. It needs the mail of the person to work.
 * 
 **************************************************************************************************************/
function lab_ldap_getName($cn)
{
    $list = explode(" ", $cn);
    $i = 0;
    foreach ($list as $elem) {
        if (preg_match("/[a-z]/", substr($elem, -1, 1))) {
            $last_name = implode(" ", array_slice($list, 0, $i));
            $first_name = implode(" ", array_slice($list, $i, (count($list) - $i)));
        } else {
            $i++;
        }
    }
    return array('first_name' => $last_name, 'last_name' => $first_name);
}


function quickSort(&$array, $compare, $start, $end)
{
    $partition = function (&$array, $start, $end) use (&$partition, $compare) {
        if ($start >= $end) {
            return;
        }
        $pivot = $array[$start];
        $left = $start;
        $right = $end;
        while ($left <= $right) {
            while ($compare($array[$left], $pivot) < 0) {
                $left += 1;
            }
            while ($compare($array[$right], $pivot) > 0) {
                $right -= 1;
            }
            if ($left > $right) {
                break;
            }
            list($array[$left], $array[$right]) = [$array[$right], $array[$left]];
            $left += 1;
            $right -= 1;
        }
        $partition($array, $start, $right);
        $partition($array, $left, $end);
    };

    $partition($array, $start, $end);
}


function ldapSort(array &$entries, $key)
{
    $SORT_KEY = 'SortValue';

    $key = strtolower($key);

    for ($i = 0; $i < $entries['count']; $i++) {
        $entry = &$entries[$i];
        $attributes = array_change_key_case($entry, CASE_LOWER);

        $entry[$SORT_KEY] = isset($attributes[$key][0]) ?
            $attributes[$key][0] : null;
    }
    unset($entry);

    quickSort(
        $entries,
        function ($a, $b) use ($SORT_KEY) {
            $a = $a[$SORT_KEY];
            $b = $b[$SORT_KEY];
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        },
        0, // start
        $entries['count'] - 1 // end
    );
}

class LAB_LDAP
{
    /**
     * @var LAB_LDAP
     * @access private
     * @static
     */
    private $ldap_url;
    private $base;
    private $ldap_admin_login;
    private $ldap_admin_password;
    private static $_instance = [];
    private $ldap_link;

    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct($url, $base, $login, $password)
    {
        $this->ldap_url = $url;
        $this->base = $base;
        $this->ldap_admin_login = $login;
        $this->ldap_admin_password = $password;
        $this->ldap_link = ldap_connect($this->ldap_url)
            or die("URL du serveur LDAP incorrecte : " . $this->ldap_url);
        ldap_set_option($this->ldap_link, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap_link, LDAP_OPT_REFERRALS, 0);
        ldap_start_tls($this->ldap_link);
    }

    public function reconnect()
    {
        $this->close();
        $this->ldap_link = ldap_connect($this->ldap_url)
            or die("URL du serveur LDAP incorrecte.");
        ldap_set_option($this->ldap_link, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    public function close()
    {
        if ($this->ldap_link) {
            ldap_close($this->ldap_link);
        }
    }

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return LAB_LDAP
     */
    public static function getInstance($url = null, $base = null, $login = null, $password = null, $forceReload = false)
    {

        $cls = static::class;
        if (!isset(self::$_instance[$cls]) || $forceReload) {
            if (isset(self::$_instance[$cls])) {
                self::$_instance->close();
            }
            self::$_instance[$cls] = new LAB_LDAP($url, $base, $login, $password);
        }

        return self::$_instance[$cls];
        /*
        if(is_null(self::$_instance) || $forceReload) {
            if ($forceReloadi && self::$_instance != null) {
                self::$_instance->close();
            }

            if ($url == null || $base == null || $login == null || $password== null) {
                //echo "Please Fill all LDAP params";
                return;
            } else {
                self::$_instance = new LAB_LDAP($url, $base, $login, $password);
            }
        }
    return self::$_instance;
    //*/
    }
    public function bindAdmin()
    {
        return ldap_bind($this->ldap_link, $this->ldap_admin_login . "," . $this->base, $this->ldap_admin_password);
    }
    public function getLink()
    {
        return $this->ldap_link;
    }
    public function getBase()
    {
        return $this->base;
    }
    public function setBase($base)
    {
        $this->base = $base;
    }
    public function getPassword()
    {
        return $this->ldap_admin_password;
    }
    public function setPassword($password)
    {
        $this->ldap_admin_password = $password;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function setHost($host)
    {
        $this->host = $host;
    }
    public function getURL()
    {
        return $this->ldap_url;
    }
    public function setURL($url)
    {
        $this->ldap_url = $url;
    }
    public function getLogin()
    {
        return $this->ldap_admin_login;
    }
    public function setLogin($login)
    {
        $this->ldap_admin_login = $login;
    }
    public function countResults($result)
    {
        return ldap_count_entries($this->ldap_link, $result);
    }

    public function get_ldap_data_from_mail($mail)
    {
        $filter    = "(mail=" . $mail . ")";
        $attrRead  = array("givenname", "sn", "mail", "uid");
        $result    = ldap_search($this->ldap_link, $this->base, $filter, $attrRead)
            or die("Error in query");
        $entry     = ldap_get_entries($this->link, $result);

        $surname = $entry[0]["sn"][0];
        $name    = $entry[0]["givenname"][0];
        $login   = $entry[0]["uid"][0];

        return array($surname, $name, $login);
    }

    function editUser($uid, $givenName, $sn, $uidNumber, $homeDirectory, $mail)
    {
        $info                  = array();
        $info["uid"]           = $uid;
        $info["givenname"]     = $givenName;
        $info["sn"]            = $sn;
        $info["uidnumber"]     = $uidNumber;
        $info["homedirectory"] = $homeDirectory;
        $info["mail"]          = $mail;
        $res = ldap_mod_replace($this->ldap_link, "uid=$uid,ou=accounts," . $this->base, $info);
    }

    public function get_ldap_data_from_uid($uid)
    {
        $filter    = "(uid=" . $uid . ")";
        $attrRead  = array("givenname", "sn", "mail", "uid");
        $result    = ldap_search($this->ldap_link, $this->base, $filter, $attrRead)
            or die("Error in query");
        $entry     = ldap_get_entries($this->ldap_link, $result);

        $surname = $entry[0]["sn"][0];
        $name    = $entry[0]["givenname"][0];
        $login   = $entry[0]["uid"][0];

        return array($surname, $name, $login);
    }

    /**
     * Search and sort over all uids
     *
     * @return ldapresult
     */
    public function searchAccounts($uid = "*")
    {
        $result = ldap_search($this->ldap_link, 'ou=accounts,' . $this->base, "uid=" . $uid);
        //ldap_sort($this->ldap_link,$result,'cn');
        //$entries = ldap_get_entries($this->ldap_link, $result);
        //ldapSort($entries, 'cn');
        return $result;
    }
    public function searchBy($filter, $attr)
    {
        return ldap_search($this->ldap_link, $this->base, $filter, $attr);
    }

    public function search($dn, $filter)
    {
        return ldap_search($this->ldap_link, "$dn," . $this->base, $filter);
    }
    public function getEntries($result, $i, $field)
    {
        return ldap_get_entries($this->ldap_link, $result)[$i][$field][0];
    }

    public function list_entries($result)
    {
        return ldap_get_entries($this->ldap_link, $result);
    }

    public function ldap_search($filter)
    {
        $attrRead  = array("givenname", "sn", "mail", "uid", "password");
        return ldap_search($this->ldap_link,  $this->base, $filter, $attrRead);
    }

    /**
     * Modify an existing LDAP entry
     *
     * @param array $info Associative array of values to replace in the LDAP entry
     * @return int Result of the operation
     */
    public function ldap_mod_replace($dn, $info)
    {
        $r = ldap_mod_replace($this->ldap_link, $dn, $info);
        if ($r === false) {
            echo "ERREUR LDAP : ".ldap_errno($this->ldap_link)."<br/>";
        }
        return $r;
    }

    public function modify($user_dn, $entry) {
        $this->bindAdmin();
        $result = ldap_modify($this->ldap_link, $user_dn, $entry);
        if ($result === false) {
            echo "ERREUR LDAP : ".ldap_errno($this->ldap_link)."<br/>";
        }
        return $result;
    }

    public function get_info_from_mail($mail)
    {
        $filter    = "(mail=" . $mail . ")";
        $attrRead  = array("givenname", "sn", "mail", "uid", "password", "homedirectory");
        $result    = ldap_search($this->ldap_link, $this->base, $filter, $attrRead)
            or die("Error in query");
        $entry     = ldap_get_entries($this->ldap_link, $result);

        $surname  = $entry[0]["givenname"][0];
        $name     = $entry[0]["sn"][0];
        $email    = $entry[0]["mail"][0];
        $password = $entry[0]["password"][0];
        $uid      = $entry[0]["uid"][0];

        return array("lastname" => $name, "firstname" => $surname, "mail" => $email, "password" => $password, "uid" => $uid);
    }
    public function get_info_from_uid($uid)
    {
        $filter    = "(uid=" . $uid . ")";
        $attrRead  = array("givenname", "sn", "mail", "uid", "uidnumber", "homedirectory");
        $result    = ldap_search($this->ldap_link, $this->base, $filter, $attrRead)
            or die("Error in query");
        $entry     = ldap_get_entries($this->ldap_link, $result);
        if ($entry["count"] == 0) {
            return null;
        }

        $surname = $entry[0]["sn"][0];
        $name    = $entry[0]["givenname"][0];
        $email   = $entry[0]["mail"][0];
        $uidNumber = $entry[0]["uidnumber"][0];
        $homeDirectory = $entry[0]["homedirectory"][0];

        return array($name, $surname, $email, $uidNumber, $homeDirectory);
    }

    public function get_map_info_from_uid($uid)
    {
        $array = $this->get_info_from_uid($uid);
        if ($array == null) {
            return null;
        }
        $info = array(
            "uid" => $uid,
            "lastname" => $array[0],
            "firstname" => $array[1],
            "mail" => $array[2],
            "uidnumber" => $array[3],
            "homedirectory" => $array[4]
        );
        return $info;
    }

    public function addEntry($path, $fields)
    {
        $this->bindAdmin();
        error_log("[addEntry] base : " . $this->base);
        //error_log("[addEntry] bind : " . print_r($b, true));
        ldap_add($this->ldap_link, $path . "," . $this->base, $fields);
        error_log("[addEntry] ldap_add" . $path . "," . $this->base . " / " . print_r($fields, true));

        //        ldap_add($this->ldap_link,"$path,".$this->base,$fields);
        return ldap_errno($this->ldap_link);
    }
    public function deleteEntry($path)
    {
        $this->bindAdmin();
        ldap_delete($this->ldap_link, "$path," . $this->base);
        return ldap_errno($this->ldap_link);
    }
}
function lab_ldap_new_WPUser($lastname, $firstname, $email, $password, $uid)
{
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM `" . $wpdb->prefix . "users` WHERE `user_login`='$uid'";
    if ($wpdb->get_var($sql) > 0) {
        return "WP User already exists";
    }
    $ldap = LAB_LDAP::getInstance();
    $userData = array(
        'user_login' => $uid,
        'user_pass' => substr($password, 0, 7) == '{CRYPT}' ? 'hahaha' : substr($password, 0, 7),
        'user_email' => $email,
        'user_registered' => date("Y-m-d H:i:s", time()),
        'first_name' => $firstname,
        'last_name' => $lastname,
        'display_name' => $firstname . " " . $lastname,
        'role' => 'subscriber'
    );
    $user_id = wp_insert_user($userData);
    lab_admin_add_new_user_metadata($user_id);
    $sql = "INSERT INTO " . $wpdb->prefix . "usermeta 
        (`user_id`, `meta_key`, `meta_value`) VALUES
        ($user_id, 'mo_ldap_user_dn', 'uid=$uid,ou=accounts," . $ldap->getBase() . "');";
    if ($wpdb->query($sql) === false) {
        return $wpdb->last_error;
    }
    return true;
    //lab_admin_add_new_user_metadata($user_id);
}


/**
 * Recherche le plus grand uidNumber présent dans l'annuaire.
 * 
 * @param LAB_LDAP $ldap_obj Instance de la classe LAB_LDAP
 * 
 * @return int Le plus grand uidNumber + 1
 * 
 * @throws Exception Si la recherche LDAP échoue
 * @throws Exception Si aucun uidNumber n'est trouvé dans l'annuaire
 */
function getMaxUidNumber($ldap_obj) {

    // Recherche de tous les uidNumber
    $filter = "(uidNumber=*)";
    $attributes = ["uidNumber"];
    $result = ldap_search($this->ldap_link, 'ou=accounts,' . $this->base, $filter, $attributes);

    if (!$result) {
        throw new Exception("Échec de la recherche LDAP avec le filtre $filter");
    }

    $entries = ldap_get_entries($this->ldap_link, $result);

    $maxUid = -1;
    for ($i = 0; $i < $entries["count"]; $i++) {
        if (isset($entries[$i]["uidnumber"][0])) {
            $uid = intval($entries[$i]["uidnumber"][0]);
            if ($uid > $maxUid) {
                $maxUid = $uid;
            }
        }
    }


    if ($maxUid === -1) {
        throw new Exception("Aucun uidNumber trouvé dans l'annuaire.");
    }

    return $maxUid + 1;
}


function lab_ldap_addUser($ldap_obj, $first_name, $last_name, $email, $password, $uid, $organization = "I2M")
{
    //$ldap_obj = LAB_LDAP::getInstance();
    $info["objectclass"][0] = "top";
    $info["objectclass"][1] = "person";
    $info["objectclass"][2] = "inetOrgPerson";
    $info["objectclass"][3] = "posixAccount";
    $info["objectclass"][4] = "shadowAccount";
    $info["objectclass"][5] = "organizationalPerson";
    $info["objectclass"][6] = "labeledURIObject";
    $info["cn"] = "$first_name $last_name";
    $info["loginshell"] = "/bin/bash";
    $info["uid"] = $uid;
    $info["displayname"] = "$first_name $last_name";
    $info["givenName"] = $first_name;

    $info["sn"] = $last_name;
    $info["mail"] = $email;
    $info["uidnumber"] = getMaxUidNumber($ldap_obj);
    //3000 + $ldap_obj->countResults($ldap_obj->searchAccounts());
    if (substr($password, 0, 7) == '{CRYPT}') {
        $info["userpassword"] = $password;
    } else {
        $info["userpassword"] = "{CRYPT}" . crypt($password, '$6$rounds=4000$NajlL3dRidV8SxW2$');
    }
    $info["homedirectory"] = "/home/" . usermeta_format_name_to_slug($first_name, $last_name);
    $info["gidnumber"] = "5000";
    if (strlen($organization) > 0) {
        $info["o"] = $organization;
    }
    $res1 = $ldap_obj->addEntry("uid=$uid,ou=accounts", $info);
    if ($res1 === 0) {
        $info2['objectclass'] = 'automount';
        $info2['cn'] = usermeta_format_name_to_slug($first_name, $last_name);
        $info2['automountinformation'] = 'olympe:/mnt/newpool/COMPTES/' . usermeta_format_name_to_slug($first_name, $last_name);
        return $ldap_obj->addEntry("cn=" . usermeta_format_name_to_slug($first_name, $last_name) . ",ou=auto.home", $info2);
    }
    return $res1;
}

function ldap_delete_user($ldap_obj, $uid)
{
    //$ldap_obj=LAB_LDAP::getInstance();
    $home = explode("/", $ldap_obj->getEntries($ldap_obj->search("ou=accounts", "uid=$uid"), 0, "homedirectory"))[2];
    $homeEntry = (ldap_get_entries($ldap_obj->getLink(), $ldap_obj->search("ou=auto.home", "cn=$home")));
    $errorNo = 0;
    if ($homeEntry['count'] > 0) {
        //echo "found home entry, deleting...";
        $ldap_obj->deleteEntry("cn=$home,ou=auto.home");
        $errorNo = ldap_errno($ldap_obj->getLink());
    }
    if ($errorNo == 0) {
        $ldap_obj->deleteEntry("uid=$uid,ou=accounts");
        $errorNo = ldap_errno($ldap_obj->getLink());
    }
    return $errorNo;
}
