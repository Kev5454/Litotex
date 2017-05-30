<?PHP

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
    unset($_SESSION);
    header("LOCATION: ../acp_login/login.php");
    exit();
}

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');


$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main');

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

if ($action == "debug_show")
{
    $debugs_del = (isset($_POST['deletes']) ? 1 : 0);
    $debugs_uid = (int)($_POST['debug_uid']);
    $debugs_level = (int)($_POST['level']);
    $debug_all_user = (isset($_POST['alluser']) ? 1 : 0);

    //pr�fen ob gel�scht werden soll
    if ($debugs_del == 1)
    {
        $result = $db->delete("delete from cc" . $n . "_debug");
        header("LOCATION: debug.php");
        exit();
    }

    //zusammensetzen der Where klausel
    $debug_where = "";
    $debug_sql = "SELECT * FROM cc" . $n . "_debug ";
    if (!$debug_all_user)
    {
        if ($debugs_uid)
        {
            $debug_where = "WHERE  fromuserid  ='$debugs_uid'";
        }

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
    exit();
}