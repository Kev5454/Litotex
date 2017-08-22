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

if (session_id() == "")
{
    session_start();
}

if (!isset($_SESSION['litotex_start_acp']))
{
    header('LOCATION: ./../../index.php');
}


$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "acp_login";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');
require (LITO_LANG_PATH . $modul_name . "/lang_" . $lang_suffix . ".php");


function login_error($error_msg)
{
    global $tpl, $modul_name;

    $tpl->assign('if_disable_menu', 1);
    $tpl->assign('LITO_ERROR_MSG', $error_msg);
    $tpl->assign('if_login_error', 1);

    template_out('login.html', $modul_name);
    exit();
}


if ($action == "main")
{
    //$tpl ->display("login/login.html");
    $tpl->assign('if_disable_menu', 1);

    template_out('login.html', $modul_name);
}
elseif ($action == "submit")
{
    if (empty($_POST['username']) || empty($_POST['password']))
    {
        login_error($ln_login_e_1);
        exit();
    }

    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $username = strtolower($username);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    $result = $db->query("SELECT * FROM cc" . $n . "_users WHERE username='$username'");
    $row = $db->fetch_array($result);

    if (strtolower($row['username']) != $username)
    {
        trace_msg("login ERROR '$username' wrong username", 2);
        login_error($ln_login_e_2);
    }

    if ($row['password'] != md5($password))
    {
        trace_msg("login ERROR '$username' wrong password", 2);
        login_error($ln_login_e_2);
    }
    if (!$row['serveradmin'])
    {
        $grp_q = $db->query("SELECT `perm_lvl`, `id` FROM `cc" . $n . "_user_groups` WHERE `id` = '" . $row['group'] . "'");
        $grp = $db->fetch_array($grp_q);
        if (!$grp)
        {
            login_error('Schwerer Fehler! Die Usergruppe konnte nicht gefunden werden! Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
        }
        if ($grp['perm_lvl'] <= 0)
        {
            login_error('Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
        }
    }

    $_SESSION['userid'] = (int)($row['userid']);
    $_SESSION['lang'] = $row['lang'];
    trace_msg("login OK '$username' ", 2);
    $db->unbuffered_query("UPDATE cc" . $n . "_users SET lastlogin='" . time() . "', ip='" . getenv("REMOTE_ADDR") .
        "' WHERE username='$username'");

    redirect('acp_core', 'admin', 'main');
}
