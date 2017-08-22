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
$modul_name = "acp_reset";
$menu_name = "Game Reset";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);


if ($action == 'reset')
{
    $sql = "TRUNCATE TABLE `cc1_allianz`;";
    $sql .= "TRUNCATE TABLE `cc1_allianznews`;";
    $sql .= "TRUNCATE TABLE `cc1_allianz_bewerbung`;";
    $sql .= "TRUNCATE TABLE `cc1_allianz_log`;";
    $sql .= "TRUNCATE TABLE `cc1_allianz_rang`;";
    $sql .= "TRUNCATE TABLE `cc1_allianz_rang_user`;";
    $sql .= "TRUNCATE TABLE `cc1_allianz_rechte`;";
    $sql .= "TRUNCATE TABLE `cc1_banner_mgr`;";
    $sql .= "TRUNCATE TABLE `cc1_battle`;";
    $sql .= "TRUNCATE TABLE `cc1_battle_archiv`;";
    $sql .= "TRUNCATE TABLE `cc1_countries`;";
    $sql .= "TRUNCATE TABLE `cc1_create_sol`;";
    $sql .= "TRUNCATE TABLE `cc1_debug`;";
    $sql .= "TRUNCATE TABLE `cc1_forum`;";
    $sql .= "TRUNCATE TABLE `cc1_forum_last`;";
    $sql .= "TRUNCATE TABLE `cc1_forum_posts`;";
    $sql .= "TRUNCATE TABLE `cc1_forum_topics`;";
    $sql .= "TRUNCATE TABLE `cc1_groups`;";
    $sql .= "TRUNCATE TABLE `cc1_groups_inhalt`;";
    $sql .= "TRUNCATE TABLE `cc1_messages`;";
    $sql .= "TRUNCATE TABLE `cc1_news`;";
    $sql .= "TRUNCATE TABLE `cc1_new_land`;";
    $sql .= "TRUNCATE TABLE `cc1_sessions`;";
    $sql .= "TRUNCATE TABLE `cc1_spions`;";
    $sql .= "DELETE FROM `cc1_users` WHERE `serveradmin` != 1;";
    $sql .= "UPDATE `cc1_crand` SET `used` = '0';";

    $db->multi_query($sql);

    removeDirectory(LITO_ROOT_PATH . 'alli_flag/');
    removeDirectory(LITO_ROOT_PATH . 'battle_kr/');
    removeDirectory(LITO_ROOT_PATH . 'image_user/');
    removeDirectory(LITO_ROOT_PATH . 'image_sig/');
    removeDirectory(LITO_ROOT_PATH . 'images_tmp/');

    _mkdir('alli_flag/');
    _mkdir('battle_kr/');
    _mkdir('image_user/');
    _mkdir('image_sig/');
    _mkdir('images_tmp/');

    redirect($modul_name, 'reset', 'main');
}
elseif ($action == 'main')
{
    template_out('main.html', $modul_name);
}
