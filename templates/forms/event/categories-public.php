<?php
/*
 * This file is called by templates/forms/location-editor.php to display fields for uploading images on your event form on your website. This does not affect the admin featured image section.
* You can override this file by copying it to /wp-content/themes/yourtheme/plugins/events-manager/forms/event/ and editing it there.
*/
global $EM_Event;
/* @var $EM_Event EM_Event */ 
$categories = EM_Categories::get(array('orderby'=>'name','hide_empty'=>0));
?>
<?php if( count($categories) > 0 ): ?>
<div class="event-categories">
	<!-- START Categories -->
        <h3 class="event-form-name"><?php esc_html_e( 'Category', 'events-manager'); ?></h3>
	<select name="event_categories[]" multiple size="10">
	<?php
	if(isset($_GET['e_cat'])){
		$selected = intval($_GET['e_cat']);
	}else{
        $selected = $EM_Event->get_categories()->get_ids();
    }
	$walker = new EM_Walker_CategoryMultiselect();
	$args_em = array( 'hide_empty' => 0, 'name' => 'event_categories[]', 'hierarchical' => true, 'id' => EM_TAXONOMY_CATEGORY, 'taxonomy' => EM_TAXONOMY_CATEGORY, 'selected' => $selected, 'walker'=> $walker);
	echo walk_category_dropdown_tree($categories, 0, $args_em);
	?></select>
	<!-- END Categories -->
</div>
<?php endif; ?>
