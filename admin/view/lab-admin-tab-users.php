<?php

function lab_admin_tab_user()
{
?>
  <table class="form-table" role="presentation">
    <tr class="user-rich-editing-wrap">
      <th scope="row">
        <label for="lab_user_name">Nom de l'utilisateur</label>
      </th>
      <td>
        <input type="text" name="lab_user_email" id="lab_user_search" value="" size="80" /><span id="lab_user_id"></span><br>
        <input type="hidden" id="lab_user_search_id" name="lab_user_search_id" value="" /><br>
      </td>
    </tr>
    <tr>
      <td>
        <label for="lab_user_left">Parti</label>
      </td>
      <td>
        <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date">Date de départ</label><input type="text" id="lab_user_left_date">
        <input type="hidden" id="lab_usermeta_id">
      </td>
    </tr>
  </table>
  <a href="#" class="page-title-action" id="lab_user_button_save_left">Modifier le statut de l'utilisateur</a>

<?php
}
