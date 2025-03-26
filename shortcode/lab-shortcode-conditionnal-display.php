<?php
/*
 * File Name: lab-shortcode-conditionnal-display.php
 * Description: shortcode affichant un contenu sous condition
 * Authors: Laurent Regnier
 * Version: 0.1
*/

/***
 * Shortcode use: [lab-display-if {role}]text[/lab-display-if]
 role=some registered role, typically 'editor'

 text is displayed if user is logged in and has proper role

***/

function lab_display_if($param, $content=null) {
    $param = shortcode_atts(
        array(
            'role' => null,
        ),
        $param,
        'lab_display_if');
    $user = wp_get_current_user();
    $role = $param['role'];
    if ($user && $role && in_array($role, (array) $user->roles))
        return $content;
    return null;
}
