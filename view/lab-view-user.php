<?php
/*
 * File Name: lab-view-user.php
 * Description: page de test pour la gestion des users
 * Authors: Lucas Urgenti
 * Version: 0.2
 * 
 */

function lab_user_echo()
{
    echo(lab_user());
}

function lab_user($args=null)
{
    $userStr ='
    <div style="display:flex; flex-wrap:wrap;">
        <form>
        <h3>'. esc_html__("Edit user","lab") .'</h3>
            <table class="form-table" role="presentation">
            <tr class="user-rich-editing-wrap">
                <th scope="row">
                <label for="lab_user_name">'. esc_html__("Username","lab") .'</label>
                </th>
                <td>
                <input type="text"   id="lab_user_search"    value="" /><br>
                <input type="hidden" id="lab_user_search_id" value="" /><br>
                </td>
            </tr>
            <tr>
                <td colspan="2"> <input type="text" id="lab_user_firstname" value="" placeholder="'. esc_html__('First name','lab') .'"/><input type="text" id="lab_user_lastname" value=""  placeholder="'. esc_html__('Last name','lab') .'"/>
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_function">'. esc_html__('User function','lab') .'</label>
                </td>
                <td>
                '. lab_html_select_str("lab_user_function", "lab_user_function", "", lab_admin_get_params_userFunction, null, array("value"=>"","label"=>"None"), "") .'
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_employer">'. esc_html__('Employ','lab') .'</label>
                </td>
                <td>
                '. lab_html_select_str("lab_user_employer", "lab_user_employer", "", lab_admin_get_params_userEmployer, null, array("value"=>"","label"=>"None"), "") .'
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_funding">'. esc_html__('Funding','lab') .'</label>
                </td>
                <td>
                '. lab_html_select_str("lab_user_funding", "lab_user_funding", "", lab_admin_get_params_userFunding, null, array("value"=>"","label"=>"None"), "") .'
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_location">'. esc_html__('User Location','lab') .'</label>
                </td>
                <td>
                '. lab_html_select_str("lab_user_location", "lab_user_location", "", lab_admin_get_params_userLocation, null, array("value"=>"","label"=>"None"), "") .'
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_office_number">'. esc_html__('User office number','lab') .'</label>
                </td>
                <td>
                <input type="text"   id="lab_user_office_number"/>
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_office_floor">'. esc_html__('User office floor','lab') .'</label>
                </td>
                <td>
                <input type="text" id="lab_user_office_floor"/>
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_phone">'. esc_html__('Phone','lab') .'</label>
                </td>
                <td>
                <input type="text" id="lab_user_phone"/>
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_section_cn">'. esc_html__('CN Section','lab') .'</label>
                </td>
                <td>
                '. lab_html_select_str("lab_user_section_cn", "lab_user_section_cn", "", lab_admin_get_params_userSectionCn, null, array("value"=>"","label"=>"None"), "") .'
                </td>
            </tr>
            <tr>
                <td>
                <label for="lab_user_section_cnu">'. esc_html__('CNU Section','lab') .'</label>
                </td>
                <td>
                '. lab_html_select_str("lab_user_section_cnu", "lab_user_section_cnu", "", lab_admin_get_params_userSectionCnu, null, array("value"=>"","label"=>"None"), "") .'
                </td>
            </tr>
            <tr>
                <td>
                    <label for="users">'. esc_html__("User groups","lab") .'</label>
                </td>
                <td>
                    <div style="float: right; margin-left:50px">
                        <label for="groups">'. esc_html__("Choose the group(s) which you will assign the person:", "lab") .'
                        </label><br/><br/>
                        <select id="list_groups" name="groups[]" multiple style="height:150px;"></select>
                    </div>
                </td>
            </tr>
            <tr>
                <form style="flex-grow:1;">
                    <h4>'. esc_html__("User historic","lab").'</h4>
                    <div>
                        <ul id="lab_history_list">
                        
                        </ul>
                    </div>
                    <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                        <label for="lab_historic_start">'. esc_html__("Start date","lab").' : </label>
                        </th>
                        <td>
                        <input type="date" id="lab_historic_start"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="lab_historic_end">'. esc_html__("End date","lab").' : </label>
                        </th>
                        <td>
                        <input type="date" id="lab_historic_end"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="lab_historic_function">'. esc_html__("Function","lab").' : </label>
                        </th>
                        <td>
                        '. lab_html_select_str("lab_history_function", "lab_history_function", '', 'lab_admin_get_params_userFunction', AdminParams::PARAMS_USER_FUNCTION_ID, array("value"=>0,"label"=>"Sélectionnez une fonction"),0).'
                        </td>
                    </tr>
                    <tr>
                        <th>
                        <label for="lab_historic_host">'. esc_html__('Host','lab').' : </label>
                        </th>
                        <td>
                        <input type="text" id="lab_historic_host"/>
                        </td>
                    </tr>
                    </table>
                </form>
            </tr>
            <tr>
                <th>
                    <label>'. esc_html__('Thematic','lab').' : </label>
                </th>
                <td><div id="user_thematics"></div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                <a href="#" class="page-title-action" id="lab_user_button_save_left">'. esc_html__('Edit user status','lab') .'</a>
                </td>
            </tr>
            </table>
        </form>
    </div>
    ';

    $userStr .='
    <hr/>';

    $userStr .= '
    <div id="ldap_menu_flex" style="display:flex; flex-wrap:wrap;">
        <form style="margin-right: 2em" id="lab_ldap_newUser" action="javascript:lab_ldap_addUser_wp()">
            <h3>'. esc_html__("Add user to the directory","lab") .'</h3>
            <table class="form-table" role="presentation">
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_queryAmu">'. esc_html__("User email AMU :","lab") .'</label>
                </th>
                <td>
                    <input type="email" id="lab_ldap_queryAmu"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_newUser_lastName">'. esc_html__("Name","lab") .'<span class="lab_form_required_star"> *</span></label>
                </th>
                <td>
                    <input required type="text" id="lab_ldap_newUser_lastName"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_newUser_firstName">'. esc_html__("Firstname","lab") .'<span class="lab_form_required_star"> *</span></label>
                </th>
                <td>
                    <input required type="text" id="lab_ldap_newUser_firstName"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_newUser_email">'. esc_html__("E-Mail","lab") .'<span class="lab_form_required_star"> *</span></label>
                </th>
                <td>
                    <input required type="email" id="lab_ldap_newUser_email"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_newUser_uid">'. esc_html__("Login (uid)","lab") .'<span class="lab_form_required_star"> *</span></label>
                </th>
                <td>
                    <input required type="text" id="lab_ldap_newUser_uid"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_newUser_pass">'. esc_html__("Password","lab") .'<span class="lab_form_required_star"> *</span></label>
                </th>
                <td>
                    <input required type="text" id="lab_ldap_newUser_pass"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <th scope="row">
                    <label for="lab_ldap_newUser_org">'. esc_html__("Organization","lab") .'</label>
                </th>
                <td>
                    <input type="text" id="lab_ldap_newUser_org"/>
                </td>
                </tr>
                <tr class="user-rich-editing-wrap">
                <td scope="row" colspan="2">
                    <input type="submit" value="Valider"/>
                </td>
                </tr>
            </table>
        </form>
    </div>
    ';

    return $userStr;
}