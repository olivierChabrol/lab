<?php

/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab_incoming_event
 **********************************************************************************************************************/

/*** 
 * Shortcode use : [lab-incoming-event {slug}]
     slug='categoryName1, categoryName2...'
     You can add as more slugs as you want but there must be at least one
***/ 

function lab_incoming_event($param) 
{
    $param = shortcode_atts(array(
        'slug' => get_option('lab-incoming-event')
        ),
        $param,
        "lab-incoming-event"
    );
    $eventCategory = $param['slug'];
    $category      = explode(",", $eventCategory);

    /***  SQL ***/
    $sql = "SELECT p.*, pmd.meta_value as speaker
            FROM `wp_terms` AS t 
            JOIN `wp_term_relationships` AS tr  ON tr.`term_taxonomy_id` = t.`term_id` 
            JOIN `wp_em_events`          AS p   ON p.`post_id`           = tr.`object_id` 
            JOIN `wp_postmeta`           AS pmd ON pmd.`post_id`         = p.`post_id`
           WHERE t.slug = '" . $category[0] . "'";
    
    for($i = 1 ; $i < count($category) ; ++$i)
    {
        $sql .= "OR t.slug = '" . $category[$i] . "'";
    }

    $sql .= "AND `p`.`event_end_date` >= NOW() 
             AND pmd.meta_key = 'Speaker'
             ORDER BY `p`.`event_start_date` 
             ASC ";
    global $wpdb;
    $results       = $wpdb->get_results($sql);
    
    /***  DISPLAY ***/
    $url           = esc_url(home_url('/'));
    $listEventStr = '<br><a href="'.$url."events/categories/agenda/seminaires/".$eventCategory.'/ical">iCal</a>';
    $listEventStr  .= "<table>";
    foreach ($results as $r )
    {
        $listEventStr .= "<tr>";
        $listEventStr .= "<td>".esc_html($r->event_start_date)."</td>
                          <td><span style=\"color: green\">".$r->speaker."</span></td><td><a href=\"".$url."event/".$r->event_slug."\">".$r->event_name."</a></td>";
        $listEventStr .= "</tr>";
    }
    $listEventStr .= "</table>";
    return $listEventStr;  
}


/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab_event_of_the_week
 **********************************************************************************************************************/
/*** 
 * Shortcode use : [lab-event-of-the-week]
    No parameters
***/ 

function lab_event_of_the_week($param) 
{
    $day        = date('w');
    $week_start = date('Y-m-d', strtotime('-'.($day-1).' days'));
    $week_end   = date('Y-m-d', strtotime('+'.(7-$day).' days'));

    $sql = "SELECT t.name, p.*, pmd.meta_value as speaker
            FROM `wp_terms` AS t 
            JOIN `wp_term_relationships` AS tr  ON tr.`term_taxonomy_id`=t.`term_id` 
            JOIN `wp_em_events`          AS p   ON p.`post_id`=tr.`object_id` 
            JOIN `wp_postmeta`           AS pmd ON pmd.`post_id`         = p.`post_id`
            WHERE p.`event_start_date` >= '".$week_start."' 
                AND p.`event_end_date` <= '".$week_end."' 
                AND pmd.meta_key = 'Speaker'
            ORDER BY `p`.`event_start_date` ASC";
    global $wpdb;
    $results = $wpdb->get_results($sql);

    $res = array();
    $ids = array();
    foreach($results as $r)
    {
        if (array_key_exists ($r->post_id, $ids)) {
            $ids[$r->post_id]->name = $ids[$r->post_id]->name.", ".$r->name;
        }
        else
        {
            $ids[$r->post_id] = $r;
            $res[] = $r;
        }
    }

    $content ="<h4><a class=\"spip_in\" href=\"/events/\">".esc_html__("I2m Week",'lab')."</a></h4><br/>";
    
    foreach ( $res as $r )
    {
        $content .= "<p><span style=\"color: #ff6600;\">".date_i18n("l j F Y", strtotime($r->event_start_date))."</span> ";
        $content .= "<span style=\"color: green\"><strong>".$r->speaker."</strong></span><br>";
        $content .= "<span style=\"color: #000000;\"><strong>".$r->name."</strong></span><br>";
        $content .= date("H:i", strtotime($r->event_start_time))." - ".date("H:i", strtotime($r->event_end_time))." <a class=\"spip_out\" href=\"".$r->event_slug."\">".$r->event_name."</a></p>";
    }
    return $content;
    //return $sql;
}

/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab-event
 **********************************************************************************************************************/
/*** 
 * Shortcode use : [lab-event]
    No parameters
***/ 
 function lab_locate_template($template_name, $load=false, $the_args = array()) 
{
    //First we check if there are overriding tempates in the child or parent theme
    $located = locate_template(array('plugins/lab/'.$template_name));
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
    //$located= $located;
    return $located;
}

function lab_event($param)
{
  print(lab_locate_template('forms/event-editor.php',true, array('args'=>$param)));
}

/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab-event-of-the-year
 **********************************************************************************************************************/
