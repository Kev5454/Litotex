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

$time_start = explode(' ', substr(microtime(), 1));
$time_start = $time_start[1] + $time_start[0];

if (!isset($db))
{

    require ("./config.php");
    require ("./class_db_mysql.php");
    require ("./../options/options.php");
    require ("./functions.php");


    $key = (isset($_REQUEST['key']) ? filter_var($_REQUEST['key'], FILTER_SANITIZE_STRING) : null);
    $server = (isset($_REQUEST['sid']) ? filter_var($_REQUEST['sid'], FILTER_SANITIZE_STRING) : null);
    if (!isset($op_update_key) || empty($key) || empty($server) || strlen($key) != 32 || $op_update_key != $key)
    {
        exit();
    }
    $db = new db($dbhost, $dbuser, $dbpassword, $dbbase, $dbport);
    $n = (int)$server;
}

$time_start = time();
trace_msg("resreload update start:" . date("d.m.Y (H:i:s)", $time_start), 888);


$result = $db->query("SELECT * FROM cc" . $n . "_countries");
while ($row = $db->fetch_array($result))
{
    $store_max = $op_set_store_max * (($row['store'] + 1) * $op_store_mulit);
    $SetRes1 = calcRes('res1', $row, 1);
    $SetRes2 = calcRes('res2', $row, 1);
    $SetRes3 = calcRes('res3', $row, 1);
    $SetRes4 = calcRes('res4', $row, 1);

    $tr_msg = "crontab country_id: " . $row['islandid'] . "  res1:$SetRes1 res2:$SetRes2 res3:$SetRes3 res4:$SetRes4 storemax:$store_max";
    trace_msg($tr_msg, 77);
    print ("$tr_msg <br>");
    $db->query("UPDATE cc" . $n . "_countries SET res1='$SetRes1', res2='$SetRes2', res3='$SetRes3', res4='$SetRes4', lastressources='" .
        $time_start . "' WHERE islandid='" . $row['islandid'] . "'");
}
$time_end = explode(' ', substr(microtime(), 1));
$time_end = $time_end[1] + $time_end[0];
$run_time = $time_end - $time_start;
$end_msg = "ResourceReload DONE  time: " . prettyNumber($run_time, 5) . " sec. ";

Trace_msg("$end_msg", 888);
print ("$end_msg <br>");
