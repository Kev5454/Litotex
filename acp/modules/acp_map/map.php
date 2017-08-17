<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */
/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberechtlich geschützt.

Für jegliche Fehler oder Schäden, die durch diese Software
auftreten könnten, übernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei 
dürfen NICHT entfernt und NICHT verändert werden. 
************************************************************
Released under the GNU General Public License 
************************************************************  
*/

session_start();
if (!isset($_SESSION['litotex_start_acp']) || !isset($_SESSION['userid']))
{
    header('LOCATION: ./../../index.php');
    exit();
}

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');

$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "acp_map";
$menu_name = "Karteneditor";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);

if ($action == "main")
{
    $result = $db->query("SELECT max(x) as maximum FROM cc" . $n . "_crand ");
    $land = $db->fetch_array($result);

    $tpl->assign('op_map_size', $land['maximum']);
    template_out('map.html', $modul_name);
}
elseif ($action == "make_map")
{
    $size = intval(trim($_POST['x']));
    $elemt_1 = intval(trim($_POST['elem1']));
    $elemt_2 = intval(trim($_POST['elem2']));

    if ($size <= 0)
    {
        error_msg("Falsche Angabe der Kartengröße (Eingabe:$size)");
        exit();
    }

    $sql = '';
    $sql .= "DROP TABLE IF EXISTS cc" . $n . "_crand;";
    $sql .= "CREATE TABLE cc" . $n .
        "_crand (crand_id INT( 11 ) NOT NULL AUTO_INCREMENT ,x INT( 5 ) NOT NULL ,y INT( 5 ) NOT NULL ,used TINYINT( 1 ) NOT NULL DEFAULT '0',element_type INT( 2 ) NOT NULL DEFAULT '0',PRIMARY KEY ( crand_id ));";

    for ($x = 1; $x <= $size; $x++)
    {
        for ($y = 1; $y <= $size; $y++)
        {
            $sql .= "insert into cc" . $n . "_crand (x,y,used) VALUES('$x','$y','0');";
        }
    }

    $db->multi_query($sql);
    trace_msg("Admin map change drop table", 112);
    trace_msg("Admin map change create table", 112);
    trace_msg("Admin map change create Elemet1", 112);
    
    $sql = '';
    // perzufall elemente 1 setzen( berge ?? )
    for ($x = 1; $x <= $elemt_1; $x++)
    {
        srand(microtime() * 1000000);
        $Zufall_x = rand(1, $size);
        $zufall_y = rand(1, $size);

        $sql .= "update cc" . $n . "_crand set element_type ='1',used='1' where x='$Zufall_x' and y='$zufall_y';";
    }
    trace_msg("Admin map change create Elemet2", 112);
    // perzufall elemente 2 setzen( see ?? )
    for ($x = 1; $x <= $elemt_2; $x++)
    {
        srand(microtime() * 1000000);
        $Zufall_x = rand(1, $size);
        $zufall_y = rand(1, $size);

        $sql .= "update cc" . $n . "_crand set element_type ='2',used='1' where x='$Zufall_x' and y='$zufall_y';";
    }
    
    $db->multi_query($sql);

    $sql = '';
    trace_msg("Admin map change change User Pos", 112);
    // umsetzen der länder auf neue koordinaten
    $result_l = $db->query("SELECT * FROM cc" . $n . "_countries");
    while ($row_l = $db->fetch_array($result_l))
    {
        $countrie_id = $row_l['islandid'];
        // per zufall verschieben
        srand(microtime() * 1000000);
        $Zufall_x = rand(1, $size);
        $zufall_y = rand(1, $size);

        $gefunden = 0;
        while ($gefunden == 0)
        {
            $result = $db->query("SELECT * FROM cc" . $n . "_crand where used = '0' and element_type ='0' and x=$Zufall_x and y=$zufall_y");
            $land = $db->fetch_array($result);

            $land_x = $land['x'];
            $land_y = $land['y'];
            $gefunden = ($land['used'] == '0' ? 1 : 0);
        }

        trace_msg("Admin map change change User Pos county:$countrie_id new pos -> $land_x:$land_y", 112);

        $sql .= "update cc" . $n . "_countries set x='$Zufall_x',y='$zufall_y' where islandid='$countrie_id';";
        $sql .= "update cc" . $n . "_crand set used ='1' where x='$Zufall_x' and y='$zufall_y';";
    }
    $db->multi_query($sql);


    trace_msg("Admin map change write Options", 112);
    $db->query("update cc" . $n . "_menu_admin_opt set value ='$size' where varname='op_map_size'");

    require (LITO_ROOT_PATH . "includes/class_options.php");
    $option = new option(LITO_ROOT_PATH . "options/");
    $option->write();

    redirect($modul_name, 'map', 'main');
}
