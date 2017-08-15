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


require ('../../includes/global.php');
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'change');
$modul_name = "lang";

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}


if ($action == "change")
{
    $newLang = (isset($_REQUEST['lang']) && strlen($_REQUEST['lang']) == 2 ? filter_var($_REQUEST['lang'],
        FILTER_SANITIZE_STRING) : 'de');
    $fileName = LITO_LANG_PATH . 'core' . DIRECTORY_SEPARATOR . 'lang_' . $newLang . '.php';
    if (!file_exists($fileName))
    {
        show_error('LANG_NOT_FOUND_ERROR', 'core');
    }

    if (isset($_SESSION['userid']))
    {
        $db->query("UPDATE cc" . $n . "_users SET lang='" . $newLang . "' WHERE userid='" . $_SESSION['userid'] . "'");
    }
    $_SESSION['lang'] = $newLang;
    template_out('lang.html', $modul_name);
    exit();
}
