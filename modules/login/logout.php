<?php

/*
************************************************************
Litotex BrowsergameEngine
https://litotex.info
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2017 K. Wehmeyer
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
    require ('../../includes/global.php');
    show_error("LOGIN_ERROR", 'core');
}

require ($_SESSION['litotex_start_g'] . 'includes/global.php');
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name="logout";

if (is_modul_name_aktive('login')==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}

/** set user inactive when logout **/
$db->query("UPDATE cc".$n."_users SET lastactive=lastactive-'3600' WHERE userid='".$_SESSION['userid']."'");

/** end a session time **/

session_unregister('userid');
session_unregister('lang');
session_unregister('ttest');
session_unregister('ttestid');

header("LOCATION: ".LITO_ROOT_PATH_URL.'index.php');