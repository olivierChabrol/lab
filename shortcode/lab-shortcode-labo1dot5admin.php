<?php

function lab_labo1_5admin() {

    if ( ! is_user_logged_in() ) {
        return "Page accessible qu’aux utilisateurs connect&eacute;s";
    }
/*    $count = lab_labo1dot5_getRowNum();
    print('<a href="#">&lt;</a>');
    for ($i =  ; $i < $count[]; $i++)
    {
        print('<a href="#">'.($i+1).'</a>');
    }
    print('<a href="#">&gt;</a>');*/
?>


<table class="table" id="travel">
    <thead>
        <tr>
            <td><input type="button" id="update_mission" class="btn btn-info" value="⟳"></td>
            <td colspan="1"><select id="page" class="form-control" onchange="changePage(this)"> </select></td>
            <td colspan="2"><input type="text" id="filter_user_name" name="filter_user_name" class="form-control" placeholder="Chercher un nom"/></td>
            <td colspan="2"><select id="filter_group" name="filter_group" onchange="filterGroupFunction(this)" class="form-control">
                <option value="">Filtrer groupe</option>
                <option value="2">Analyse Appliquée</option>
                <option value="3">Arithmétique, Géométrie, Logique et Représe...</option>
                <option value="4">Analyse, Géométrie, Topologie</option>
                <option value="5">Mathématiques de l’Aléatoire</option>
                <option value="6">Géométrie, Dynamique, Arithmétique, Combina...</option>
                <option value="7">Services d'Appui à la Recherche</option>
                </select>
            </td>
            <td colspan="3"><select id="orderBy" name="orderBy" onchange="orderByFunction(this)" class="form-control">
                            <option value="mission_id">Trier par défaut (N°mission)</option>
                            <option value="date_submit DESC">Par date demandé (nouveauté)</option>
                            <option value="statut">Statut accord (Non)</option>
                            <option value="statut DESC">Status accord (Oui)</option>
                            <option value="closed">Clôture (Non)</option>
                            <option value="closed DESC">Clôture (Oui)</option>
                            <option value="mission_cost">Avec frais</option>
                            <option value="mission_cost DESC">Sans frais</option>
                            </select>
            </td>
            <td colspan="2"><select id="mission_year" class="form-control" onchange="changeYear(this)"></select></td>
            <!--<td colspan="2"><select id="filter_closed" name="filter_closed" onchange="filterClosedFunction(this)" class="form-control">
                            <option value="">Filtrer clôture</option>
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                            </select>
            </td>-->
            <td colspan="2"><a class="btn btn-info" href="/wp-content/plugins/lab/lab_export.php?do=labo1.5&filename=labo1.5.xls">
                Exporter labo1.5
            </a></td>
            <td colspan="2"><a class="btn btn-info" href="">
                Exporter
            </a></td>
        </tr>
        <tr>
            <th></th>
            <th>N°Mission</th>
            <th>Prénom Nom</th>
            <th>Groupe</th>
            <th>Motif de mission</th>
            <th>Frais </th>
            <th>Frais total estimé</th>
            <th>Frais total maximum </th>
            <th>Prise en charge par labo</th>
            <th>Credit&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
            <th>Commentaire</th>
            <th>Statut Accord&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
            <th>Clôture&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
            <th>Date de demande</th>
            <th></th>
        </tr>
    </thead>
    <tbody id="list_mission">

    </tbody>
</table>
<?php
}

