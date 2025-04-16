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
         //echo "<h3>Pas de données envoyées</h3><br/>";
    }
    else {
        echo "<h3>Données envoyées</h3><br/>";
        if (isset($_POST['login'])) {
            $login = $_POST['login'];
            $url = $url;
            $data = array(
                'login' => $login,
            );
            $email = lab_reset_password_get_email($login);
            
            $tokenData = uniqid() . time() . 'votre_secret'; // Combinaison unique
            $token = hash('sha256', $tokenData); // Crée le token
            
            lab_reset_password_send_mail($email,$url, $token, $login);
        }
        if (isset($_POST['password'])) {
            echo "<h4>Nouveau mdp</h4><br/>";
            $password = $_POST['password'];
            $password_confirmation = $_POST['password_confirmation'];
            $token = $_POST['token'];
            if ($password == $password_confirmation) {
                echo "Meme mdp<br/>";
                $uid = lab_reset_password_get_uid_from_token($token);
                echo "UID : $uid <br/>";
                if ($uid == null) {
                    echo "Token invalide ou expiré.";
                    return;
                }
                lab_reset_password_reset_ldap_password($token, $password);
            }
            else {
                echo "Les mots de passe ne correspondent pas.";
            }

        }
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
        echo '<form method="post" action="'.$url.'">';
        echo '<input type="hidden" name="token" value="' . $token . '"><br/>';
        echo '<input type="password" name="password" placeholder="Nouveau mot de passe" required><br/>';
        echo '<input type="password" name="password_confirmation" placeholder="Confirmer le mot de passe" required><br/>';
        echo '<button type="submit">Envoyer</button>';
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

/**
 * Inserts a reset password token into the database.
 *
 * This function stores a token, the associated user ID, and the current timestamp into the 
 * 'lab_reset_password' table in the database.
 *
 * @param string $token The reset password token.
 * @param string $uid The user ID associated with the token.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 * 
 * @return void
 */

function lab_reset_password_add_token_to_db($token, $uid) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lab_reset_password';
    
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
 * Given a token, returns the uid associated with it from the database.
 * 
 * @param string $token The token to look up.
 * 
 * @return string|null The uid associated with the token, or null if the token is invalid.
 */
function lab_reset_password_get_uid_from_token($token) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lab_reset_password';
    
    // Récupération de l'uid correspondant au token
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT uid FROM $table_name WHERE token = %s", 
        $token
    ));
    
    if ($result) {
        return $result->uid;
    } else {
        return null;
    }
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

function lab_reset_password_reset_ldap_password($newPassword, $uid) {
    $ldap_obj = LAB_LDAP::getInstance(
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
        true
      );
      $filter    = "(uid=" . $uid . ")";
      $result    = ldap_search($this->ldap_link, $this->base, $filter)
          or die("Error in query");
      $entry     = ldap_get_entries($this->ldap_link, $result);
      if ($entries["count"] > 0) {
        $dn = $entries[0]["dn"]; // DN de l'utilisateur trouvé

        // Hacher le mot de passe (optionnel, selon le format attendu par le serveur)
        $hashedPassword = "{SHA}" . base64_encode(pack("H*", sha1($newPassword)));

        // Remplacer le mot de passe
        $modifications = array(
            "userPassword" => $hashedPassword
        );

        if (ldap_mod_replace($ldapconn, $dn, $modifications)) {
            echo "Mot de passe modifié avec succès.";
        } else {
            echo "Échec de la modification du mot de passe.";
        }
    } else {
        echo "Utilisateur non trouvé.";
    }

}
?>