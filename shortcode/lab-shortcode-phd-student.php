<?php

function lab_admin_get_phd_student($filters, $order, $page) {

    global $wpdb;

    $items_per_page = 25;
    $offset = ($page - 1) * $items_per_page;
    $retour = array();

    $sql_groups = "SELECT id, acronym FROM `wp_lab_groups`";
    $groups = $wpdb->get_results($sql_groups);
    $array_group = array();
    
    foreach ($groups as $group) {
        $array_group[$group->id] = $group->acronym;
    }
    $retour["groups"] = $array_group;

    // Obtention du nombre total de pages
    $query = "SELECT luh.* FROM `wp_lab_params` AS p JOIN wp_lab_users_historic as luh ON luh.function = p.id WHERE p.slug = 'DOCT' ORDER BY luh.begin DESC LIMIT ${offset}, ${items_per_page};";
    $total_query = "SELECT count(*) FROM `wp_lab_params` AS p JOIN wp_lab_users_historic as luh ON luh.function = p.id WHERE p.slug = 'DOCT';";
    $total = $wpdb->get_var( $total_query );

    $doctos = $wpdb->get_results($query);
    $num_rows = $wpdb->num_rows;

    $retour["count"] = $num_rows;
    $retour["total"] = ceil($total / $items_per_page);
    $retour["page"] = $page;
    $retour["data"] = array();
    $user_fields = ["user_section_cn","user_section_cnu","user_function","user_thesis_title", "user_phd_school", "user_country", "user_thesis_date"];
    //$host_fields = ["user_section_cn","user_section_cnu","user_function","user_thesis_title", "user_phd_school", "user_country", "user_thesis_date"];
    $array_user = array();
    foreach ($doctos as $docto) {
        $retour["data"][] = $docto;
        $user_id = $docto->user_id;
        $host_id = $docto->host_id;
        if(!isset($array_user[$user_id])) {
            $array_user[$user_id] = lab_admin_get_user_info($user_id, $user_fields);
        }
        if(!isset($array_user[$host_id])) {
            $array_user[$host_id] = lab_admin_get_user_info($host_id, null);
        }
    }
    $retour["users"] = $array_user;

    return $retour;
}

function lab_display_phd_student($params) {

    $html = "<div class=\"table-responsive\"><table  id=\"lab_php_student_table\" class=\"table table-striped  table-hover\"><thead id=\"lab_php_student_table_header\" class=\"thead-dark\"><tr><th>".esc_html__("Name", "lab")."</th>";

    $html .= "<th>".esc_html__("Intitulé", "lab")."</th>";
    $html .= "<th>".esc_html__("Direction", "lab")."</th>";
    $html .= "<th>".esc_html__("ED", "lab")."</th>";
    $html .= "<th>".esc_html__("Pays", "lab")."</th>";
    $html .= "<th>".esc_html__("Soutien", "lab")."</th>";
    $html .= "<th>".esc_html__("Debut", "lab")."</th>";
    $html .= "<th>".esc_html__("Soutenance", "lab")."</th>";
    $html .= "<th>".esc_html__("Devenir", "lab")."</th>";
    $html .= "<th>".esc_html__("Groupe", "lab")."</th>";
    $html .= "</thead><tbody id=\"lab_php_student_table_body\">";
    $html .= "<div id=\"lab_php_student_table_pagination\"></div>";

    global $wpdb;
    $sql = "SELECT luh.user_id, luh.begin, luh.end, um1.meta_value as first_name, um2.meta_value as last_name, um3.meta_value AS user_slug, um4.meta_value as host_first_name, um5.meta_value as host_last_name, um6.meta_value AS phd_title, um7.meta_value AS cn, um8.meta_value AS cnu, um9.meta_value AS phd_school, um10.meta_value AS country
    FROM `wp_lab_params` AS p 
    JOIN wp_lab_users_historic as luh ON luh.function = p.id 
    JOIN wp_usermeta AS um1 on um1.user_id=luh.user_id 
    JOIN wp_usermeta AS um2 on um2.user_id=luh.user_id 
    JOIN wp_usermeta AS um3 on um3.user_id=luh.user_id 
    JOIN wp_usermeta AS um4 on um4.user_id=luh.host_id 
    JOIN wp_usermeta AS um5 on um5.user_id=luh.host_id 
    JOIN wp_usermeta AS um6 on um6.user_id=luh.user_id 
    JOIN wp_usermeta AS um7 on um7.user_id=luh.user_id 
    JOIN wp_usermeta AS um8 on um8.user_id=luh.user_id 
    JOIN wp_usermeta AS um9 on um9.user_id=luh.user_id 
    JOIN wp_usermeta AS um10 on um10.user_id=luh.user_id 
    
    WHERE p.slug = 'DOCT' AND um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_slug' AND um4.meta_key='first_name' AND um5.meta_key='last_name' AND um6.meta_key='lab_user_thesis_title' AND um7.meta_key='lab_user_section_cn' AND um8.meta_key='lab_user_section_cnu'AND um9.meta_key='lab_user_phd_school' AND um10.meta_key='lab_user_country';";
    $doctos = array(); //$wpdb->get_results($sql);
    foreach($doctos as $docto) {
        $html .= '<tr>';
        $html .= '<td>';
        $html .= $docto->first_name . " ".strtoupper($docto->last_name);
        $html .= '</td>';
        $html .= '<td>';
        $html .= $docto->phd_title;
        $html .= '</td>';
        $html .= '<td>';
        $html .= $docto->host_first_name . " ".strtoupper($docto->host_last_name);
        $html .= '</td>';
        $html .= '<td>';
        $html .= $docto->phd_school;
        $html .= '</td>';
        $html .= '<td>';
        $html .= $docto->country;
        $html .= '</td>';
        $html .= '<td>';
        // funding
        $html .= '</td>';
        $html .= '<td>';
        $html .= $docto->begin;
        $html .= '</td>';
        $html .= '<td>';
        $html .= $docto->end;
        $html .= '</td>';
        $html .= '<td>';
        // devenir
        $html .= '</td>';
        $html .= '<td>';
        // groupe
        $html .= '</td>';

        
        $html .= '</tr>';
    }
    $html .= "</tbody></table></div>";
    return $html;
}