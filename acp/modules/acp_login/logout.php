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
Diese Software ist urheberechtlich gesch�tzt.

F�r jegliche Fehler oder Sch�den, die durch diese Software
auftreten k�nnten, �bernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei 
d�rfen NICHT entfernt und NICHT ver�ndert werden. 
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


$db->query("UPDATE cc" . $n . "_users SET lastactive=lastactive-'3600' WHERE userid='" . $_SESSION['userid'] . "'");
session_unregister('userid');
session_unregister('lang');

header("LOCATION: " . $_SESSION['litotex_start_url'] . 'acp/index.php');