<?php
/**
 * Generate HTML <select> field
 * @param htmlId : id of the select
 * @param htmlName : name of the select
 * @param htmlClass : css class of the select
 * @param fctCallback : function to call to perform the select must return an array([id:0,value:"your value"])
 * @param fctArgs : function to call arguments
 * @param defaultValue : add an default <option> in the select, must be this form : ex. : array("value"=>0,"label"=>"None")
 * @param selectedValue $selectedValue
 */
function lab_html_select($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs = null, $defaultValue = null, $selectedValue = null) {
    echo lab_html_select_str($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs, $defaultValue, $selectedValue);
}


function lab_html_select_str($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs = null, $defaultValue = null, $selectedValue = null, $idValues = null) {
    $output ='<select id="'.$htmlId.'" name="'.$htmlName.'" class="'.$htmlClass.'">';
    $results = null;
    if ($fctArgs == null) {
        $results = $fctCallback();
    } else {
        $results = $fctCallback($fctArgs);
    }
    //$output .= "<option value =\"\">".$results."</option>";
    //$output .= "</select>";
    //return $output;
    if ($defaultValue != null) {
        $output .= "<option value =\"".$defaultValue["value"]."\" ".(($selectedValue!=null && $selectedValue==$defaultValue["value"])?"selected":"").">".$defaultValue["label"]."</option>";
    }
    if ($idValues == null) {
        foreach ( $results as $r ) {
            $output .= "<option value=\"".$r->id."\"".(($selectedValue!=null && $selectedValue==$r->id)?"selected":"").">".$r->value."</option>";
        }
    }
    else {
        foreach ( $results as $r ) {
            $output .= "<option value=\"".$r->{$idValues["id"]}."\"".(($selectedValue!=null && $selectedValue==$r->{$idValues["id"]})?"selected":"").">".$r->{$idValues["value"]}."</option>";
        }
    }
    $output .= "</select>";
    return $output;
}