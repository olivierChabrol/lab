<label for="wp_lab_event_title">Nom du seminaire</label>
    <input type="text" name="lab_eventTitle" id="wp_lab_event_title" value="" size="80"/><span id="lab_event_id"></span><br>
    <label id="wp_lab_event_label"></label><span id="wp_lab_event_date"></span>
    <input type="hidden" id="lab_searched_event_id" name="lab_searched_event_id" value=""/>
    <br>
<?php lab_locate_template('forms/event/categories-public.php',true); ?>
<br><a href="#" class="page-title-action" id="lab-button-change-category">Modifier la categorie d'un evenement</a>




