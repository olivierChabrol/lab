<?php
function lab_admin_tab_events()
{
    ?>
    <div style="display:flex; flex-wrap:wrap;">
        <h3><?php esc_html_e("Replace one event tag with another","lab")?></h3>
    </div>
    <?php esc_html_e("Replace","lab")?>
    <select name="event_tag_to_replace"
            class="em-search-tag em-selectize checkboxes <?php echo $args['search_multiselect_style']; ?>"
            id="event_tag_to_replace">
        <?php
        $args_em = apply_filters('em_advanced_search_tags_args', array('orderby' => 'name', 'hide_empty' => 0, 'include' => $include, 'exclude' => $exclude));
        $tags = EM_Tags::get($args_em);
        $selected = array();
        if (!empty($args['tag'])) {
            if (!is_array($args['tag'])) {
                $selected = explode(',', $args['tag']);
            } else {
                $selected = $args['tag'];
            }
        }
        $walker = new EM_Walker_CategoryMultiselect();
        $args_em = apply_filters('em_advanced_search_tags_walker_args', array(
            'hide_empty' => 0,
            'orderby' => 'name',
            'name' => 'tag',
            'hierarchical' => true,
            'taxonomy' => EM_TAXONOMY_TAG,
            'selected' => $selected,
            'show_option_none' => $args['tags_label'],
            'option_none_value' => 0,
            'walker' => $walker
        ));
        echo walk_category_dropdown_tree($tags, 0, $args_em);
        ?>
    </select>
    <?php esc_html_e("by","lab")?>
    <select name="event_tag_replacement"
            class="em-search-tag em-selectize checkboxes <?php echo $args['search_multiselect_style']; ?>"
            id="event_tag_replacement">
        <?php
        echo walk_category_dropdown_tree($tags, 0, $args_em);
        ?>
    </select>
    <a href="#" class="page-title-action" id="lab_admin_replace_event_tags"><?php esc_html_e("Replace","lab")?></a>
    <?php
}