<?php

define ('LAB_SHORTCODE_PARAM_ASLEFT', 'asLeft');

function lab_incoming_event($param) {
  extract(shortcode_atts(array(
        'slug' => get_option('option_event'),
        'year' => get_option('option_event')
    ),
        $param
  ));
  $eventCaterory = get_option('option_event');
  if (isset($year) && $year != "") {

  }

  $sql = "SELECT p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` WHERE t.slug='".$slug."' AND `p`.`event_end_date` >= NOW() ORDER BY `p`.`event_start_date` ASC ";
  #$sql = "SELECT p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` WHERE t.slug='".$slug."' AND `p`.`event_end_date` < NOW() ORDER BY `p`.`event_end_date` DESC";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $listEventStr = "<table>";
  $url = esc_url(home_url('/'));
  foreach ( $results as $r )
  {
    $listEventStr .= "<tr>";
    $listEventStr .= "<td>".esc_html($r->event_start_date)."</td><td><a href=\"".$url."event/".$r->event_slug."\">".$r->event_name."</a></td>";
    $listEventStr .= "</tr>";
  }
  $listEventStr .= "</table>";
  //return "category de l'evenement : ".$event."<br>".$sql."<br>".$listEventStr;
  return $listEventStr;  
}
//*/


/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab_event_of_the_week
 **********************************************************************************************************************/

function lab_event_of_the_week($param) {
  $day = date('w');
  $week_start = date('Y-m-d', strtotime('-'.($day-1).' days'));
  $week_end = date('Y-m-d', strtotime('+'.(7-$day).' days'));

  //$sql = "SELECT * FROM `wp_em_events` WHERE `event_start_date` >= '".$week_start."' AND `event_end_date` <= '".$week_end."'";
  $sql = "SELECT t.name, p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` WHERE p.`event_start_date` >= '".$week_start."' AND p.`event_end_date` <= '".$week_end."' ORDER BY `p`.`event_start_date` ASC";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  
  $content ="<h4><a class=\"spip_in\" href=\"/events/\">La semaine de l’I2M</a></h4>";
  foreach ( $results as $r )
  {
    $content .= "<p><span style=\"color: #ff6600;\">".date_i18n("l j F Y", strtotime($r->event_start_date))."</span> ";
    $content .= "<span style=\"color: #000000;\"><strong>".$r->name."</strong></span><br>";
    $content .= date("H:i", strtotime($r->event_start_time))." - ".date("H:i", strtotime($r->event_end_time))." <a class=\"spip_out\" href=\"".$r->event_slug."\">".$r->event_name."</a></p>";
  }
  return $content;
}

/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab-event
 **********************************************************************************************************************/
function lab_locate_template( $template_name, $load=false, $the_args = array() ) {
        //First we check if there are overriding tempates in the child or parent theme
        $located = locate_template(array('plugins/lab/'.$template_name));
        $log = "";
        if( !$located ){
                $located = apply_filters('lab_locate_template_default', $located, $template_name, $load, $the_args);
                if ( !$located && file_exists(LAB_DIR.'/templates/'.$template_name) ) {
                        $located = LAB_DIR.'/templates/'.$template_name;
                }
        }
        $located = apply_filters('lab_locate_template', $located, $template_name, $load, $the_args);
        if( $located && $load ){
                if( is_array($the_args) ) extract($the_args);
                include($located);
        }
        $located= $log." ".$located;
        return $located;
}

function lab_event($param)
{
  print(lab_locate_template('forms/event-editor.php',true, array('args'=>$args)));
}

/***********************************************************************************************************************
 * SHORTCODE lab-old-event
 **********************************************************************************************************************/
function lab_old_event($param)
{
  extract(shortcode_atts(array(
        'slug' => get_option('option_event'),
        'year' => get_option('option_event')
    ),
        $param
  ));
   $sqlYearCondition = "";
   if (isset($year) && !empty($year)) {
     $sqlYearCondition = " AND YEAR(`p`.`event_end_date`)=".$year." ";
  }

  $eventCaterory = get_option('option_event');
  $sql = "SELECT p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` WHERE t.slug='".$slug."'".$sqlYearCondition." AND `p`.`event_end_date` < NOW() ORDER BY `p`.`event_end_date` DESC ";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $listEventStr = "<table>";
  $url = esc_url(home_url('/'));
  foreach ( $results as $r )
  {
    $listEventStr .= "<tr>";
    $listEventStr .= "<td>".esc_html($r->event_start_date)."</td><td><a href=\"".$url."event/".$r->event_slug."\">".$r->event_name."</a></td>";
    $listEventStr .= "</tr>";
  }
  $listEventStr .= "</table>";
  //return "category de l'evenement : ".$event."<br>".$sql."<br>".$listEventStr;
  return $listEventStr;
}
