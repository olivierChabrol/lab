<?php

function lab_labo1_5admin() {

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
            <td colspan="3"><input type="text" id="filter_user_name" name="filter_user_name" class="form-control" placeholder="Chercher un nom"/></td>
            <td colspan="3"><select id="orderBy" name="orderBy" onchange="orderByFunction(this)" class="form-control">
                            <option value="">Tirer par défaut (N°mission)</option>
                            <option value="date_submit">Par date demendé (les anciens)</option>
                            <option value="date_submit DESC">Par date demendé (les récents)</option>
                            <option value="statut">Par status (non valide)</option>
                            <option value="statut DESC">Par status (valide)</option>
                            </select></td>
            <td colspan="2"><select id="page" class="form-control" onchange="page(this)"> </select></td>
            <td colspan="2"><a class="btn btn-info" href="/wp-content/plugins/lab/lab_export.php?do=labo1.5&filename=labo1.5.xls">
                Export All
            </a></td>
            <td><input type="button" id="update_mission" class="btn btn-info" value="⟳"></td>
        </tr>
        <tr>
            <th></th>
            <th>N°Mission</th>
            <th>Nom</th>
            <th>Motif de mission</th>
            <th>Frais </th>
            <th>Prise en charge par labo</th>
            <th>Credit</th>
            <th>Commentaire</th>
            <th>Statut</th>
            <th>Date demende</th>
            <th></th>
        </tr>
    </thead>
    <tbody id="list_mission">

    </tbody>
</table>
<?php
}

