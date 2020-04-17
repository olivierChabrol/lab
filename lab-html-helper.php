<?php
/**
 * Generate HTML <select> field
 * @param htmlId : id of the select
 * @param htmlName : name of the select
 * @param htmlClass : css class of the select
 * @param fctCallback : function to call to perform the select must return an array([id:0,value:"your value"])
 * @param fctArgs : function to call arguments
 * @param defaultValue : add an default <option> in the select, must be this form : ex. : array("value"=>0,"label"=>"None")
 */
function lab_html_select($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs = null, $defaultValue = null) {
    $output ='<select id="'.$htmlId.'" name="'.$htmlName.'" class="'.$htmlClass.'">';
    $results = null;
    if ($fctArgs == null) {
        $results = $fctCallback();
    } else {
        $results = $fctCallback($fctArgs);
    }
    if ($defaultValue != null) {
        $output .= "<option value =".$defaultValue["value"].">".$defaultValue["label"]."</option>";
    }

    foreach ( $results as $r ) {
    $output .= "<option value =".$r->id.">".$r->value."</option>";
    }
    $output .= "</select>";
    echo $output;
}