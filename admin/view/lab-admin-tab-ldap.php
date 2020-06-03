<?php
/*
 * File Name: lab-admin-tab-users.php
 * Description: interface de paramètres utilisateurs, affecter un groupe à un utilisateur
 * Authors: Olivier CHABROL, Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

function lab_admin_tab_ldap() {
?>
<div style="display:flex; flex-wrap:wrap;">
    <table class="form-table" role="presentation">
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_enable"><?php esc_html_e('LDAP enable','lab') ?></label></td><td><input type="checkbox" id="lab_admin_tab_ldap_enable"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_host"><?php esc_html_e('LDAP Host','lab') ?></label></td><td><input type="text" id="lab_admin_tab_ldap_host" value="" placeholder="<?php esc_html_e('LDAP Host','lab') ?>"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_token"><?php esc_html_e('LDAP Token','lab') ?></label></td><td><input type="text" id="lab_admin_tab_ldap_token" value="" placeholder="<?php esc_html_e('LDAP Token','lab') ?>"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_base"><?php esc_html_e('LDAP Base','lab') ?></label></td><td><input type="text" id="lab_admin_tab_ldap_base" value="" placeholder="<?php esc_html_e('LDAP Base','lab') ?>"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_login"><?php esc_html_e('LDAP Login','lab') ?></label></td><td><input type="text" id="lab_admin_tab_ldap_login" value="" placeholder="<?php esc_html_e('LDAP Login','lab') ?>"/></td>
        </tr>
        <tr class="user-rich-editing-wrap">
            <td><label for="lab_admin_tab_ldap_tls"><?php esc_html_e('LDAP TLS','lab') ?></label></td><td><input type="checkbox" id="lab_admin_tab_ldap_tls"/></td>
        </tr>
    </table>
</div>
<?php
}
?>