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


$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "acp_sample";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');

if ($action == "main")
{
    $sql = "SELECT * from  cc" . $n . "_users";
    $result_users = $db->query($sql);

    while ($row_g = $db->fetch_array($result_users))
    {
        $daten[] = $row_g;
    }

    $tpl->assign('daten', $daten);
    $tpl->assign('test1', "http://www.blabla.de");
    
    template_out('sample.html', $modul_name);
}