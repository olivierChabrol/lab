<?php
/*
 * File Name: lab-admin-tab-users.php
 * Description: interface de paramètres utilisateurs, affecter un groupe à un utilisateur
 * Authors: Olivier CHABROL, Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

function lab_admin_tab_ldap() {
$out = '
<div style="display:flex; flex-wrap:wrap;" id="lab_admin_ldap_tab">
<form action="javascript:lab_admin_ldap_settings();" style="width: 90%;">
    <table class="form-table" role="presentation">
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_enable">'.esc_html__('LDAP enable','lab') .'</label></td>
            <td>
                <label class="switch">
                    <input id="lab_admin_tab_ldap_enable" ';
                    $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_ENABLE)[0]; 
                    $out .= ($var->value == 'true' ? 'checked' : '').' param_id="'.$var->id.'"' .' type="checkbox">
                    <span class="slider round"></span>
                </label>
            </td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_host">'. esc_html__('LDAP Host','lab') .'</label></td>
            <td><input type="text" id="lab_admin_tab_ldap_host" value="';
            $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0];
            $out .= $var->value.'" param_id="'.$var->id .'" placeholder="'. esc_html__('LDAP Host','lab') .'"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_token">'. esc_html__('LDAP Token','lab') .'</label></td>
            <td><input type="text" id="lab_admin_tab_ldap_token" value="';
            $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_TOKEN)[0];
            $out .= $var->value.'" param_id="'.$var->id .'" placeholder="'. esc_html__('LDAP Token','lab') .'"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_base">'. esc_html__('LDAP Base','lab') .'</label></td>
            <td><input type="text" id="lab_admin_tab_ldap_base" value="';
            $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0];
            $out .= $var->value.'" param_id="'.$var->id .'" placeholder="'. esc_html__('LDAP Base','lab') .'"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_login">'. esc_html__('LDAP Login','lab') .'</label></td>
            <td><input type="text" id="lab_admin_tab_ldap_login" value="';
            $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0];
            $out .= $var->value.'" param_id="'.$var->id .'" placeholder="'. esc_html__('LDAP Login','lab') .'"/></td>
        </tr>

        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_pass">'. esc_html__('LDAP Password','lab') .'</label></td>
            <td><input type="text" id="lab_admin_tab_ldap_pass" value="';
            $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0];
            $out .= $var->value.'" param_id="'.$var->id .'" placeholder="'. esc_html__('LDAP Password','lab') .'"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_tls">'. esc_html__('LDAP TLS','lab') .'</label></td>
            <td>
                <label class="switch">
                    <input id="lab_admin_tab_ldap_tls" ';
                    $var = AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_TLS)[0];
                    $out .= ($var->value == 'true' ? 'checked' : '').' param_id="'.$var->id.'" type="checkbox">
                     <span class="slider round"></span>
                </label>
            </td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td colspan="2">
                <input type="submit" value="'.esc_html__('Valider','lab') ;
                $out .= '"/>
            </td>
        </tr>
    </table>
</form>
</div>';
return $out;
}
?>