/*** 
 * Display events from a year
 * Shortcode use : [lab-event-of-the-year {slug} {year}]
***/
function lab_event_of_the_year($param) {
    $param = shortcode_atts(array(
        'slug' => get_option('lab-event-of-the-year'),
        'year' => get_option('lab-event-of-the-year')
    ),
        $param,
        "lab-event-of-the-year"
    );
    $eventCategory = $param['slug'];
    $eventYear     = $param['year'];
    //return "[lab_event_of_the_year] eventCategory : ".$eventCategory.", eventYear : ".$eventYear.", year : ".$year."<br>";
    return lab_events($eventCategory, $eventYear, false);

}
/***********************************************************************************************************************
 * PLUGIN SHORTCODE lab-old-event
 **********************************************************************************************************************/
/*** 
 ** Shortcode use : [lab-old-event {slug} {year}]
 * To search category OR another category :
    slug='categoryName1, categoryName2...'
 * To search category AND another category :
    slug='categoryName1+categoryName2...'
     You can add as more slugs as you want but there must be at least one
    
     year='20xx'
     Year is optional
***/ 

function lab_old_event($param)
{
    $param = shortcode_atts(array(
        'slug' => get_option('lab-old-event'),
        'year' => get_option('lab-old-event')
    ),
        $param,
        "lab-old-event"
    );
    $eventCategory = $param['slug'];
    $eventYear     = $param['year'];

    return lab_events($eventCategory, $eventYear, true);
}

/* SQL request for lab_old_event & lab_event_of_the_year */
function lab_events($eventCategory, $eventYear, $old) {
    //return "[lab_events] : ".$eventCategory.", ".$eventYear.", ".$old."<br>";
    if (strpos($eventCategory,"+")) {
        $category = explode("+", $eventCategory);
        $sqlYearCondition = "";
        $sqlCondition = "";

        if (isset($eventYear) && !empty($eventYear)) {
            $sqlYearCondition = " AND YEAR(`ee`.`event_end_date`) = '".$eventYear."'";
        }

        if ($old == true) {
            $sqlCondition = " AND `ee`.`event_end_date` < NOW() ";
        }
        
        $sql = "SELECT ee.*, pmd.meta_value as speaker";
        $categorySize = count($category);
        for ($i = 0; $i < $categorySize; $i++)
        {
            $sql .= ", t".$i.".slug";
        }
        $sql .= " FROM `wp_em_events` AS ee ";
        for ($i = 0; $i < $categorySize; $i++) 
        {
            $sql .= " JOIN `wp_term_relationships` AS tr".$i." ON ee.post_id=tr".$i.".`object_id`
                JOIN `wp_term_taxonomy`      AS tt".$i." ON tt".$i.".term_taxonomy_id=tr".$i.".term_taxonomy_id 
                JOIN `wp_terms`              AS t".$i."  ON t".$i.".term_id=tt".$i.".term_id";
        }
        $sql .= "JOIN `wp_postmeta` AS pmd ON pmd.`post_id` = p.`post_id`";
        $sql .= " WHERE (";
        for($i = 0; $i < $categorySize ; $i++)
        {
            $sql .= " t".$i.".slug = '" . $category[$i] . "'";
            if ($i+1 < $categorySize) {
                $sql .= " AND ";
            }
        }
        $sql .= ")" . $sqlYearCondition . $sqlCondition . " AND pmd.meta_key = 'Speaker' ".
            " ORDER BY `ee`.`event_start_date` DESC";
    } 
    else
    {
    //if(strpos($eventCategory, ",")>0 ) {
        $category         = explode(",", $eventCategory); 
        $sqlYearCondition = "";
        $sqlCondition     = "";
        
        if (isset($eventYear) && !empty($eventYear)) {
            $sqlYearCondition = " AND YEAR(`p`.`event_end_date`) = '".$eventYear."'";
        }

        if ($old == true) {
            $sqlCondition = " AND `p`.`event_end_date` < NOW() ";
        }
        
        /***  SQL ***/
        $sql = "SELECT p.*, pmd.meta_value as speaker 
                FROM `wp_terms` AS t 
                JOIN `wp_term_relationships` AS tr 
                    ON tr.`term_taxonomy_id`=t.`term_id` 
                JOIN `wp_em_events` as p 
                    ON p.`post_id`=tr.`object_id`
                JOIN `wp_postmeta` AS pmd ON pmd.`post_id` = p.`post_id`
                WHERE (t.slug='".$category[0]."'";
        for($i = 1 ; $i < count($category) ; ++$i) {
            $sql .= " OR t.slug = '" . $category[$i] . "'";
        }
        $sql .=     ")" . $sqlYearCondition  . $sqlCondition . " AND pmd.meta_key = 'Speaker' 
                    ORDER BY `p`.`event_end_date` DESC ";
    } 
    //return $sql;
    //return "MON SQL : ".$sql."<br>";
    global $wpdb;
    $results = $wpdb->get_results($sql);

    /***  DISPLAY ***/
    $listEventStr = "<table>";
    $url = esc_url(home_url('/'));
    foreach ($results as $r){
        $listEventStr .= "<tr><td>" . esc_html($r->event_start_date) . "</td><td><span style=\"color: green\">".esc_html($r->speaker)."</span></td><td><a href=\"".$url."event/".$r->event_slug."\">".$r->event_name."</a></td></tr>";
    }
    $listEventStr .= "</table>";
    return $listEventStr;
}