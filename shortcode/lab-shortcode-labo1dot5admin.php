<?php

function lab_labo1_5admin() {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_labo1dot5`";

     
    $results = $wpdb->get_results($sql);  
    
?>
<table class="table table-hover table-bordered" id="travel">
    <thead>
        <tr>
          <th>Choisir</th>
          <th>Travel ID</th>
          <th>Pays de départ</th>
          <th>Ville de départ</th>
          <th>Pays d'arrivee</th>
          <th>Ville d'arrivee</th>
          <th>Date de départ</th>
          <th>Mode de tansport</th>
          <th>Aller/Retour</th>
          <th>Status</th>
          <th>Modif</th>
        </tr>
        <tr>
            <td><input type="checkbox" onclick="checkAll(this)"> Tout</td>
            <td colspan="3"><a href="javascrip;" class="btn btn-danger btn-sm" role="button" onclick="delAll(this)">Suprimer les trajets choisis</a></td>
            <td colspan="3"><input type="text" id="filter_user_name" name="filter_user_name" class="form-control" placeholder="Chercher un nom"/></td>
            <td colspan="4"><select id="orderBy" name="orderBy" onchange="orderBy(this)" class="form-control">
            <option value="">Tirer par ?</option>
            <option value="travel_date">Date de départ (les anciens)</option>
            <option value="travel_date DESC">Date de départ (les récents)</option>
            <option value="status">Status (non valide)</option>
            <option value="status DESC">Status (valide)</option>
            </select>
            </td>

        </tr>
    </thead>
    <tbody id="list_travel">

    </tbody>
</table>
<h5>Ajouter ou Modifer un trajet</h5>
    <input type="hidden" id="travel_id" value=""/>
    <table class="table table-hover table-bordered" id="trajet">
        <tr>
            <th>Pays de départ</th>
            <td><input type="text" id="country_from" name="country_from" class="form-control"/></td>
            <th>Ville de départ</th>
            <td><input type="text" id="travel_from" name="travel_from" class="form-control"/></td>
        </tr>
        <tr>
            <th>Pays d'arrivee</th>
            <td><input type="text" id="country_to" name="country_to" class="form-control"/></td>
            <th>Ville d'arrivee</th>
            <td><input type="text" id="travel_to" name="travel_to" class="form-control"/></td>
        </tr>
        <tr>
            <th>Date de départ</th>
            <td><input type="date" id="travel_date" name="travel_date" class="form-control"/></td>
            <th>Mode de transport</th>
            <td><select id="means" name="means" class="form-control">
            <option value="">Choisissez une option</option>
            <option value="car">Voiture</option>
            <option value="train">Train</option>
            <option value="plane">Avion</option>
            <option value="bus">Car</option>
            <option value="none">Aucun</option>
            <option value="other">Autre</option>
            </select></td>
        </tr>
        <tr>
            <th>Un trajet aller/retour?</th>
            <td><select id="go_back" name="go_back" class="form-control">
            <option value="">Aller/Retour?</option>
            <option value="gosimple">Aller simple</option>
            <option value="goback">Aller Retour</option>
            </select></td>
            <th>Status</th>
            <td><select id="status" name="status" class="form-control">
            <option value="novalid">Non Valité</option>
            <option value="valid">Valité</option>
            </select></td>
        </tr>
        <tr>
            <td colspan="4">  
                <input type="button" value="Ajouter"  class="btn btn-success" id="add"/>
                <input type="button" value="Mise à jour"  class="btn btn-info" id="update"  onclick="update()" />
            </td>
        </tr>
    </table>
<?php
}