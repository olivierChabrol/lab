<?php

function lab_labo1_5resp(){
    ?>
    <table class="table" id="d_mission_info">
        <tr>
            <th>N°Mission</th>
            <td id="d_mission_id"></td>
        </tr> 
        <tr>
            <th>Date de demande</th>
            <td id="d_mission_date_submit"></td>
            <th>Prénom et Nom</th>
            <td id="d_mission_first_last_name"></td>
        </tr>
        <tr>
            <th>Motif de mission</th>
            <td id="d_mission_motif"></td>
            <th>Frais</th>
            <td><select id="d_mission_cost" class="form-control">
                <option value="Avec frais">Avec frais</option>
                <option value="Sans frais">Sans frais</option>
                </select>
            </td>
        </tr>
        <tr>
            <th colspan="4">Prise en charge par le labo (après accord du responsable des crédits):
            <br>
            <br>
            <input type="checkbox" value="transport" name="d_mission_cost_cover[]">&nbsp;Transport&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="hebergement" name="d_mission_cost_cover[]">&nbsp;Hébergement&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="repas"  name="d_mission_cost_cover[]">&nbsp;Repas&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="inscription"  name="d_mission_cost_cover[]">&nbsp;Inscription
            </th>
        </tr>
        </tr>
            <tr>
            <th colspan="4">Frais annexes :&nbsp;&nbsp;&nbsp;
            <br>
            <br>
            <input type="checkbox" value="parking" name="d_mission_cost_cover[]">&nbsp;Parking&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="transportcommun" name="d_mission_cost_cover[]">&nbsp;Transport en commun&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="taxi" name="d_mission_cost_cover[]">&nbsp;Taxi&nbsp;&nbsp;&nbsp;
            </th>
        </tr>
        <tr>
            <th colspan="">Sur quels crédits</th>
            <td colspan="">
                <select id="d_mission_credit" class="form-control">
                <option value="Crédits du groupe">Crédits du groupe</option>
                <option value="ANR">ANR</option>
                <option value="Contrat de recherche">Contrat de recherche</option>
                <option value="Autre">Autre</option>
                </select>
            </td>
            <th>Frais total estimé</th>
            <td><input type="text" id="d_mission_cost_estimate" class="form-control" disabled="disabled"></td>
        </tr>
            <th colspan="">Frais total max</th>
            <td><input type="text" id="d_mission_cost_max" class="form-control" placeholder="€"></td>
        <tr>
            <td style="height:100px" colspan="4" >
            <textarea style="width:100%;height:100%;" class="form-control"id="d_mission_comment" disabled="disabled" placeholder="commentaire"></textarea>
            </td>
        </tr>
    </table>
    <table class="table" id="d_mission_trajet">
        <tr>
            <th>Ville de départ</th>
            <th>Ville d'arrivee</th>
            <th>Date de départ</th>
            <th>Date de retour</th>
            <th>Mode de transport</th>
        </tr>
        <tbody id="d_mission_trajet_list">

        </tbody>
    </table>
    &nbsp;&nbsp;&nbsp;<input type="button" value="Valider cette mission"  class="btn btn-success" id="d_mission_valide"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" value="Pas maintenant"  class="btn btn-info" id="d_mission_novalide"/>

    <?php
}