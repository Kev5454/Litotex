<?php
/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */

session_start();

require ('../../includes/global.php');
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "pagemanager";

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}


if ($action == "main")
{

    $getName = (isset($_GET['name']) ? filter_var($_GET['name'], FILTER_SANITIZE_STRING) : null);
    if ($getName == null)
    {
        template_out('notFound.html', $modul_name);
        exit();
    }

    $result_news = $db->query("SELECT * FROM cc" . $n . "_pages WHERE isActive='1' && getName = '$getName'");
    if ($db->num_rows($result_news) == 0)
    {
        template_out('notFound.html', $modul_name);
        exit();
    }
    $row_g = $db->fetch_array($result_news);

    if ($row_g['access'] == 'member' && !isset($_SESSION['userid']) || $row_g['access'] == 'support' && !isset($_SESSION['userid']) ||
        $row_g['access'] == 'admin' && !isset($_SESSION['userid']) || $row_g['access'] == 'member' && !isset($userdata) || $row_g['access'] ==
        'support' && !isset($userdata) || $row_g['access'] == 'admin' && !isset($userdata))
    {
        template_out('notFound.html', $modul_name);
        exit();
    }

    if ($row_g['access'] == 'admin' && !$userdata['serveradmin'] || $row_g['access'] == 'support' || $row_g['access'] ==
        'member')
    {
        if ($row_g['access'] == 'admin' && $row['group'] != 2 || $row_g['access'] == 'support' && $row['group'] == 1)
        {
            template_out('notFound.html', $modul_name);
            exit();
        }
    }

    $tpl->assign('TITLE', $row_g['title']);
    $tpl->assign('CONTENT', htmlspecialchars_decode($row_g['content']));
    template_out('page.html', $modul_name);
}
