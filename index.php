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

require ('./includes/global.php');

$modul_name = "index";

if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
else  $action = "main";


if ($action == "main")
{
    //$tpl ->display("login/login.html");
    $tpl->assign('if_disable_menu', 1);

    template_out('index.html', $modul_name);
}
echo "geht";
