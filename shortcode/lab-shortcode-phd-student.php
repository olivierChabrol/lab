<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    $where = "WHERE p.slug = 'DOCT'";
    $joins = "";

    if (!empty($filters['group'])) {
        $group_id = intval($filters['group']);
        $where .= " AND lug.group_id = $group_id";
        $joins = " JOIN wp_lab_users_groups AS lug ON lug.user_id = luh.user_id ";
    }

    if (!empty($filters['defended']) && $filters['defended'] == 'true') {
        $joins .= " JOIN wp_usermeta AS um_td ON um_td.user_id = luh.user_id AND um_td.meta_key = 'lab_user_thesis_date'";
        $where .= " AND um_td.meta_value IS NOT NULL AND um_td.meta_value != ''";
    }


    if (!empty($filters['search'])) {
        $search_term = esc_sql($filters['search']);
        $joins .= "
            JOIN wp_usermeta AS um_fn ON um_fn.user_id = luh.user_id AND um_fn.meta_key = 'first_name'
            JOIN wp_usermeta AS um_ln ON um_ln.user_id = luh.user_id AND um_ln.meta_key = 'last_name'
        ";
        $where .= " AND (um_fn.meta_value LIKE '%$search_term%' OR um_ln.meta_value LIKE '%$search_term%')";
    }


    // Obtention du nombre total de pages
    $query = "SELECT luh.*, u.user_email FROM `wp_lab_params` AS p JOIN wp_lab_users_historic as luh ON luh.function = p.id JOIN `wp_users` as u ON u.ID = luh.user_id $joins $where ORDER BY luh.begin DESC LIMIT $offset, $items_per_page;";
    if ($page <= 0) {
        $query = "SELECT luh.*, u.user_email FROM `wp_lab_params` AS p JOIN wp_lab_users_historic as luh ON luh.function = p.id JOIN `wp_users` as u ON u.ID = luh.user_id $joins $where ORDER BY luh.begin DESC;";
    }
    $total_query = "SELECT count(*) FROM `wp_lab_params` AS p JOIN wp_lab_users_historic as luh ON luh.function = p.id $joins $where;";


    $total = $wpdb->get_var( $total_query );

    $doctos = $wpdb->get_results($query);
    $num_rows = $wpdb->num_rows;

    $retour["sql"] = $query;
    $retour["count"] = $num_rows;
    $retour["total"] = ceil($total / $items_per_page);
    $retour["page"] = $page;
    $retour["data"] = array();

    $retour["phd_support"] = array();
    $phdSupports = lab_admin_get_params_userPhdSupport();
    foreach($phdSupports as $phdSupport) {
        $retour["phd_support"][$phdSupport->id] = $phdSupport;
    }

    $user_fields = ["user_section_cn","user_section_cnu","user_function","user_thesis_title", "user_phd_school", "user_country", "user_thesis_date", "user_phd_support", "become"];
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
        $array_user[$user_id]["historic"] = lab_admin_load_lastUserHistory($user_id);
    }
    $retour["users"] = $array_user;

    return $retour;
}


