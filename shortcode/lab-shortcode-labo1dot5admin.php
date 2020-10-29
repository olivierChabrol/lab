<?php

function lab_labo1_5admin() {

/*    $count = lab_labo1dot5_getRowNum();
    print('<a href="#">&lt;</a>');
    for ($i = 0 ; $i < $count[0]; $i++)
    {
        print('<a href="#">'.($i+1).'</a>');
    }
    print('<a href="#">&gt;</a>');*/
?>


<table class="table table-hover table-bordered" id="travel">
    <thead>
        <tr>
            <td colspan="3"><input type="text" id="filter_user_name" name="filter_user_name" class="form-control" placeholder="Chercher un nom"/></td>
            <td colspan="2"><select id="orderBy" name="orderBy" onchange="orderByFunction(this)" class="form-control">
                            <option value="">Tirer par ?</option>
                            <option value="travel_date">Date de départ (les anciens)</option>
                            <option value="travel_date DESC">Date de départ (les récents)</option>
                            <option value="status">Status (non valide)</option>
                            <option value="status DESC">Status (valide)</option>
                            </select></td>
            <td colspan="2"><select id="page" class="form-control" onchange="page(this)"> </select></td>
            <td colspan="1"><a class="btn btn-info" href="/wp-content/plugins/lab/lab_export.php?do=labo1.5&filename=labo1.5.xls">
                Export All
            </a></td>
        </tr>
        <tr>
            <th>N°Mission</th>
            <th>Nom</th>
            <th>Motif de mission</th>
            <th>Frais </th>
            <th>Prise en charge par labo</th>
            <th>Credit</th>
            <th>Commentaire</th>
            <th>Statut</th>
            <th>Date enregistré</th>
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

