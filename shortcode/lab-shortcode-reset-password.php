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
        //echo '<pre>';
        //var_dump($data);
        //echo '</pre>';
        $email = lab_reset_password_get_email($login);
        
        $tokenData = uniqid() . time() . 'votre_secret'; // Combinaison unique
        $token = hash('sha256', $tokenData); // Crée le token
        
        lab_reset_password_send_mail($email,$url, $token, $login);
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

/**
 * Sends a password reset email to the specified email address.
 *
 * This function constructs an email with a password reset link and sends it to the user.
 * The email includes a tokenized URL for resetting the password.
 *
 * @param string $email The recipient's email address.
 * @param string $url The base URL for password reset.
 * @param string $token A token to include in the reset URL for authentication.
 *
 * @return void
 */

function lab_reset_password_send_mail($email,$url, $token, $uid) {

    // Paramètres de l'email
    $destinataire = $email; // Adresse email du destinataire
    $sujet = "[Informatique] Réinitialisation de mot de passe / Reset password"; // Sujet de l'email
    $message = "Ceci est un email généré automatiquement.<br/>
    <p>Bonjour,</p>
    <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
    <p>Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :</p>
    <p><a href='$url?token=$token'>Réinitialiser le mot de passe</a></p>";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Envoi de l'email
    if (wp_mail($destinataire, $sujet, $message, $headers)) {
        lab_reset_password_add_token_to_db($token, $uid);
    }
    echo "Un email a été envoyé à votre adresse avec un lien de réinitialisation de mot de passe.";
}

function lab_reset_password_add_token_to_db($token, $uid) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lab_reset_password';
    $token = $_POST['token'];
    $login = $_POST['login'];
    $expiration_time = time() + 3600; // 1 heure d'expiration

    // Insertion du token dans la base de données
    $wpdb->insert(
        $table_name,
        array(
            'token' => $token,
            'uid' => $uid,
            'date' => current_time('mysql'),
        )
    );   
}

/**
 * Renvoie l'email correspondant à un uid si l'utilisateur existe, null sinon.
 *
 * @param string $uid L'uid de l'utilisateur.
 *
 * @return string|null L'email de l'utilisateur, null si l'utilisateur n'existe pas.
 */
function lab_reset_password_get_email($uid) {
    $ldap_obj = LAB_LDAP::getInstance(
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
        true
      );
    $user_datas = $ldap_obj->get_map_info_from_uid($uid);

    if($user_datas != null) {
        $email = $user_datas['mail'];
        //echo "Email correspondant : " . $email;
        return $email;
    } else {
        //echo "Aucun utilisateur trouvé avec cet email.";
        return null;
    }
}
?>