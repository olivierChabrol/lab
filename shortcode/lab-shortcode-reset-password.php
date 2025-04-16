<?php
/*** 
 * Shortcode use : [lab-reset-password]
     load="yes" OR load="no"
     debug="yes" OR debug="no"
***/ 
function lab_reset_password($atts) {
    $atts = shortcode_atts(array(
        'load'    => get_option('lab-cirm'),
        'debug'    => get_option('lab-cirm'),
    ), $atts, "lab-cirm");
    //var_dump($atts);
    global $wp;
    $url = home_url( $wp->request );
    $debug = false;
    if (isset($atts['debug'])) {
        if ($atts['debug'] == 'yes') {
            $debug = true;
        }
    }
    if ( 'POST' !== $_SERVER['REQUEST_METHOD']) {
         echo "<h3>Pas de données envoyées</h3><br/>";
    }
    else {
        echo "<h3>Données envoyées</h3><br/>";
        $login = $_POST['login'];
        $url = $url;
        $data = array(
            'login' => $login,
        );
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        lab_reset_password_get_email($login);
        
    }

    $token = '';
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
    }
    echo '<div class="lab-reset-password">';
    if ($token == '') {
        echo '<h2>Demande de réinitialisation de mot de passe</h2>';
        echo '<form method="post" action="'.$url.'">';
        echo '<input type="text" name="login" placeholder="login" required>';
        echo '<button type="submit">Envoyer</button>';
        echo '</form>';
        echo '<p>Un email vous a été envoyé avec un lien de réinitialisation de mot de passe.</p>';
        echo '<p>Si vous ne recevez pas d\'email, vérifiez votre dossier spam ou contactez votre administrateur.</p>';
    }
    else {
        echo '<h2>Reinitialisation du mot de passe</h2>';
        echo '<form method="post" action="https://app.cirm-math.fr/api/auth/reset-password">';
        echo '<input type="hidden" name="token" value="' . $token . '"><br/>';
        echo '<input type="password" name="password" placeholder="Nouveau mot de passe" required>';
        echo '<input type="password" name="password_confirmation" placeholder="Confirmer le mot de passe" required>';
        echo '</form>';
        echo '<p>Un email vous a été envoyé avec un lien de réinitialisation de mot de passe.</p>';
        echo '<p>Si vous ne recevez pas d\'email, vérifiez votre dossier spam ou contactez votre administrateur.</p>';

    }
        
}

function lab_reset_password_get_email($uid) {
    /*
    $ldapServer = "ldap://ldap.i2m.univ-amu.fr"; // Adresse de votre serveur LDAP
    $ldapAdminDn = "admin";            // DN de l'administrateur
    $ldapAdminPassword = "d5u.4SR:"; // Mot de passe de l'administrateur

    // Connexion au serveur LDAP
    $ldapconn = ldap_connect($ldapServer) or die("Impossible de se connecter au serveur LDAP.");

    if ($ldapconn) {
        // Authentification
        $ldapbind = ldap_bind($ldapconn, $ldapAdminDn, $ldapAdminPassword);
        
        if ($ldapbind) {
            // Recherche LDAP avec un filtre basé sur l'email
            $baseDn = "dc=i2m,dc=univ-amu,dc=fr"; // Remplacez par le DN de base de votre annuaire
            $searchFilter = "(uid=$uid)";
            
            $searchResult = ldap_search($ldapconn, $baseDn, $searchFilter);
            $entries = ldap_get_entries($ldapconn, $searchResult);
            
            if ($entries["count"] > 0) {
                $email = $entries[0]["email"][0]; // Récupérer le premier 'uid' trouvé
                echo "Email correspondant : " . $email;
            } else {
                echo "Aucun utilisateur trouvé avec cet email.";
            }
        } else {
            echo "Échec de l'authentification.";
        }
        ldap_close($ldapconn);
    }
    //*/
    $ldap_obj = LAB_LDAP::getInstance(
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
        true
      );
    $user_datas = $ldap_obj->get_info_from_uid($uid);
    if($user_datas != null) {
        $email = $user_datas['mail'][0];
        echo "Email correspondant : " . $email;
    } else {
        echo "Aucun utilisateur trouvé avec cet email.";
    }
}
?>