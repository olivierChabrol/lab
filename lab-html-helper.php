<?php
require_once("lab-admin-params.php");

/**
 * Generate HTML <select> field
 * @param String $htmlId : id of the select
 * @param String $htmlName : name of the select
 * @param String $htmlClass : css class of the select
 * @param String $fctCallback : function to call to perform the select must return an array([id:0,value:"your value"])
 * @param String $fctArgs : function to call arguments
 * @param String $defaultValue : add an default <option> in the select, must be this form : ex. : array("value"=>0,"label"=>"None")
 * @param String $selectedValue $selectedValue
 * @param String $attrMapping mapping to add attribute [attributeName=>objectFieldName]
 */
function lab_html_select($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs = null, $defaultValue = null, $selectedValue = null, $attrMapping = null) {
    echo lab_html_select_str($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs, $defaultValue, $selectedValue, null, $attrMapping);
}


function lab_html_select_str($htmlId, $htmlName, $htmlClass, $fctCallback, $fctArgs = null, $defaultValue = null, $selectedValue = null, $idValues = null, $attrMapping = null) {
    $output ='<select id="'.$htmlId.'" name="'.$htmlName.'" class="'.$htmlClass.'" df="'.$selectedValue.'">';
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
            $output .= "<option value=\"".$r->id."\"".(($selectedValue!=null && $selectedValue==$r->id)?"selected":"");
            if ($attrMapping != null)
            {
                foreach($attrMapping as $k=>$v)
                {
                    $output .= " ".$k."=\"".$r->{$v}."\"";
                }
            }
            $output .= ">".$r->value."</option>";
        }
    }
    else {
        foreach ( $results as $r ) {
            $output .= "<option value=\"".$r->{$idValues["id"]}."\"".(($selectedValue!=null && $selectedValue==$r->{$idValues["id"]})?"selected":"");
            if ($attrMapping != null)
            {
                foreach($attrMapping as $k=>$v)
                {
                    $output .= " ".$k."=\"".$r->{$v}."\"";
                }
            }
            $output .= ">".$r->{$idValues["value"]}."</option>";
        }
    }
    $output .= "</select>";
    return $output;
}