function lab_ajax_export_phd_excel() {
    //require_once ABSPATH . 'vendor/autoload.php'; // Chemin vers PhpSpreadsheet
    $data = lab_admin_get_phd_student(array(), array(), -1); // Pour initialiser les données si nécessaire
    $students = $data['data'];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Titre du fichier
    $sheet->setTitle('Liste des doctorants');   
    
    $sheet->setCellValue('A1', 'Nom');
    $sheet->setCellValue('B1', 'Mail');
    $sheet->setCellValue('C1', 'Intitulé de la thèse');
    $sheet->setCellValue('D1', 'Direction');
    $sheet->setCellValue('E1', 'Ecole doctorale');
    $sheet->setCellValue('F1', 'Soutien');
    $sheet->setCellValue('G1', 'Début');
    $sheet->setCellValue('H1', 'Soutenance');
    $sheet->setCellValue('I1', 'Devenir');
    $sheet->setCellValue('J1', 'Groupe');

    $row = 2;
    foreach ($students as $student) {
        $phd = $data['users'][$student->user_id];
        $sheet->setCellValue('A' . $row, $phd['first_name'] . ' ' . strtoupper($phd['last_name']));
        $sheet->setCellValue('B' . $row, $student->user_email);
        $sheet->setCellValue('C' . $row, $phd['lab_user_thesis_title']);
        $host = $data['users'][$student->host_id];
        if (isset($host['first_name']) == false) {
            $host['first_name'] = '';
        }
        if (isset($host['last_name']) == false) {
            $host['last_name'] = '';
        }
        $sheet->setCellValue('D' . $row, $host['first_name'] . ' ' . strtoupper($host['last_name']));
        $sheet->setCellValue('E' . $row, $phd['lab_user_phd_school']);
        $phd_support_id = isset($phd['lab_user_phd_support']) ? intval($phd['lab_user_phd_support']) : 0;
        $phd_support = isset($data['phd_support'][$phd_support_id]) ? $data['phd_support'][$phd_support_id]->slug : '';
        $sheet->setCellValue('F' . $row, $phd_support);
        $sheet->setCellValue('G' . $row, $student->begin);
        $thesis_date = isset($phd['lab_user_thesis_date']) ? $phd['lab_user_thesis_date'] : '';
        $sheet->setCellValue('H' . $row, $thesis_date);
        $become = isset($phd['lab_user_become']) ? $phd['lab_user_become'] : '';
        $sheet->setCellValue('I' . $row, $become);
        $groups = "";
        // Récupération des groupes
        if(isset($phd["group"]) && is_array($phd["group"])) {
            foreach($phd["group"] as $group_id => $group) {
                $groups .= $group->acronym . " ";
            }
        }
        $sheet->setCellValue('J' . $row, trim($groups));
        
        //$sheet->setCellValue('A' . $row, $phd->first_name . ' ' . strtoupper($phd->last_name));
        //$sheet->setCellValue('B' . $row, $phd->lab_user_thesis_title);
        $row++;
    }

    // Envoi du fichier
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="phd_students.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}


function lab_display_phd_student($params) {
    $html = "";
$html .= '<div id="lab_php_student_filters" class="mb-3">';

$html .= '<input type="text" id="lab_search_input" class="form-control mb-2" placeholder="Rechercher un doctorant...">';

$html .= lab_html_select_str(
    'lab_group_filter',
    'lab_group_filter',
    'lab_allRoles',
    'lab_admin_group_load_all',
    null,
    array("value" => 0, "label" => "--- Select Group ---"),
    0
);


// Ligne contenant la checkbox à gauche et le bouton à droite
$html .= '<div class="d-flex align-items-center justify-content-between mt-2">';
$html .= '<div class="form-check form-switch">';
$html .= '<input class="form-check-input" type="checkbox" id="lab_filter_defended">';
$html .= '<label class="form-check-label" for="lab_filter_defended" style="white-space: nowrap;">Afficher uniquement les doctorants ayant soutenu</label>';
$html .= '</div>';

$html .= '<button id="export-excel-btn" class="btn btn-outline-primary ms-auto">📄 Exporter en Excel</button>';
$html .= '</div>'; // fin de la ligne flex

$html .= '</div>'; // fin du bloc principal

    

    
    
    //$html .= '<div class="loading-state"><span class="lab-loader"></span></div>';
    $html .= "<div class=\"table-responsive\"><table  id=\"lab_php_student_table\" class=\"table table-striped  table-hover\"><thead id=\"lab_php_student_table_header\" class=\"thead-dark\"><tr><th>".esc_html__("Name", "lab")."</th>";

    $html .= "<th>".esc_html__("Mail", "lab")."</th>";
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
        $html .= $docto->mail;
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
    $html .= "<span id=\"lab_php_student_table_pagination\"></span>";
    return $html;
}
