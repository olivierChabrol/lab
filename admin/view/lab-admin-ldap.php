<?php
/**************************************************************************************************************
 * LDAP
 * 
 * lab_ldap_list_update($lc,$BASE) => function which displays all the "cn"s from LDAP.
 * 
 * get_ldap_data_from_mail($mail) => function which return name, surname, login and mail of
 *  a requested person from LDAP. It needs the mail of the person to work.
 * 
 **************************************************************************************************************/
function lab_ldap_pagination_Req() {
  wp_send_json_success(lab_ldap_pagination($_POST['pages'],$_POST['currentPage']));
}
function lab_ldap_list_update($lc,$BASE) {
  $value = isset($_POST['value']) ? $_POST['value'] : '5' ;
  $page = isset($_POST['page']) ? $_POST['page'] : '1' ;
  $pageVar = $page - 1;
  $result = ldap_search($lc,'ou=accounts,'.$BASE,"uid=*");
  ldap_sort($lc,$result,'cn');
  for($i = $pageVar; $i < $value; ++$i)
  {
    $ldapResult .= '<tr><td>'. ldap_get_entries($lc,$result)[$i]["cn"][0].'</td>
                    <td>*</td>
                </tr>';
  }
  return($ldapResult);
}
function get_ldap_data_from_mail($mail) {
  $lc        = ldap_connect("localhost","389");
  $base      = "ou=accounts,dc=i2m,dc=univ-amu,dc=fr";
  $filter    = "(mail=" . $mail . ")";
  $attrRead  = array("givenname", "sn", "mail", "uid");
  $result    = ldap_search($lc, $base, $filter, $attrRead) 
      or die ("Error in query");
  $entry     = ldap_get_entries($lc,$result);

  echo $entry[0]["sn"][0]        . " est le nom de famille recherché via mail.</br>" .
       $entry[0]["givenname"][0] . " est le prénom recherché via mail.</br>" .
       $entry[0]["uid"][0]       . " est le login recherché via mail.";
  ldap_close($lc);
}