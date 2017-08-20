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
if (!isset($_SESSION['litotex_start_g']) || !isset($_SESSION['userid']))
{
    echo ("error");
    exit();
}

$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = 'core';
require ($_SESSION['litotex_start_g'] . 'includes/global.php');

if (!isset($_SESSION['userid']))
{
    echo ("error");
    exit();
}

if ($action == "main")
{

    echo ("main");
}
if ($action == "gettime")
{
    echo (date("d.m.Y H:i:s", time()));
    exit();
}

if ($action == "get_b_count")
{

    if (intval($userdata['rassenid']) <= 0)
    {
        exit();
    }


    $result = $db->query("SELECT count(groupid) as anz  FROM cc" . $n . "_groups where group_status =1 and to_userid ='" . $userdata['userid'] .
        "'  ");
    $row = $db->fetch_array($result);
    if (intval($row['anz']) > 0)
    {

        $module = get_modulname(9);
        $battle_modul_org = "./../" . $module[0] . "/" . $module[1];
        $ret_msg = "<a href=\"$battle_modul_org\"><img src=\"" . LITO_IMG_PATH_URL . $module[0] . "/battle.png\" border=\"0\"> Du wirst von " .
            $row['anz'] . " Gruppe(n) angegriffen !!!</a>";

    }
    else
    {
        $ret_msg = "";

    }
    echo ($ret_msg);

}
if ($action == "get_msg_count")
{

    if (intval($userdata['rassenid']) <= 0)
    {
        exit();
    }


    $new_msg_count = get_new_msg_count();

    if (intval($new_msg_count) > 0)
    {
        $module = get_modulname(6);
        $msg_modul_org = "./../" . $module[0] . "/" . $module[1];
        $ret_msg = "<a href=\"$msg_modul_org\"><img src=\"" . LITO_IMG_PATH_URL . $module[0] . "/newpost.png\" border=\"0\"> {$new_msg_count} neue Nachrichten</a>";
    }
    else
    {
        $ret_msg = "";

    }
    echo ($ret_msg);
}

?>