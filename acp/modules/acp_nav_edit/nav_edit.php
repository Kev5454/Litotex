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
$modul_name = "acp_nav_edit";
$menu_name = "Navigationmanager";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);

if ($action == 'new')
{
    $design_id = (!isset($_GET['design_id']) ? 1 : filter_var($_GET['design_id'], FILTER_SANITIZE_NUMBER_INT));
    $_POST['ingame'] = (!isset($_POST['ingame']) ? 0 : filter_var($_POST['ingame'], FILTER_SANITIZE_NUMBER_INT));

    if (isset($_POST['title']) && isset($_POST['url']) && isset($_POST['ingame']))
    {
        $lastpos = $db->query("SELECT `sort_order` FROM `cc" . $n . "_menu_game` ORDER BY `sort_order` DESC");
        $lastpos = $db->fetch_array($lastpos);
        $lastpos = (isset($lastpos['position']) ? $lastpos['position'] + 1 : 1);

        $db->query("INSERT INTO `cc" . $n .
            "_menu_game` (`menu_game_name`, `menu_game_link`, `sort_order`, `menu_art_id`, `ingame`, `modul_id`, `design_id`) VALUES ('" .
            $db->escape_string($_POST['title']) . "', '" . $db->escape_string($_POST['url']) . "', '" . $lastpos . "', 0, " . $_POST['ingame'] .
            ", 12, " . $design_id . ")");
    }
    redirect($modul_name, 'nav_edit', 'main');
}
elseif ($action == 'delete')
{
    if (!isset($_GET['id'])) die('Es ist ein schwerer Fehler aufgetreten!');

    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $db->query("DELETE FROM `cc" . $n . "_menu_game` WHERE `menu_game_id` = '" . $id . "'");

    redirect($modul_name, 'nav_edit', 'main');
}
elseif ($action == 'change')
{
    if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_POST['change_title']) || !isset($_POST['change_url']))
    {
        die('Es ist ein schwerer Fehler aufgetreten!');
    }

    $change_ingame = (!isset($_POST['change_ingame']) ? 0 : filter_var($_POST['change_ingame'], FILTER_SANITIZE_NUMBER_INT));

    $db->query("UPDATE `cc" . $n . "_menu_game` SET `menu_game_name` = '" . $db->escape_string($_POST['change_title']) .
        "', `menu_game_link` = '" . $db->escape_string($_POST['change_url']) . "', `ingame` = '" . $change_ingame .
        "' WHERE `menu_game_id` = '" . $_GET['id'] . "'");

    redirect($modul_name, 'nav_edit', 'main');
}
elseif ($action == 'change_select')
{
    $id = (!isset($_GET['id']) ? -1 : $_GET['id']);
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

    redirect($modul_name, 'nav_edit', 'main', array('changeID' => $id));
}
elseif ($action == "main")
{
    $design_id = (!isset($_GET['design_id']) ? 1 : $_GET['design_id']);
    $design_id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

    $change = (isset($_GET['changeID']) ? filter_var($_GET['changeID'], FILTER_SANITIZE_NUMBER_INT) : -1);

    $navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = " . $design_id .
        " ORDER BY `sort_order` ASC");
    $id = 0;
    while ($row = $db->fetch_array($navi_db))
    {
        $navi[$row['menu_art_id']][$id]['id'] = $row['menu_game_id'];
        $navi[$row['menu_art_id']][$id]['title'] = $row['menu_game_name'];
        $navi[$row['menu_art_id']][$id]['url'] = $row['menu_game_link'];
        $navi[$row['menu_art_id']][$id]['ingame'] = $row['ingame'];
        if ($change == $row['menu_game_id'])
        {
            $navi[$row['menu_art_id']][$id]['change'] = true;
        }
        else  $navi[$row['menu_art_id']][$id]['change'] = false;
        $id++;
    }
    if (!isset($navi[0])) $navi[0] = array();
    if (!isset($navi[1])) $navi[1] = array();
    if (!isset($navi[2])) $navi[2] = array();
    $tpl->assign('design_id', $design_id);
    $tpl->assign('navi_up', $navi[0]);
    $tpl->assign('navi_left', $navi[1]);
    $tpl->assign('navi_right', $navi[2]);
    $designs = array();
    $designs_q = $db->query("SELECT `design_id`, `design_name` FROM `cc" . $n . "_desigs`");
    $i = 0;
    while ($design = $db->fetch_array($designs_q))
    {
        $designs[$i]['id'] = $design['design_id'];
        $designs[$i]['name'] = $design['design_name'];
        $i++;
    }
    $tpl->assign('designs', $designs);
    template_out('list.html', $modul_name);
}
elseif ($action == 'move')
{
    $order = filter_var($_GET['order'], FILTER_SANITIZE_STRING);
    if (isset($order))
    {
        $order = explode(';', $order);
        $pos_style = 0;
        foreach ($order as $pos => $id)
        {
            if (!is_numeric($id))
            {
                if ($id == 'ign') continue;
                switch ($id)
                {
                    case 'up':
                        $pos_style = 0;
                        break;
                    case 'left':
                        $pos_style = 1;
                        break;
                    case 'right':
                        $pos_style = 2;
                        break;
                    default:
                        continue;
                }
            }
            $db->query("UPDATE `cc" . $n . "_menu_game` SET `menu_art_id` = '" . $pos_style . "', `sort_order` = '" . $pos .
                "' WHERE `menu_game_id` = '" . $id . "'");
        }
    }
    echo 'Gespeichert!';
}
