<?php

use lab\core\Lab_Event;

function lab_event_add()
{
    global $EM_Notices,$EM_Event;
    if( !empty($_REQUEST['action']) && substr($_REQUEST['action'],0,9) == 'lab_event' ) {
        if (!empty($_REQUEST['event_id'])) {
            $EM_Event = new Lab_Event(absint($_REQUEST['event_id']));
        } else {
            $EM_Event = new Lab_Event();
        }
        if ($_REQUEST['action'] == 'lab_event_save' && $EM_Event->can_manage('edit_events', 'edit_others_events')) {
            //Check Nonces
            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'wpnonce_event_save')) exit('Trying to perform an illegal action.');
            //Set server timezone to UTC in case other plugins are doing something naughty
            $server_timezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            //Grab and validate submitted data
            if ($EM_Event->get_post() && $EM_Event->save()) { //EM_Event gets the event if submitted via POST and validates it (safer than to depend on JS)
                $events_result = true;
                //Success notice
                if (is_user_logged_in()) {
                    if (empty($_REQUEST['event_id'])) {
                        $EM_Notices->add_confirm($EM_Event->output(get_option('dbem_events_form_result_success')), true);
                    } else {
                        $EM_Notices->add_confirm($EM_Event->output(get_option('dbem_events_form_result_success_updated')), true);
                    }
                } else {
                    $EM_Notices->add_confirm($EM_Event->output(get_option('dbem_events_anonymous_result_success')), true);
                }
                $redirect = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : em_wp_get_referer();
                $redirect = em_add_get_params($redirect, array('success' => 1), false, false);
                wp_safe_redirect($redirect);
                exit();
            } else {
                $EM_Notices->add_error($EM_Event->get_errors());
                $events_result = false;
            }

            //Set server timezone back, even though it should be UTC anyway
            date_default_timezone_set($server_timezone);

            if (isset($events_result) && !empty($_REQUEST['em_ajax'])) {
                if ($events_result) {
                    $return = array('result' => true, 'success' => true, 'message' => $EM_Event->feedback_message);
                } else {
                    $return = array('result' => false, 'success' => false, 'message' => $EM_Event->feedback_message, 'errors' => $EM_Event->errors);
                }
                echo EM_Object::json_encode($return);
                edit();
            }
        }
    }
}