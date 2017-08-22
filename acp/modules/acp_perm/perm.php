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
$modul_name = "acp_perm";
$menu_name = "Gruppenmanager";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);

if ($action == "save_mod")
{
    if (!isset($_POST['perm']) || !is_array($_POST['perm']))
    {
        error_msg('Schwerer Fehler! Formulardaten wurden nicht übergeben!');
        exit;
    }

    $sql = '';
    foreach ($_POST['perm'] as $id => $perm)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $perm = filter_var($perm, FILTER_SANITIZE_NUMBER_INT);

        $sql .= "UPDATE `cc" . $n . "_modul_admin` SET `perm_lvl` = '" . $perm . "' WHERE `modul_admin_id` = '" . $id . "';";
    }
    $db->multi_query($sql);
    redirect($modul_name, 'perm', 'listmod');
}
elseif ($action == "save_grp")
{
    if ((!isset($_POST['perm']) || !isset($_POST['name'])) && (!isset($_POST['new_name']) || !isset($_POST['new_lvl'])) || !
        is_array($_POST['perm']))
    {
        error_msg('Schwerer Fehler! Formulardaten wurden nicht übergeben!');
        exit;
    }
    if (isset($_POST['perm']) && isset($_POST['name']))
    {
        $sql = '';
        foreach ($_POST['perm'] as $id => $perm)
        {
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
            $perm = filter_var($perm, FILTER_SANITIZE_NUMBER_INT);
            $sql .= "UPDATE `cc" . $n . "_user_groups` SET `perm_lvl` = '" . $perm . "', `name` = '" . $db->escape_string($_POST['name'][$id]) .
                "' WHERE `id` = '" . $id . "';";
        }
        $db->multi_query($sql);
    }
    if (!empty($_POST['new_name']) && !empty($_POST['new_lvl']))
    {
        $new_lvl = filter_var($_POST['new_lvl'], FILTER_SANITIZE_NUMBER_INT);
        $db->query("INSERT INTO `cc" . $n . "_user_groups` (`perm_lvl`, `name`) VALUES ('" . $new_lvl . "', '" . $db->
            escape_string($_POST['new_name']) . "')");
    }
    redirect('acp_perm', 'perm', 'listgroup');
}
elseif ($action == "main")
{
    template_out('main.html', $modul_name);
}
elseif ($action == "listmod")
{
    $mods_q = $db->query("SELECT `modul_admin_id`, `modul_name`, `modul_description`, `acp_modul`, `perm_lvl` FROM `cc" . $n .
        "_modul_admin` WHERE `acp_modul` = '1'");
    $mods = array();
    $i = 0;
    while ($mod = $db->fetch_array($mods_q))
    {
        $mods[$i]['id'] = $mod['modul_admin_id'];
        $mods[$i]['name'] = $mod['modul_name'];
        $mods[$i]['description'] = $mod['modul_description'];
        $mods[$i]['perm'] = $mod['perm_lvl'];
        $i++;
    }
    $tpl->assign('mods', $mods);
    template_out('listmod.html', $modul_name);
}
elseif ($action == "listgroup")
{
    $groups_q = $db->query("SELECT `id`, `name`, `perm_lvl` FROM `cc" . $n . "_user_groups`");
    $groups = array();
    $i = 0;
    while ($group = $db->fetch_array($groups_q))
    {
        $groups[$i]['id'] = $group['id'];
        $groups[$i]['name'] = $group['name'];
        $groups[$i]['perm'] = $group['perm_lvl'];
        $i++;
    }
    $tpl->assign('groups', $groups);
    template_out('listgroup.html', $modul_name);
}
