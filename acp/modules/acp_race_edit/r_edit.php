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
if (!isset($_SESSION['litotex_start_acp']) || !isset($_SESSION['userid']))
{
    header('LOCATION: ./../../index.php');
    exit();
}

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');

$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "acp_race_edit";
$menu_name = "Rasseneditor";
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);

if ($action == "main")
{
    $out_a = array();
    $result = $db->query("select * from cc" . $n . "_rassen");
    while ($row = $db->fetch_array($result))
    {
        $out_a[] = $row;
    }

    $tpl->assign('modules', $out_a);
    template_out('r_edit.html', $modul_name);
}
elseif ($action == "save_rass")
{
    if (isset($_GET['r_id']))
    {
        $save_id = filter_var($_GET['r_id'], FILTER_SANITIZE_NUMBER_INT);
        if ($save_id < 1 || $save_id > 4)
        {
            error_msg("$ln_error_17");
        }
        $save_name = filter_var($_POST['rassname'], FILTER_SANITIZE_STRING);
        $save_description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $save_description_en = filter_var($_POST['description_en'], FILTER_SANITIZE_STRING);

        $update = $db->query("UPDATE cc" . $n . "_rassen SET rassenname = '" . $save_name . "', descriprion = '" . $save_description .
            "', descriprion_en= '" . $save_description_en . "' WHERE rassenid='" . $save_id . "'");
    }
    redirect($modul_name, 'r_edit', 'main');
}
