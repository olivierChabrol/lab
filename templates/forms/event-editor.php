<?php
/* WARNING! This file may change in the near future as we intend to add features to the event editor. If at all possible, try making customizations using CSS, jQuery, or using our hooks and filters. - 2012-02-14 */
/* 
 * To ensure compatability, it is recommended you maintain class, id and form name attributes, unless you now what you're doing. 
 * You also must keep the _wpnonce hidden field in this form too.
 */
global $EM_Event, $EM_Notices, $bp;
?>
<?php
//check that user can access this page
if (is_object($EM_Event) && !$EM_Event->can_manage('edit_events', 'edit_others_events')) {
?>
	<div class="wrap">
		<h2><?php esc_html_e('Unauthorized Access', 'events-manager'); ?></h2>
		<p><?php echo sprintf(__('You do not have the rights to manage this %s.', 'events-manager'), __('Event', 'events-manager')); ?></p>
	</div>
<?php
	return false;
} elseif (!is_object($EM_Event)) {
	$EM_Event = new EM_Event();
}
$required = apply_filters('em_required_html', '<i><span style="color:red;"><b>*</b></span></i>');

echo $EM_Notices;
//Success notice
if (!empty($_REQUEST['success'])) {
	$eventAdded = new EM_Event();
	if (!get_option('dbem_events_form_reshow')) return false;
}
?>
<form enctype='multipart/form-data' id="event-form" class="em-event-admin-editor <?php if ($EM_Event->is_recurring()) echo 'em-event-admin-recurring' ?>" method="post" action="<?php echo esc_url(add_query_arg(array('success' => null))); ?>">
	<div class="wrap">
		<?php do_action('em_front_event_form_header', $EM_Event); ?>
		<!-- <h3 class="event-form-name"><?php esc_html_e('Event Name', 'events-manager'); ?></h3> -->
		<div class="event-attributes">
			<i><span style="color:red;"><b>*</b></span></i> : Champs obligatoire/Mandatory fields
		</div>
		<div class="inside event-form-name">
			<label for="event_name">Nom de l'evenement</label>
			<input type="text" name="event_name" id="event-name" placeholder="Indiquer le nom de l'evenement" value="<?php echo esc_attr($EM_Event->event_name, ENT_QUOTES); ?>"><?php echo $required; ?>
			<br />
			<div class="event-attributes">
				<label for="em_attributes[Speaker]">Nom du speaker</label>
				<input type="text" id="lab_event_speaker_name" name="em_attributes[Speaker]" value=""><i><span style="color:red;"><b>*</b></span></i>
				<span id="lab_event_speaker_name_error"></span>
			</div>
			<div class="event-attributes">
				<label for="em_attributes[Speaker affiliation]">Affiliation</label>
				<input type="text" id="lab_event_speaker_affiliation" name="em_attributes[Speaker affiliation]" value=""><i><span style="color:red;"><b>*</b></span></i>
				<span id="lab_event_speaker_affiliation_error"></span>
			</div>
			<div class="event-attributes">
				<label for="em_attributes[Speaker web site]">Page internet du speaker : </label>
				<input type="text" name="em_attributes[Speaker web site]" value="">
			</div>
			<?php lab_locate_template('forms/event/group.php', true); ?>
		</div>
		<div class="event-extra-details">
			<div class="input">
				<?php if (get_option('dbem_categories_enabled')) {
					lab_locate_template('forms/event/categories-public.php', true);
				} ?>
				<?php if (get_option('dbem_tags_enabled')) {
					lab_locate_template('forms/event/tags-public.php', true);
				} ?>
			</div>
		</div>

		<h3 class="event-form-when"><?php esc_html_e('When', 'events-manager'); ?></h3>
		<div class="inside event-form-when">
			<?php
			if (empty($EM_Event->event_id) && $EM_Event->can_manage('edit_recurring_events', 'edit_others_recurring_events') && get_option('dbem_recurrence_enabled')) {
				lab_locate_template('forms/event/when-with-recurring.php', true);
			} elseif ($EM_Event->is_recurring()) {
				lab_locate_template('forms/event/recurring-when.php', true);
			} else {
				lab_locate_template('forms/event/when.php', true);
			}
			?>
		</div>

		<?php if (get_option('dbem_locations_enabled')): ?>
			<h3 class="event-form-where"><?php esc_html_e('Where', 'events-manager'); ?></h3>
			<div class="inside event-form-where">
				<?php lab_locate_template('forms/event/location.php', true); ?>
			</div>
		<?php endif; ?>

		<h3 class="event-form-details"><?php esc_html_e('Details', 'events-manager'); ?></h3>
		<div class="inside event-form-details">
			<div class="event-editor">
				<?php if (get_option('dbem_events_form_editor') && function_exists('wp_editor')): ?>
					<?php wp_editor($EM_Event->post_content, 'em-editor-content', array('textarea_name' => 'content')); ?>
				<?php else: ?>
					<textarea name="content" rows="10" style="width:100%"><?php echo $EM_Event->post_content ?></textarea>
					<br />
					<?php esc_html_e('Details about the event.', 'events-manager') ?> <?php esc_html_e('HTML allowed.', 'events-manager') ?>
				<?php endif; ?>
			</div>

		</div>

		<?php if ($EM_Event->can_manage('upload_event_images', 'upload_event_images')): ?>
			<h3><?php esc_html_e('Event Image', 'events-manager'); ?></h3>
			<div class="inside event-form-image">
				<?php lab_locate_template('forms/event/featured-image-public.php', true); ?>
			</div>
		<?php endif; ?>


		<input type="hidden" name="data_privacy_consent" value="1">
	</div>
	<button id="lav_event_submit" type="button" class="btn btn-success"><?php echo esc_attr(sprintf(__('Submit %s', 'events-manager'), __('Event', 'events-manager'))); ?></button>
	<p class="submit">
		<?php if (empty($EM_Event->event_id)): ?>

		<?php else: ?>
			<button id="toto" class='button-primary' value='<?php echo esc_attr(sprintf(__('Update %s', 'events-manager'), __('Event', 'events-manager'))); ?>' />
		<?php endif; ?>
	</p>
	<input type="hidden" name="event_id" value="<?php echo $EM_Event->event_id; ?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('wpnonce_event_save'); ?>" />
	<input type="hidden" name="action" value="lab_event_save" />
	<?php if (!empty($_REQUEST['redirect_to'])): ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr($_REQUEST['redirect_to']); ?>" />
	<?php endif; ?>
</form>