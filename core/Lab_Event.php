<?php

namespace lab\core;

use EM_Tags;

/**
 * Class Lab_Event
 * @package lab\core
 * Implements the Event Manager Event class to add tags saving functionality.
 */
class Lab_Event extends \EM_Event
{
    /**
     * Returns an EM_Tags object of the EM_Event instance.
     * @return EM_Tags
     */
    function get_tags() {
        if ( empty($this->tags) ) {
            $this->tags = new EM_Tags($this);
        } elseif ( empty($this->tags->event_id) ) {
            $this->tags->event_id = $this->event_id;
            $this->tags->post_id = $this->post_id;
        }
        return apply_filters('em_event_get_tags', $this->tags, $this);
    }

    function get_post($validate = true)
    {
        global $allowedposttags;
        do_action('em_event_get_post_pre', $this);
        //we need to get the post/event name and content.... that's it.
        $this->post_content = isset($_POST['content']) ? wp_kses( wp_unslash($_POST['content']), $allowedposttags):'';
        $this->post_excerpt = !empty($this->post_excerpt) ? $this->post_excerpt:''; //fix null error
        $this->event_name = ( !empty($_POST['event_name']) ) ? sanitize_post_field('post_title', $_POST['event_name'], $this->post_id, 'db'):'';
        $this->post_type = ($this->is_recurring() || !empty($_POST['recurring'])) ? 'event-recurring':EM_POST_TYPE_EVENT;
        //don't forget categories!
        if( get_option('dbem_categories_enabled') ) $this->get_categories()->get_post();
        if( get_option('dbem_tags_enabled') ) $this->get_tags()->get_post();
        //get the rest and validate (optional)
        $this->get_post_meta();
        //anonymous submissions and guest basic info
        if( !is_user_logged_in() && get_option('dbem_events_anonymous_submissions') && empty($this->event_id) ){
            $this->event_owner_anonymous = 1;
            $this->event_owner_name = !empty($_POST['event_owner_name']) ? wp_kses_data(wp_unslash($_POST['event_owner_name'])):'';
            $this->event_owner_email = !empty($_POST['event_owner_email']) ? wp_kses_data($_POST['event_owner_email']):'';
            if( empty($this->location_id) && !($this->location_id === 0 && !get_option('dbem_require_location',true)) ){
                $this->get_location()->owner_anonymous = 1;
                $this->location->owner_email = $this->event_owner_email;
                $this->location->owner_name = $this->event_owner_name;
            }
        }
        //validate and return results
        $result = $validate ? $this->validate():true; //validate both post and meta, otherwise return true
        return apply_filters('em_event_get_post', $result, $this);
    }

    function save(){
        global $wpdb, $current_user, $blog_id, $EM_SAVING_EVENT;
        $EM_SAVING_EVENT = true; //this flag prevents our dashboard save_post hooks from going further
        if( !$this->can_manage('edit_events', 'edit_others_events') && !( get_option('dbem_events_anonymous_submissions') && empty($this->event_id)) ){
            //unless events can be submitted by an anonymous user (and this is a new event), user must have permissions.
            return apply_filters('em_event_save', false, $this);
        }
        //start saving process
        do_action('em_event_save_pre', $this);
        $post_array = array();
        //Deal with updates to an event
        if( !empty($this->post_id) ){
            //get the full array of post data so we don't overwrite anything.
            if( !empty($this->blog_id) && is_multisite() ){
                $post_array = (array) get_blog_post($this->blog_id, $this->post_id);
            }else{
                $post_array = (array) get_post($this->post_id);
            }
        }
        //Overwrite new post info
        $post_array['post_type'] = ($this->recurrence && get_option('dbem_recurrence_enabled')) ? 'event-recurring':EM_POST_TYPE_EVENT;
        $post_array['post_title'] = $this->event_name;
        $post_array['post_content'] = !empty($this->post_content) ? $this->post_content : '';
        $post_array['post_excerpt'] = $this->post_excerpt;
        //decide on post status
        if( empty($this->force_status) ){
            if( count($this->errors) == 0 ){
                $post_array['post_status'] = ( $this->can_manage('publish_events','publish_events') ) ? 'publish':'pending';
            }else{
                $post_array['post_status'] = 'draft';
            }
        }else{
            $post_array['post_status'] = $this->force_status;
        }
        //anonymous submission only
        if( !is_user_logged_in() && get_option('dbem_events_anonymous_submissions') && empty($this->event_id) ){
            $post_array['post_author'] = get_option('dbem_events_anonymous_user');
            if( !is_numeric($post_array['post_author']) ) $post_array['post_author'] = 0;
        }
        //Save post and continue with meta
        $post_id = wp_insert_post($post_array);
        $post_save = false;
        $meta_save = false;
        if( !is_wp_error($post_id) && !empty($post_id) ){
            $post_save = true;
            //refresh this event with wp post info we'll put into the db
            $post_data = get_post($post_id);
            $this->post_id = $this->ID = $post_id;
            $this->post_type = $post_data->post_type;
            $this->event_slug = $post_data->post_name;
            $this->event_owner = $post_data->post_author;
            $this->post_status = $post_data->post_status;
            $this->get_status();
            //Categories
            if( get_option('dbem_categories_enabled') ){
                $this->get_categories()->event_id = $this->event_id;
                $this->categories->post_id = $this->post_id;
                $this->categories->save();
            }
            if( get_option('dbem_tags_enabled') ){
                $this->get_tags()->event_id = $this->event_id;
                $this->tags->post_id = $this->post_id;
                $this->tags->save();
            }
            //anonymous submissions should save this information
            if( !empty($this->event_owner_anonymous) ){
                update_post_meta($this->post_id, '_event_owner_anonymous', 1);
                update_post_meta($this->post_id, '_event_owner_name', $this->event_owner_name);
                update_post_meta($this->post_id, '_event_owner_email', $this->event_owner_email);
            }
            //save the image, errors here will surface during $this->save_meta()
            $this->image_upload();
            //now save the meta
            $meta_save = $this->save_meta();
        }
        $result = $meta_save && $post_save;
        if($result) $this->load_postdata($post_data, $blog_id); //reload post info
        //do a dirty update for location too if it's not published
        if( $this->is_published() && !empty($this->location_id) ){
            $EM_Location = $this->get_location();
            if( $EM_Location->location_status !== 1 ){
                //let's also publish the location
                $EM_Location->set_status(1, true);
            }
        }
        $return = apply_filters('em_event_save', $result, $this);
        $EM_SAVING_EVENT = false;
        //reload post data and add this event to the cache, after any other hooks have done their thing
        //cache refresh when saving via admin area is handled in EM_Event_Post_Admin::save_post/refresh_cache
        if( $result && $this->is_published() ){
            //we won't depend on hooks, if we saved the event and it's still published in its saved state, refresh the cache regardless
            $this->load_postdata($this);
            wp_cache_set($this->event_id, $this, 'em_events');
            wp_cache_set($this->post_id, $this->event_id, 'em_events_ids');
        }
        return $return;
    }
}