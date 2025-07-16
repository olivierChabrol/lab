<?php
require_once dirname(__FILE__, 4) . '/wp-load.php'; // Charge WordPress
require_once dirname(__File__, 2) . '/events-manager/em-events.php'; // Charge Events Manager
header('Content-Type: application/json; charset=utf-8');

//define and clean up formats for display
$summary_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option ( 'dbem_ical_description_format' ) ) );header('Content-Type: application/json; charset=utf-8');

//define and clean up formats for display
$summary_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option ( 'dbem_ical_description_format' ) ) );
$description_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option ( 'dbem_ical_real_description_format') ) );
$location_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option ( 'dbem_ical_location_format' ) ) );

//figure out limits
$ical_limit = get_option('dbem_ical_limit');
$page_limit = $ical_limit > 50 || !$ical_limit ? 50:$ical_limit; //set a limit of 50 to output at a time, unless overall limit is lower

//get passed on $args and merge with defaults
$args = !empty($args) ? $args:array(); /* @var $args array */
$args = array_merge(array('limit'=>$page_limit, 'page'=>'1', 'owner'=>false, 'orderby'=>'event_start_date,event_start_time', 'scope' => 'all' ), $args);
$args = apply_filters('em_calendar_template_args',$args);

//get first round of events to show
$EM_Events = EM_Events::get( $args );

//initialize JSON output
$json_output = [];
$count = 0;

while ( count($EM_Events) > 0 ){
    foreach ( $EM_Events as $EM_Event ) {
        /* @var $EM_Event EM_Event */
        if( $ical_limit != 0 && $count >= $ical_limit ) break;

        // Prepare event data
        $event_data = [
            'id' => $EM_Event->event_id,
            'title' => apply_filters('em_ical_output_content_summary', $EM_Event->output($summary_format, 'ical'), $EM_Event, $args),
            'description' => apply_filters('em_ical_output_content_description', $EM_Event->output($description_format, 'ical'), $EM_Event, $args),
            'start' => $EM_Event->start()->format('c'), // ISO 8601 format
            'end' => $EM_Event->end()->format('c'), // ISO 8601 format
            'url' => $EM_Event->get_permalink(),
            'location' => $EM_Event->location_id ? $EM_Event->output($location_format, 'ical') : null,
            'categories' => array_map(function($category) {
                return $category->name;
            }, $EM_Event->get_categories()->categories),
            'image' => $EM_Event->get_image_url(),
        ];

        // Add event data to JSON output
        $json_output[] = $event_data;
        $count++;
    }

    if( $ical_limit != 0 && $count >= $ical_limit ){ 
        break;
    } else {
        $args['page']++;
        $EM_Events = EM_Events::get( $args );
    }
}

// Output JSON
echo json_encode($json_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);