<?php

function lab_request_save($request_id, $request_user_id, $request_type, $request_title, $request_text) {
    global $wpdb;
    if (isset($request_id) && $request_id) {
        $wpdb->update($wpdb->prefix."lab_request", array("request_type" => $request_type, "request_title"=>$request_title, "request_text" => $request_text), array("id" => $request_id));
        return $request_id;
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_request", array("request_date" => current_time('mysql', 1), "request_user_id"=>$request_user_id, "request_type" => $request_type, "request_title"=>$request_title, "request_text" => $request_text));
        $request_id = $wpdb->insert_id;
        lab_request_send_request_to_manager($request_id);
        return $request_id;
    }
}

function lab_request_send_request_to_manager($request_id) {

}
?>