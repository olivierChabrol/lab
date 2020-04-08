<?php


/**
 * Fonction qui répond à la requete ajax de recherche d'evenement
 **/
function lab_admin_search_event() {
    $search = $_POST['search'];
    $title  = $search["term"];

    $sql = 'SELECT post_id, `event_name`,`event_start_date` FROM `wp_em_events` AS ee LEFT JOIN `wp_term_relationships` AS tr ON tr.`object_id`=ee.post_id WHERE tr.`object_id` IS NULL AND `event_name` LIKE \'%'.$title.'%\' LIMIT 30';
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
  
    $url = esc_url(home_url('/'));
    foreach ( $results as $r )
    {
      $items[] = array(label=>$r->event_name." ".date("d/m/Y", strtotime($r->event_start_date)),value=>$r->post_id);
    }
    wp_send_json_success( $items );
}

/**
 * Fonction qui répond a la requete d'un recherche par nom de groupe
 */
function lab_admin_group_search() {
    $search = $_POST['search'];
    $groupName  = $search["term"];

    $sql = "SELECT id, group_name FROM `wp_lab_groups` WHERE `group_name` LIKE '%".$groupName."%' ";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $items = array();
    $url = esc_url(home_url('/'));
    foreach ( $results as $r )
    {
      $items[] = array(label=>$r->group_name, value=>$r->id);
    }
    wp_send_json_success( $items ); 
}

function lab_admin_group_delete(){
    $group_name = $_GET['label'];

    $sql = "DELETE FROM `wp_lab_groups` WHERE `group_name` LIKE '%".$group_name."%' ";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    if($results->affected_rows == 1)
    {
        echo "Success";
    }
    else
    {
        echo "Fail";
    }
}