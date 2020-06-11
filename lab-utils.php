<?php

function beginsWith($string_n, $prefix)
{
    return strncmp($string_n, $prefix, strlen($prefix)) === 0;
}

function get_easter_datetime($year) {
    $base = new DateTime("$year-03-21");
    $days = easter_days($year);

    $base->add(new DateInterval("P{$days}D"));
    return $base->getTimestamp ();
}
/**
 * Titre : Détermine rapidement si un jour est férié (fetes mobiles incluses)                                         
 *                                                                                                                          
 *  URL   : https://phpsources.net/code_s.php?id=382
 *  Auteur           : Olravet                                                                                            
 *  Website auteur   : http://olravet.fr/                                                                                 
 *  Date édition     : 05 Mai 2008                                                                                        
 *  Date mise à jour : 17 Aout 2019                                                                                      
 *  Rapport de la maj:                                                                                                    
 *  - fonctionnement du code vérifié    
 *
 * @param [type] $timestamp
 * @return true, is non-working-day in France, false, otherwise
 */
function nonWorkingDay($timestamp)
{
    $jour = date("d", $timestamp);
    $mois = date("m", $timestamp);
    $annee = date("Y", $timestamp);
    $EstFerie = false;
    // dates fériées fixes
    if($jour == 1 && $mois == 1) $EstFerie = true; // 1er janvier
    if($jour == 1 && $mois == 5) $EstFerie = true; // 1er mai
    if($jour == 8 && $mois == 5) $EstFerie = true; // 8 mai
    if($jour == 14 && $mois == 7) $EstFerie = true; // 14 juillet
    if($jour == 15 && $mois == 8) $EstFerie = true; // 15 aout
    if($jour == 1 && $mois == 11) $EstFerie = true; // 1 novembre
    if($jour == 11 && $mois == 11) $EstFerie = true; // 11 novembre
    if($jour == 25 && $mois == 12) $EstFerie = true; // 25 décembre
    // fetes religieuses mobiles
    $pak = get_easter_datetime($annee);
    $jp = date("d", $pak);
    $mp = date("m", $pak);
    
    if($jp == $jour && $mp == $mois){ $EstFerie = true;} // Pâques
    $lpk = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak) , date("d", $pak) +1, date("Y", $pak) );
    $jp = date("d", $lpk);
    $mp = date("m", $lpk);

    if($jp == $jour && $mp == $mois){ $EstFerie = true; }// Lundi de Pâques
    $asc = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 39, date("Y", $pak) );
    $jp = date("d", $asc);
    $mp = date("m", $asc);
    $dates[] = date("d/m/Y", $asc);
    if($jp == $jour && $mp == $mois){ $EstFerie = true;}//ascension
    $pe = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak),
    date("d", $pak) + 49, date("Y", $pak) );
    $jp = date("d", $pe);
    $mp = date("m", $pe);
    if($jp == $jour && $mp == $mois) {$EstFerie = true;}// Pentecôte
    $lp = mktime(date("H", $asc), date("i", $pak), date("s", $pak), date("m", $pak),
    date("d", $pak) + 50, date("Y", $pak) );
    $jp = date("d", $lp);
    $mp = date("m", $lp);
    if($jp == $jour && $mp == $mois) {$EstFerie = true;}// lundi Pentecôte
    // Samedis et dimanches
    $jour_sem = jddayofweek(unixtojd($timestamp), 0);
    if($jour_sem == 0 || $jour_sem == 6) $EstFerie = true;
    return $EstFerie;
}

function getFirstMondayOfTheWeek($dateObj)
{
    $dayofweek = date('w', $dateObj);
    //echo $dayofweek."<br>";
    // if sunday
    if ($dayofweek < 1) {
        return strtotime('-6 days', $dateObj);
    }
    else {
        $aStr = '-'.($dayofweek-1).' days';
        return strtotime($aStr, $dateObj);
    }
}