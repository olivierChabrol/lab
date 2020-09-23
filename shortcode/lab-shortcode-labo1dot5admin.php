<?php

function lab_labo1_5admin() {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_labo1dot5_historic`";

     
    $results = $wpdb->get_results($sql);  
    
?>
<table border="1">
    <thead>
        <tr>
          <th>travel_id</th>
          <th>user_id</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $results as $myrow ) {  ?>
        <tr>
          <td><?php echo $myrow->travel_id; ?></td>
          <td><?php echo $myrow->user_id; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php
}