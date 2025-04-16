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
    $ldap_obj = LAB_LDAP::getInstance(
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
        true
      );
    $user_datas = $ldap_obj->get_map_info_from_uid($uid);
    var_dump($user_datas);
    if($user_datas != null) {
        $email = $user_datas['mail'];
        echo "Email correspondant : " . $email;
    } else {
        echo "Aucun utilisateur trouvé avec cet email.";
    }
}
?>