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

$modul_name = "acp_debug";
$menu_name = "Debuger";
$tpl->assign('menu_name', $menu_name);

if ($action == "main")
{
    $optionbit = "";
    $result = $db->query("SELECT * FROM cc" . $n . "_users");
    while ($row = $db->fetch_array($result))
    {
        $optionbit .= "<option value=\"" . $row['userid'] . "\">" . $row['username'] . "</option>";
    }
    $tpl->assign('optionbit', $optionbit);
    template_out('debug.html', $modul_name);

}
elseif ($action == "debug_show")
{
    $debugs_del = (isset($_POST['deletes']) ? 1 : 0);
    $debug_all_user = (isset($_POST['alluser']) ? 1 : 0);
    $debugs_uid = filter_var($_POST['debug_uid'], FILTER_SANITIZE_NUMBER_INT);
    $debugs_level = filter_var($_POST['level'], FILTER_SANITIZE_NUMBER_INT);

    //prüfen ob gelöscht werden soll
    if ($debugs_del == 1)
    {
        $result = $db->query("delete from cc" . $n . "_debug");
        redirect($modul_name, 'debug', 'main');
    }

    //zusammensetzen der Where klausel
    $debug_where = "";
    $debug_sql = "SELECT * FROM cc" . $n . "_debug ";
    if (!$debug_all_user && $debugs_uid)
    {
        $debug_where = "WHERE  fromuserid  ='$debugs_uid'";
    }

    if ($debugs_level)
    {
        if ($debug_where)
        {
            $debug_where = "$debug_where AND  db_type =$debugs_level";
        }
        elseif (!$debug_where)
        {
            $debug_where = "WHERE  db_type=$debugs_level";
        }

    }
    if ($debug_where)
    {
        $debug_sql = "$debug_sql $debug_where";
    }

    $optionbit = "";
    $inhalt = array();
    $result = $db->query($debug_sql);
    $i = 0;
    while ($row = $db->fetch_array($result))
    {
        $debug_date = date("d.m.Y, H:i:s", $row['db_time']);
        $inhalt[$i]['debug_id'] = $row['debug_id'];
        $inhalt[$i]['db_type'] = $row['db_type'];
        $inhalt[$i]['fromuserid'] = $row['fromuserid'];
        $inhalt[$i]['db_text'] = $row['db_text'];
        $inhalt[$i]['debug_date'] = $debug_date;
        $i++;
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_users");
    while ($row = $db->fetch_array($result))
    {
        $optionbit .= "<option value=\"" . $row['userid'] . "\">" . $row['username'] . "</option>";
    }
    
    $tpl->assign('optionbit', $optionbit);
    $tpl->assign('debug_output', $inhalt);
    template_out('debug.html', $modul_name);
}
