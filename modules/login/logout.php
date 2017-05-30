<?PHP

/*
************************************************************
Litotex Browsergame - Engine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberrechtlich gesch�tzt.

F�r jegliche Fehler oder Sch�den, die durch diese Software
auftreten k�nnten, �bernimmt der Autor keine Haftung.

Alle Copyright - Hinweise innerhalb dieser Datei
d�rfen WEDER entfernt, NOCH ver�ndert werden.
************************************************************
Released under the GNU General Public License
************************************************************

*/

$modul_name = "logout";
require ("./../../includes/global.php");


if (!isset($_SESSION['userid']))
{
    show_error('LOGIN_ERROR', 'core');
    exit();
}

if (is_modul_name_aktive('login') == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}


/** set user inactive when logout **/
$db->update("UPDATE cc" . $n . "_users SET lastactive=lastactive-'3600' WHERE userid='" . $_SESSION['userid'] . "'");

/** end a session time **/

unset($_SESSION['userid'], $_SESSION['ttest'], $_SESSION['ttestid']);
header("LOCATION: " . getBaseUrl(true) . 'index.php');