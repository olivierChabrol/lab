<?php

function lab_labo1_5admin() {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_labo1dot5`";

     
    $results = $wpdb->get_results($sql);  
    
?>
<table border="2" id="travel">
    <thead>
        <tr>
          <th>Travel ID</th>
          <th>Pays de départ</th>
          <th>Ville de départ</th>
          <th>Pays d'arrivee</th>
          <th>Ville d'arrivee</th>
          <th>Date de départ</th>
          <th>Mode de tansport</th>
          <th>Aller/Retour</th>
          <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $results as $myrow ) {  ?>
        <tr>
          <td><?php echo $myrow->travel_id; ?></td>
          <td><?php echo $myrow->country_from; ?></td>
          <td><?php echo $myrow->travel_from; ?></td>
          <td><?php echo $myrow->country_to; ?></td>
          <td><?php echo $myrow->travel_to; ?></td>
          <td><?php echo $myrow->travel_date; ?></td>
          <td><?php echo $myrow->means; ?></td>
          <td><?php echo $myrow->go_back; ?></td>
          <td><?php echo $myrow->status; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<p><button id="addVar">Ajouter un nouveau trajet</button></p>
<?php
}